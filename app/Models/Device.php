<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'ip', 'client', 'active', 'api_token',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
