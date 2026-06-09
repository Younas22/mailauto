<?php

namespace App\Jobs;

use App\Models\EmailList;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Models\WarmupPlan;
use App\Services\EmailProviders\EmailProviderManager;
use App\Services\WarmupScheduleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class SendWarmupEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;
    public int $timeout = 60;

    public function __construct(
        public int $warmupPlanId,
        public int $emailListId
    ) {}

    public function handle(WarmupScheduleService $scheduler): void
    {
        Setting::applyMailConfig();

        $plan      = WarmupPlan::find($this->warmupPlanId);
        $emailItem = EmailList::find($this->emailListId);

        if (!$plan || !$emailItem) return;

        // Abort if plan was paused or stopped while jobs were queued
        if (!in_array($plan->status, ['active'])) return;

        // Never send to suppressed addresses
        if ($emailItem->isDoNotMail() || $emailItem->isUnsubscribed()) return;

        // Per-plan rate limit: 1 warmup email/second max across all workers
        $rateKey = "warmup:{$this->warmupPlanId}:rate";
        if (RateLimiter::tooManyAttempts($rateKey, 1)) {
            $this->release(RateLimiter::availableIn($rateKey) + 1);
            return;
        }
        RateLimiter::hit($rateKey, 1);

        // Resolve which template to use
        $template = $plan->template
            ?? EmailTemplate::where('status', 'active')->inRandomOrder()->first();

        if (!$template) {
            Log::warning("Warmup plan [{$plan->id}]: no active template found, skipping.");
            $scheduler->incrementFailed($plan);
            return;
        }

        // Ensure unsubscribe token exists before rendering
        if (!$emailItem->getRawOriginal('unsubscribe_token')) {
            $token = Str::random(64);
            $emailItem->updateQuietly(['unsubscribe_token' => $token]);
            $emailItem->setRawAttributes(array_merge($emailItem->getRawOriginal(), ['unsubscribe_token' => $token]));
        }

        try {
            $mail = new \App\Mail\CampaignMail(
                $template,
                $emailItem->name ?? '',
                $emailItem->getRawOriginal('unsubscribe_token') ?? ''
            );
            $content = $mail->renderContent();

            EmailProviderManager::provider($plan->provider)->send([
                'to'      => $emailItem->email,
                'to_name' => $emailItem->name ?? '',
                'subject' => $content['subject'],
                'html'    => $content['html'],
            ]);

            $scheduler->incrementSent($plan);

        } catch (Throwable $e) {
            $scheduler->incrementFailed($plan);
            Log::warning("Warmup plan [{$plan->id}] failed for {$emailItem->email}: " . $e->getMessage());
            throw $e; // Honour the 3-retry policy
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Warmup plan [{$this->warmupPlanId}] permanently failed for list id {$this->emailListId}: " . $exception->getMessage());
    }
}
