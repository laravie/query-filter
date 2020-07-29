<?php

namespace Laravie\QueryFilter\Tests\Models;

class User extends \Illuminate\Foundation\Auth\User
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
