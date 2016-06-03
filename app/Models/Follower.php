<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Follower extends Model
{
    use SoftDeletes;

    public function group()
    {
        return $this->belongsTo('App\Models\Group');
    }

    public function follower_user()
    {
        return $this->belongsTo('App\Models\User', 'follower_id');
    }

    public function followee_user()
    {
        return $this->belongsTo('App\Models\User', 'followee_id');
    }
}
