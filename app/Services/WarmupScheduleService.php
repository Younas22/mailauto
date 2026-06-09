<?php

namespace App\Services;

use App\Models\WarmupLog;
use App\Models\WarmupPlan;

class WarmupScheduleService
{
    // Daily sending targets per warmup day. Day 15+ stays at max.
    private const SCHEDULE = [
        1  => 20,
        2  => 40,
        3  => 80,
        4  => 120,
        5  => 200,
        6  => 300,
        7  => 500,
        8  => 750,
        9  => 1_000,
        10 => 1_500,
        11 => 2_000,
        12 => 3_000,
        13 => 5_000,
        14 => 7_500,
        15 => 10_000,
    ];

    public const MAX_DAY    = 15;
    public const MAX_VOLUME = 10_000;

    public function dailyLimitForDay(int $day): int
    {
        if ($day <= 0) return self::SCHEDULE[1];
        if ($day >= self::MAX_DAY) return self::MAX_VOLUME;
        return self::SCHEDULE[$day] ?? self::MAX_VOLUME;
    }

    /** Full 15-day schedule for display (day => limit). */
    public function fullSchedule(): array
    {
        return self::SCHEDULE;
    }

    /**
     * Generate a custom warmup schedule based on user-selected parameters.
     * Returns [day => emails] for the given number of days.
     */
    public function customSchedule(int $day1, int $factor, int $days): array
    {
        $increment = $factor * $day1; // additive step per day
        $schedule  = [];
        for ($i = 1; $i <= $days; $i++) {
            $schedule[$i] = $day1 + $increment * ($i - 1);
        }
        return $schedule;
    }

    /** Daily limit for a specific plan, respecting its custom schedule if set. */
    public function dailyLimitForPlan(WarmupPlan $plan, int $day): int
    {
        if ($plan->day1_emails && $plan->increase_factor && $plan->end_date) {
            $days     = max(1, (int) $plan->start_date->diffInDays($plan->end_date) + 1);
            $schedule = $this->customSchedule($plan->day1_emails, $plan->increase_factor, $days);
            if (isset($schedule[$day])) {
                return $schedule[$day];
            }
            return end($schedule) ?: self::MAX_VOLUME;
        }
        return $this->dailyLimitForDay($day);
    }

    /**
     * Safety check against a plan's recent warmup_logs.
     * Returns ['safe' => bool, 'reason' => string|null]
     */
    public function safetyCheck(WarmupPlan $plan): array
    {
        // Aggregate last 7 days of warmup logs
        $recentLogs = $plan->logs()
            ->where('date', '>=', now()->subDays(6)->toDateString())
            ->get();

        $totalSent       = $recentLogs->sum('emails_sent');
        $totalBounces    = $recentLogs->sum('bounce_count');
        $totalComplaints = $recentLogs->sum('complaint_count');

        if ($totalSent === 0) {
            return ['safe' => true, 'reason' => null];
        }

        $bounceRate    = round($totalBounces    / $totalSent * 100, 2);
        $complaintRate = round($totalComplaints / $totalSent * 100, 2);

        if ($bounceRate > (float) $plan->max_bounce_rate) {
            return [
                'safe'   => false,
                'reason' => "Bounce rate {$bounceRate}% exceeds threshold {$plan->max_bounce_rate}%",
            ];
        }

        if ($complaintRate > (float) $plan->max_complaint_rate) {
            return [
                'safe'   => false,
                'reason' => "Complaint rate {$complaintRate}% exceeds threshold {$plan->max_complaint_rate}%",
            ];
        }

        return ['safe' => true, 'reason' => null];
    }

    /**
     * Returns how many emails still need to be sent today for a plan.
     * Reads (or creates) the today warmup_log row.
     */
    public function remainingForToday(WarmupPlan $plan): int
    {
        $today     = today()->toDateString();
        $targetDay = $plan->calendarDay();
        $limit     = $this->dailyLimitForDay($targetDay);

        $log = WarmupLog::firstOrCreate(
            ['warmup_plan_id' => $plan->id, 'date' => $today],
            ['daily_limit' => $limit, 'emails_sent' => 0, 'emails_failed' => 0]
        );

        // Keep limit in sync if plan day advanced
        if ($log->daily_limit !== $limit) {
            $log->update(['daily_limit' => $limit]);
        }

        return max(0, $limit - $log->emails_sent);
    }

    /** Increment the sent counter on today's log. Thread-safe via atomic DB increment. */
    public function incrementSent(WarmupPlan $plan, int $count = 1): void
    {
        WarmupLog::where('warmup_plan_id', $plan->id)
            ->whereDate('date', today())
            ->increment('emails_sent', $count);
    }

    /** Increment the failed counter on today's log. */
    public function incrementFailed(WarmupPlan $plan, int $count = 1): void
    {
        WarmupLog::where('warmup_plan_id', $plan->id)
            ->whereDate('date', today())
            ->increment('emails_failed', $count);
    }

    /**
     * Refresh today's bounce/complaint counts from EmailList table.
     * Call this once per command run, not per email.
     */
    public function refreshSafetyCounters(WarmupPlan $plan): void
    {
        $bounces    = \App\Models\EmailList::whereDate('bounced_at',    today())->count();
        $complaints = \App\Models\EmailList::whereDate('complained_at', today())->count();

        WarmupLog::where('warmup_plan_id', $plan->id)
            ->whereDate('date', today())
            ->update([
                'bounce_count'    => $bounces,
                'complaint_count' => $complaints,
            ]);
    }
}
