<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailList extends Model
{
    protected $fillable = ['email', 'name', 'status', 'group_id'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(EmailGroup::class, 'group_id');
    }
}
