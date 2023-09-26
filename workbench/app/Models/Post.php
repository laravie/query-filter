<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
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
