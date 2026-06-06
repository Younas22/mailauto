<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'email_group_id',
        'delay_minutes',
        'status',
        'total_emails',
        'sent_count',
        'failed_count',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function emailGroup(): BelongsTo
    {
        return $this->belongsTo(EmailGroup::class, 'email_group_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CampaignLog::class);
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_emails === 0) return 0;
        $processed = $this->sent_count + $this->failed_count;
        return (int) round($processed / $this->total_emails * 100);
    }

    public function getRemainingAttribute(): int
    {
        return max(0, $this->total_emails - $this->sent_count - $this->failed_count);
    }

    public function getSuccessRateAttribute(): int
    {
        $processed = $this->sent_count + $this->failed_count;
        if ($processed === 0) return 0;
        return (int) round($this->sent_count / $processed * 100);
    }
}
