<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailGroup extends Model
{
    protected $fillable = ['name', 'description'];

    public function emails(): HasMany
    {
        return $this->hasMany(EmailList::class, 'group_id');
    }

    public function pendingEmails(): HasMany
    {
        return $this->emails()->where('status', 'pending');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'email_group_id');
    }
}
