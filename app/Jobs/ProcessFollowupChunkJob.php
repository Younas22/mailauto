<?php

namespace App\Jobs;

use App\Models\CampaignFollowup;
use App\Models\CampaignLog;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessFollowupChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 100;
    public int $timeout = 120;

    public function __construct(
        public int $followupId,
        public int $afterId   = 0,
        public int $chunkSize = 500
    ) {}

    public function handle(): void
    {
        $followup = CampaignFollowup::find($this->followupId);

        if (!$followup || $followup->status === 'completed') return;

        $delay = (int) Setting::get('campaign_delay', 1728);

        // On first dispatch (pending), count eligible recipients and initialize
        if ($followup->status === 'pending') {
            $eligible = CampaignLog::where('campaign_id', $followup->campaign_id)
                ->whereNull('followup_id')
                ->where('status', 'sent')
                ->where('reply_count', 0)
                ->count();

            $followup->update([
                'status'       => 'running',
                'total_emails' => $eligible,
                'started_at'   => now(),
            ]);

            $followup->refresh();
        }

        // Get email_list_ids already sent for this followup (idempotency)
        $alreadySent = CampaignLog::where('followup_id', $this->followupId)
            ->pluck('email_list_id')
            ->toArray();

        // Fetch next chunk of eligible main-campaign logs
        $chunk = CampaignLog::where('campaign_id', $followup->campaign_id)
            ->whereNull('followup_id')
            ->where('status', 'sent')
            ->where('reply_count', 0)
            ->where('id', '>', $this->afterId)
            ->when(!empty($alreadySent), fn($q) => $q->whereNotIn('email_list_id', $alreadySent))
            ->orderBy('id')
            ->take($this->chunkSize)
            ->get(['id', 'email_list_id']);

        if ($chunk->isEmpty()) {
            // No more recipients — mark completed and trigger next followup if any
            $followup->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
            $this->triggerNextFollowup($followup);
            return;
        }

        // Dispatch staggered send jobs for this chunk
        foreach ($chunk as $index => $log) {
            SendFollowupEmailJob::dispatch($this->followupId, $log->email_list_id)
                ->delay(now()->addSeconds($index * $delay));
        }

        // If chunk was full, chain next chunk dispatcher
        if ($chunk->count() === $this->chunkSize) {
            static::dispatch($this->followupId, $chunk->last()->id, $this->chunkSize)
                ->delay(now()->addSeconds($this->chunkSize * $delay));
        }
    }

    private function triggerNextFollowup(CampaignFollowup $followup): void
    {
        $next = CampaignFollowup::where('campaign_id', $followup->campaign_id)
            ->where('sort_order', $followup->sort_order + 1)
            ->where('status', 'pending')
            ->first();

        if ($next) {
            static::dispatch($next->id)
                ->delay(now()->addDays($next->delay_days));
        }
    }
}
