<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\CampaignLog;
use App\Models\EmailList;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Services\EmailProviders\EmailProviderManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Large enough to survive ~41 hours of pause (500 × 5 min) plus send retries.
    public int $tries   = 500;
    public int $backoff = 120;
    public int $timeout = 60;

    public function __construct(
        public int $campaignId,
        public int $emailListId
    ) {}

    // Controls how many *thrown* exceptions (actual send failures) are allowed.
    // Pause releases don't throw, so they don't count toward this limit.
    public function maxExceptions(): int
    {
        try {
            return Setting::get('campaign_retry_failed', '1') === '1' ? 3 : 1;
        } catch (Throwable) {
            return 3;
        }
    }

    public function handle(): void
    {
        // Always re-apply DB mail config so queue workers use the correct mailer,
        // not whatever was set in .env at worker-boot time.
        Setting::applyMailConfig();

        $campaign  = Campaign::find($this->campaignId);
        $emailItem = EmailList::find($this->emailListId);

        if (!$campaign || !$emailItem) return;

        // Never send to addresses suppressed by hard bounce or spam complaint.
        if ($emailItem->isDoNotMail()) {
            $this->logResult($campaign->id, $emailItem, null, 'failed', 'Address suppressed (bounce/complaint)');
            $campaign->increment('failed_count');
            $this->checkCompletion($campaign);
            return;
        }

        if ($campaign->status === 'paused') {
            $this->release(300); // Re-check every 5 min; doesn't count as an exception
            return;
        }

        if ($campaign->status !== 'running') return;

        // Enforce the global daily send limit before touching the rate limiter
        if (!$this->withinDailyLimit()) {
            $this->release(3600); // re-queue for 1 hour later
            return;
        }

        // Rate-limit: at most 1 email per second across all workers for this campaign
        $rateKey = "campaign:{$this->campaignId}:rate";
        if (RateLimiter::tooManyAttempts($rateKey, 1)) {
            $this->release(RateLimiter::availableIn($rateKey) + 1);
            return;
        }
        RateLimiter::hit($rateKey, 1);

        // Template selection: random rotation only when the setting is enabled
        $useRandom = Setting::get('campaign_random_rotation', '0') === '1';
        $query     = EmailTemplate::where('status', 'active');
        $template  = $useRandom ? $query->inRandomOrder()->first() : $query->first();

        if (!$template) {
            $this->logResult($campaign->id, $emailItem, null, 'failed', 'No active templates found');
            $campaign->increment('failed_count');
            $this->checkCompletion($campaign);
            return;
        }

        // Ensure the contact has an unsubscribe token before sending
        if (!$emailItem->getRawOriginal('unsubscribe_token')) {
            $token = Str::random(64);
            $emailItem->updateQuietly(['unsubscribe_token' => $token]);
            $emailItem->setRawAttributes(array_merge($emailItem->getRawOriginal(), ['unsubscribe_token' => $token]));
        }

        $provider       = Setting::get('active_email_provider', 'ses');
        $trackingToken  = Str::random(64);

        try {
            $content = (new CampaignMail($template, $emailItem->name ?? '', $emailItem->email, $emailItem->getRawOriginal('unsubscribe_token') ?? ''))
                ->renderContent($trackingToken);

            $replyDomain  = parse_url(config('app.url'), PHP_URL_HOST);
            $replyToken   = substr($trackingToken, 0, 57); // reply+ (6) + 57 = 63 chars, within RFC limit

            $result = EmailProviderManager::send([
                'to'       => $emailItem->email,
                'to_name'  => $emailItem->name ?? '',
                'subject'  => $content['subject'],
                'html'     => $content['html'],
                'reply_to' => 'reply+' . $replyToken . '@' . $replyDomain,
            ]);

            $emailItem->update(['status' => 'sent']);
            $this->logResult($campaign->id, $emailItem, $template->id, 'sent', null, $result['provider'] ?? $provider, $result['message_id'] ?? null, $trackingToken);
            $campaign->increment('sent_count');
        } catch (Throwable $e) {
            $retryEnabled = Setting::get('campaign_retry_failed', '1') === '1';

            if (!$retryEnabled) {
                // No retry: permanently fail this email now
                $emailItem->update(['status' => 'failed']);
                $this->logResult($campaign->id, $emailItem, $template->id, 'failed', $e->getMessage(), $provider);
                $campaign->increment('failed_count');
                Log::error("Campaign [{$this->campaignId}] permanently failed for {$emailItem->email}: " . $e->getMessage());
                $this->checkCompletion($campaign);
                return;
            }

            // Retry enabled: log the attempt and re-throw so Laravel retries.
            // maxExceptions() caps send-failure retries at 3; failed() handles the final failure.
            Log::warning("Campaign [{$this->campaignId}] send attempt {$this->attempts()} failed for {$emailItem->email}: " . $e->getMessage());
            throw $e;
        }

        $this->checkCompletion($campaign);
    }

    public function failed(Throwable $exception): void
    {
        $campaign  = Campaign::find($this->campaignId);
        $emailItem = EmailList::find($this->emailListId);

        if (!$emailItem || !$campaign) return;

        // Only handle here when retry-enabled path exhausted maxExceptions.
        // The no-retry path marks the email inside handle() and returns early,
        // so we must not double-count sent or already-failed emails.
        if (in_array($emailItem->status, ['sent', 'failed'])) return;

        $emailItem->update(['status' => 'failed']);
        $this->logResult($campaign->id, $emailItem, null, 'failed', $exception->getMessage(), Setting::get('active_email_provider', 'ses'));
        $campaign->increment('failed_count');
        $this->checkCompletion($campaign);
    }

    // Atomically increment the daily counter and return false when the limit is exceeded.
    // Cache key is date-scoped so the counter resets automatically at midnight.
    private function withinDailyLimit(): bool
    {
        $limit = (int) Setting::get('campaign_daily_limit', 500);
        $key   = 'mailauto:daily_sent:' . now()->format('Y-m-d');

        Cache::add($key, 0, now()->endOfDay()); // no-op if key already exists
        $count = Cache::increment($key);

        if ($count > $limit) {
            Cache::decrement($key);
            return false;
        }

        return true;
    }

    private function logResult(int $campaignId, EmailList $emailItem, ?int $templateId, string $status, ?string $error = null, ?string $provider = null, ?string $messageId = null, ?string $trackingToken = null): void
    {
        CampaignLog::create([
            'campaign_id'         => $campaignId,
            'email_list_id'       => $emailItem->id,
            'email_template_id'   => $templateId,
            'email'               => $emailItem->email,
            'provider'            => $provider,
            'provider_message_id' => $messageId,
            'status'              => $status,
            'error_message'       => $error,
            'sent_at'             => $status === 'sent' ? now() : null,
            'tracking_token'      => $trackingToken,
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
