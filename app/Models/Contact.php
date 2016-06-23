<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function contact()
    {
        return $this->belongsTo('App\Models\User', 'contact_user_id');
    }
}
