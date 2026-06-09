<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainHealthCheck extends Model
{
    protected $fillable = [
        'domain',
        'spf_status',
        'dkim_status',
        'dmarc_status',
        'spf_record',
        'dkim_record',
        'dmarc_record',
        'dkim_selector',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function overallScore(): int
    {
        $valid = collect([$this->spf_status, $this->dkim_status, $this->dmarc_status])
            ->filter(fn ($s) => $s === 'valid')
            ->count();

        return (int) round(($valid / 3) * 100);
    }

    public function overallStatus(): string
    {
        $score = $this->overallScore();
        if ($score === 100) return 'healthy';
        if ($score >= 67)  return 'warning';
        return 'critical';
    }
}
