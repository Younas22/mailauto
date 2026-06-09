<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarmupLog extends Model
{
    protected $fillable = [
        'warmup_plan_id', 'date', 'daily_limit',
        'emails_sent', 'emails_failed',
        'bounce_count', 'complaint_count', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(WarmupPlan::class, 'warmup_plan_id');
    }

    public function bounceRate(): float
    {
        if ($this->emails_sent === 0) return 0.0;
        return round($this->bounce_count / $this->emails_sent * 100, 2);
    }

    public function complaintRate(): float
    {
        if ($this->emails_sent === 0) return 0.0;
        return round($this->complaint_count / $this->emails_sent * 100, 2);
    }
}
