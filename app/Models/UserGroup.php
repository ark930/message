<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    public function group()
    {
        return $this->belongsTo('App\Group');
    }
}
