<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'provider',
        'event_type',
        'payload',
        'processed',
        'process_error',
        'received_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'processed'   => 'boolean',
        'received_at' => 'datetime',
    ];
}
