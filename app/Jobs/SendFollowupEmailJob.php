<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\CampaignFollowup;
use App\Models\CampaignLog;
use App\Models\EmailList;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Models\TemplateCategory;
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

class SendFollowupEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 500;
    public int $backoff = 120;
    public int $timeout = 60;

    public function __construct(
        public int $followupId,
        public int $emailListId
    ) {}

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
        Setting::applyMailConfig();

        $followup  = CampaignFollowup::find($this->followupId);
        $emailItem = EmailList::find($this->emailListId);

        if (!$followup || !$emailItem) return;

        if ($followup->status !== 'running') return;

        // Idempotency: skip if already sent for this followup
        if (CampaignLog::where('followup_id', $this->followupId)
                ->where('email_list_id', $this->emailListId)
                ->exists()) {
            return;
        }

        // Re-check if the recipient has replied to the main campaign
        $mainLog = CampaignLog::where('campaign_id', $followup->campaign_id)
            ->whereNull('followup_id')
            ->where('email_list_id', $this->emailListId)
            ->where('status', 'sent')
            ->first();

        if (!$mainLog || $mainLog->reply_count > 0) {
            $followup->increment('failed_count');
            $this->checkFollowupCompletion($followup);
            return;
        }

        // Never send to addresses suppressed by hard bounce or spam complaint
        if ($emailItem->isDoNotMail()) {
            $this->logResult($followup, $emailItem, null, 'failed', 'Address suppressed (bounce/complaint)');
            $followup->increment('failed_count');
            $this->checkFollowupCompletion($followup);
            return;
        }

        // Enforce the global daily send limit
        if (!$this->withinDailyLimit()) {
            $this->release(3600);
            return;
        }

        // Rate-limit per followup
        $rateKey = "followup:{$this->followupId}:rate";
        if (RateLimiter::tooManyAttempts($rateKey, 1)) {
            $this->release(RateLimiter::availableIn($rateKey) + 1);
            return;
        }
        RateLimiter::hit($rateKey, 1);

        // Template selection
        $template = $this->resolveTemplate($followup);

        if (!$template) {
            $this->logResult($followup, $emailItem, null, 'failed', 'No active templates found');
            $followup->increment('failed_count');
            $this->checkFollowupCompletion($followup);
            return;
        }

        // Ensure unsubscribe token
        if (!$emailItem->getRawOriginal('unsubscribe_token')) {
            $token = Str::random(64);
            $emailItem->updateQuietly(['unsubscribe_token' => $token]);
            $emailItem->setRawAttributes(array_merge($emailItem->getRawOriginal(), ['unsubscribe_token' => $token]));
        }

        $provider      = Setting::get('active_email_provider', 'ses');
        $trackingToken = Str::random(64);

        try {
            $content = (new CampaignMail($template, $emailItem->name ?? '', $emailItem->email, $emailItem->getRawOriginal('unsubscribe_token') ?? ''))
                ->renderContent($trackingToken);

            $replyDomain = parse_url(config('app.url'), PHP_URL_HOST);
            $replyToken  = substr($trackingToken, 0, 40);
            $fromName    = Setting::get('mail_from_name', 'Support');
            $adminEmail  = Setting::get('mail_from_email')
                        ?: Setting::get('resend_sender_email')
                        ?: Setting::get('ses_sender_email');

            $replyTo = [$fromName . ' <' . $replyToken . '@' . $replyDomain . '>'];
            if ($adminEmail) {
                $replyTo[] = $adminEmail;
            }

            $result = EmailProviderManager::send([
                'to'       => $emailItem->email,
                'to_name'  => $emailItem->name ?? '',
                'subject'  => $content['subject'],
                'html'     => $content['html'],
                'reply_to' => $replyTo,
            ]);

            $emailItem->update(['status' => 'sent']);
            $this->logResult($followup, $emailItem, $template->id, 'sent', null, $result['provider'] ?? $provider, $result['message_id'] ?? null, $trackingToken);
            $followup->increment('sent_count');
        } catch (Throwable $e) {
            $retryEnabled = Setting::get('campaign_retry_failed', '1') === '1';

            if (!$retryEnabled) {
                $emailItem->update(['status' => 'failed']);
                $this->logResult($followup, $emailItem, $template->id, 'failed', $e->getMessage(), $provider);
                $followup->increment('failed_count');
                Log::error("Followup [{$this->followupId}] permanently failed for {$emailItem->email}: " . $e->getMessage());
                $this->checkFollowupCompletion($followup);
                return;
            }

            Log::warning("Followup [{$this->followupId}] send attempt {$this->attempts()} failed for {$emailItem->email}: " . $e->getMessage());
            throw $e;
        }

        $this->checkFollowupCompletion($followup);
    }

    public function failed(Throwable $exception): void
    {
        $followup  = CampaignFollowup::find($this->followupId);
        $emailItem = EmailList::find($this->emailListId);

        if (!$emailItem || !$followup) return;

        if (in_array($emailItem->status, ['sent', 'failed'])) return;

        $emailItem->update(['status' => 'failed']);
        $this->logResult($followup, $emailItem, null, 'failed', $exception->getMessage(), Setting::get('active_email_provider', 'ses'));
        $followup->increment('failed_count');
        $this->checkFollowupCompletion($followup);
    }

    private function resolveTemplate(CampaignFollowup $followup): ?EmailTemplate
    {
        if ($followup->template_id) {
            return EmailTemplate::where('id', $followup->template_id)->where('status', 'active')->first()
                ?? EmailTemplate::where('status', 'active')->first();
        }

        $useRandom = Setting::get('campaign_random_rotation', '0') === '1';

        if ($useRandom) {
            $query = EmailTemplate::where('status', 'active');
            $campaign = $followup->campaign;
            if ($campaign && $campaign->template_category_id) {
                $catName = TemplateCategory::find($campaign->template_category_id)?->name;
                if ($catName) $query->where('category', $catName);
            }
            return $query->inRandomOrder()->first();
        }

        return EmailTemplate::where('status', 'active')->first();
    }

    private function withinDailyLimit(): bool
    {
        $limit = (int) Setting::get('campaign_daily_limit', 500);
        $key   = 'mailauto:daily_sent:' . now()->format('Y-m-d');

        Cache::add($key, 0, now()->endOfDay());
        $count = Cache::increment($key);

        if ($count > $limit) {
            Cache::decrement($key);
            return false;
        }

        return true;
    }

    private function logResult(CampaignFollowup $followup, EmailList $emailItem, ?int $templateId, string $status, ?string $error = null, ?string $provider = null, ?string $messageId = null, ?string $trackingToken = null): void
    {
        CampaignLog::create([
            'campaign_id'         => $followup->campaign_id,
            'followup_id'         => $followup->id,
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

    private function checkFollowupCompletion(CampaignFollowup $followup): void
    {
        $followup->refresh();
        if (($followup->sent_count + $followup->failed_count) >= $followup->total_emails && $followup->total_emails > 0) {
            $followup->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
            // Trigger next followup if any
            $next = CampaignFollowup::where('campaign_id', $followup->campaign_id)
                ->where('sort_order', $followup->sort_order + 1)
                ->where('status', 'pending')
                ->first();

            if ($next) {
                ProcessFollowupChunkJob::dispatch($next->id)
                    ->delay(now()->addDays($next->delay_days));
            }
        }
    }
}
