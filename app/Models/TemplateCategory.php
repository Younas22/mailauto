<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateCategory extends Model
{
    protected $fillable = ['name'];

    public function templates()
    {
        return $this->hasMany(EmailTemplate::class, 'category', 'name');
    }
}
