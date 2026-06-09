<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarmupPlan extends Model
{
    protected $fillable = [
        'name', 'domain', 'provider', 'email_group_id', 'email_template_id',
        'start_date', 'end_date', 'day1_emails', 'increase_factor',
        'current_day', 'status', 'daily_limit',
        'max_bounce_rate', 'max_complaint_rate', 'pause_reason',
    ];

    protected $casts = [
        'start_date'          => 'date',
        'end_date'            => 'date',
        'day1_emails'         => 'integer',
        'increase_factor'     => 'integer',
        'current_day'         => 'integer',
        'daily_limit'         => 'integer',
        'max_bounce_rate'     => 'decimal:2',
        'max_complaint_rate'  => 'decimal:2',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(EmailGroup::class, 'email_group_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WarmupLog::class);
    }

    public function todayLog(): ?WarmupLog
    {
        return $this->logs()->whereDate('date', today())->first();
    }

    public function totalSent(): int
    {
        return (int) $this->logs()->sum('emails_sent');
    }

    public function totalTarget(): int
    {
        return (int) $this->logs()->sum('daily_limit');
    }

    public function progressPercent(): int
    {
        // Progress toward full warmup (Day 15 = 10 000/day considered complete)
        $maxDay = 15;
        return min(100, (int) round(($this->current_day / $maxDay) * 100));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // Effective day number based on calendar, capped at current_day stored on model
    public function calendarDay(): int
    {
        return max(1, (int) $this->start_date->diffInDays(today()) + 1);
    }
}
