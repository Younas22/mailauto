<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignLog extends Model
{
    protected $fillable = [
        'campaign_id',
        'email_list_id',
        'email_template_id',
        'email',
        'provider',
        'provider_message_id',
        'status',
        'bounce_type',
        'complaint_reason',
        'error_message',
        'event_at',
        'sent_at',
        'tracking_token',
        'open_count',
        'click_count',
        'reply_count',
        'replied_at',
        'replied_by',
    ];

    protected $casts = [
        'sent_at'    => 'datetime',
        'event_at'   => 'datetime',
        'replied_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }
}
