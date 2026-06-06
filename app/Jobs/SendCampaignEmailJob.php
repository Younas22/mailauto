<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\CampaignLog;
use App\Models\EmailList;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 120;
    public int $timeout = 60;

    public function __construct(
        public int $campaignId,
        public int $emailListId
    ) {}

    public function handle(): void
    {
        $campaign  = Campaign::find($this->campaignId);
        $emailItem = EmailList::find($this->emailListId);

        if (!$campaign || !$emailItem) return;

        // If paused, release back to queue after 60 s and wait
        if ($campaign->status === 'paused') {
            $this->release(60);
            return;
        }

        // If campaign was cancelled or completed externally, skip
        if (!in_array($campaign->status, ['running'])) return;

        // Rate-limit: at most 1 email per second across all workers
        $key = "campaign:{$this->campaignId}:rate";
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $this->release(RateLimiter::availableIn($key) + 1);
            return;
        }
        RateLimiter::hit($key, 1);

        // Pick a random active template
        $template = EmailTemplate::where('status', 'active')->inRandomOrder()->first();

        if (!$template) {
            $this->logResult($campaign->id, $emailItem, null, 'failed', 'No active templates found');
            $campaign->increment('failed_count');
            $this->checkCompletion($campaign);
            return;
        }

        try {
            Mail::to($emailItem->email, $emailItem->name ?? '')
                ->send(new CampaignMail($template, $emailItem->name ?? ''));

            $emailItem->update(['status' => 'sent']);
            $this->logResult($campaign->id, $emailItem, $template->id, 'sent');
            $campaign->increment('sent_count');
        } catch (Throwable $e) {
            $emailItem->update(['status' => 'failed']);
            $this->logResult($campaign->id, $emailItem, $template->id, 'failed', $e->getMessage());
            $campaign->increment('failed_count');
            Log::error("Campaign [{$this->campaignId}] failed for {$emailItem->email}: " . $e->getMessage());
        }

        $this->checkCompletion($campaign);
    }

    public function failed(Throwable $exception): void
    {
        $campaign  = Campaign::find($this->campaignId);
        $emailItem = EmailList::find($this->emailListId);

        if (!$emailItem || !$campaign) return;

        $emailItem->update(['status' => 'failed']);
        $this->logResult($campaign->id, $emailItem, null, 'failed', $exception->getMessage());
        $campaign->increment('failed_count');
        $this->checkCompletion($campaign);
    }

    private function logResult(int $campaignId, EmailList $emailItem, ?int $templateId, string $status, ?string $error = null): void
    {
        CampaignLog::create([
            'campaign_id'       => $campaignId,
            'email_list_id'     => $emailItem->id,
            'email_template_id' => $templateId,
            'email'             => $emailItem->email,
            'status'            => $status,
            'error_message'     => $error,
            'sent_at'           => $status === 'sent' ? now() : null,
        ]);
    }

    private function checkCompletion(Campaign $campaign): void
    {
        $campaign->refresh();
        $processed = $campaign->sent_count + $campaign->failed_count;
        if ($processed >= $campaign->total_emails) {
            $campaign->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
        }
    }
}
