<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailList extends Model
{
    protected $fillable = ['email', 'name', 'status', 'group_id', 'unsubscribe_token', 'unsubscribed_at'];

    protected $casts = ['unsubscribed_at' => 'datetime'];

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
}
