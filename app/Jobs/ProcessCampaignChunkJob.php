<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\EmailList;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCampaignChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // High enough to survive long pauses via 5-min release cycles.
    public int $tries   = 100;
    public int $timeout = 120;

    public function __construct(
        public int $campaignId,
        public int $afterId    = 0,   // ID cursor — fetch emails with id > afterId
        public int $chunkSize  = 500
    ) {}

    public function handle(): void
    {
        $campaign = Campaign::find($this->campaignId);
        if (!$campaign) return;

        if ($campaign->status === 'paused') {
            $this->release(300); // Re-check every 5 min while paused
            return;
        }

        if ($campaign->status !== 'running') return;

        // Delay is always driven by the global setting (in seconds).
        $delay = (int) Setting::get('campaign_delay', 1728);

        // Fetch next slice of pending emails ordered by ID (ID cursor, not offset).
        // Ordering by ID is stable even if earlier rows change status between chunks.
        $chunk = EmailList::where('group_id', $campaign->email_group_id)
            ->where('status', 'pending')
            ->where('id', '>', $this->afterId)
            ->orderBy('id')
            ->take($this->chunkSize)
            ->get(['id']);

        if ($chunk->isEmpty()) return;

        // Dispatch send jobs staggered within this chunk.
        // Index 0 fires immediately; index N fires at N × delay seconds from now.
        // The next chunk dispatcher is scheduled at chunkSize × delay seconds,
        // so email spacing is continuous across chunk boundaries.
        foreach ($chunk as $index => $emailItem) {
            SendCampaignEmailJob::dispatch($this->campaignId, $emailItem->id)
                ->delay(now()->addSeconds($index * $delay));
        }

        // If we filled the chunk there are likely more emails — chain the next dispatcher.
        if ($chunk->count() === $this->chunkSize) {
            static::dispatch($this->campaignId, $chunk->last()->id, $this->chunkSize)
                ->delay(now()->addSeconds($this->chunkSize * $delay));
        }
    }
}
