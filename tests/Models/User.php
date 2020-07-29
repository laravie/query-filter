<?php

namespace Laravie\QueryFilter\Tests\Models;

class User extends \Illuminate\Foundation\Auth\User
{
    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
