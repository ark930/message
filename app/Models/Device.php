<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'ip', 'client', 'active', 'api_token',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
