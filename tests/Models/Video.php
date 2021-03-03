<?php

namespace Laravie\QueryFilter\Tests\Models;

class Video extends \Illuminate\Database\Eloquent\Model
{
    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
