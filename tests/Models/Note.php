<?php

namespace Laravie\QueryFilter\Tests\Models;

class Note extends \Illuminate\Database\Eloquent\Model
{
    public function notable()
    {
        return $this->morphTo();
    }
}
