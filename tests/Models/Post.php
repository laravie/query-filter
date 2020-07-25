<?php

namespace Laravie\QueryFilter\Tests\Models;

class Post extends \Illuminate\Database\Eloquent\Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
