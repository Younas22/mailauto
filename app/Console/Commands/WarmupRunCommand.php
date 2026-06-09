<?php

namespace App\Console\Commands;

use App\Jobs\SendWarmupEmailJob;
use App\Models\EmailList;
use App\Models\WarmupPlan;
use App\Services\WarmupScheduleService;
use Illuminate\Console\Command;

class WarmupRunCommand extends Command
{
    protected $signature   = 'warmup:run {--plan= : Run only a specific plan ID}';
    protected $description = 'Execute daily warmup sends for all active warmup plans';

    public function handle(WarmupScheduleService $scheduler): int
    {
        $query = WarmupPlan::where('status', 'active');

        if ($planId = $this->option('plan')) {
            $query->where('id', $planId);
        }

        $plans = $query->get();

        if ($plans->isEmpty()) {
            $this->info('No active warmup plans found.');
            return self::SUCCESS;
        }

        foreach ($plans as $plan) {
            $this->runPlan($plan, $scheduler);
        }

        return self::SUCCESS;
    }

    private function runPlan(WarmupPlan $plan, WarmupScheduleService $scheduler): void
    {
        $this->line("▶ Plan [{$plan->id}] {$plan->name} ({$plan->domain})");

        // Refresh bounce/complaint counters from today's EmailList data
        $scheduler->refreshSafetyCounters($plan);

        // Safety check — auto-pause if thresholds exceeded
        $safety = $scheduler->safetyCheck($plan);
        if (!$safety['safe']) {
            $plan->update([
                'status'       => 'paused',
                'pause_reason' => $safety['reason'],
            ]);
            $this->warn("  ⚠ Auto-paused: {$safety['reason']}");
            return;
        }

        // Determine current warmup day from calendar
        $calendarDay = $plan->calendarDay();

        // Mark as completed once past max warmup day with full volume already achieved
        if ($calendarDay > WarmupScheduleService::MAX_DAY) {
            $plan->update(['status' => 'completed', 'current_day' => WarmupScheduleService::MAX_DAY]);
            $this->info("  ✓ Plan completed — full warmup volume reached.");
            return;
        }

        $remaining = $scheduler->remainingForToday($plan);

        if ($remaining <= 0) {
            $this->info("  ✓ Daily quota already met for today (Day {$calendarDay}).");
            // Still sync current_day in case we're catching up
            $plan->update(['current_day' => $calendarDay, 'daily_limit' => $scheduler->dailyLimitForDay($calendarDay)]);
            return;
        }

        $this->line("  Day {$calendarDay} — dispatching {$remaining} warmup emails…");

        // Pull eligible subscribers (not suppressed, not unsubscribed)
        $query = EmailList::whereNull('bounced_at')
            ->where('is_do_not_mail', false)
            ->whereNull('unsubscribed_at');

        if ($plan->email_group_id) {
            $query->where('group_id', $plan->email_group_id);
        }

        $subscribers = $query->inRandomOrder()->limit($remaining)->get();

        if ($subscribers->isEmpty()) {
            $this->warn("  ⚠ No eligible subscribers found for plan [{$plan->id}].");
            return;
        }

        $dispatched = 0;
        foreach ($subscribers as $idx => $subscriber) {
            // Stagger by 2 seconds per email to avoid burst
            SendWarmupEmailJob::dispatch($plan->id, $subscriber->id)
                ->delay(now()->addSeconds($idx * 2));
            $dispatched++;
        }

        // Sync plan state
        $plan->update([
            'current_day' => $calendarDay,
            'daily_limit' => $scheduler->dailyLimitForDay($calendarDay),
        ]);

        $this->info("  ✓ Dispatched {$dispatched} jobs (Day {$calendarDay}, limit {$scheduler->dailyLimitForDay($calendarDay)}).");
    }
}
