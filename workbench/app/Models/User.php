<?php

namespace Workbench\App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $casts = [
        'address' => 'array',
    ];

    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
