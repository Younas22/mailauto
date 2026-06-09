<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailList extends Model
{
    protected $fillable = [
        'email', 'name', 'status', 'group_id',
        'unsubscribe_token', 'unsubscribed_at',
        'is_do_not_mail', 'bounced_at', 'complained_at',
    ];

    protected $casts = [
        'unsubscribed_at' => 'datetime',
        'bounced_at'      => 'datetime',
        'complained_at'   => 'datetime',
        'is_do_not_mail'  => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(EmailGroup::class, 'group_id');
    }

    public function getUnsubscribeTokenAttribute(?string $value): string
    {
        if (!$value) {
            $token = Str::random(64);
            $this->updateQuietly(['unsubscribe_token' => $token]);
            return $token;
        }
        return $value;
    }

    public function isUnsubscribed(): bool
    {
        return $this->unsubscribed_at !== null;
    }

    public function isDoNotMail(): bool
    {
        return $this->is_do_not_mail || $this->bounced_at !== null;
    }
}
