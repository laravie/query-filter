<?php

namespace Laravie\QueryFilter\Tests\Models;

class User extends \Illuminate\Foundation\Auth\User
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
