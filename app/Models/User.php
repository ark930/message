<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_name', 'nick_name', 'email', 'tel', 'api_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'verify_code', 'verify_code_expire_at', 'verify_code_refresh_at', 'deleted_at',
    ];

    public function groups()
    {
        return $this->belongsToMany('App\Models\Group', 'user_groups');
    }

}
