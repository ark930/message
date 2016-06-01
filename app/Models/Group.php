<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Group extends Model
{
    use SoftDeletes;
    
    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'user_groups');
    }
}
