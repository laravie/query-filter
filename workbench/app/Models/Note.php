<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    public function notable()
    {
        return $this->morphTo();
    }
}
