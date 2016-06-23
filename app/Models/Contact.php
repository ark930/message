<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $primaryKey = ['user_id', 'contact_user_id'];
    public $incrementing = false;

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function contact()
    {
        return $this->belongsTo('App\Models\User', 'contact_user_id');
    }

    /**
     * A work around fix to Illegal offset type
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(\Illuminate\Database\Eloquent\Builder $query)
    {
        if (is_array($this->primaryKey)) {
            foreach ($this->primaryKey as $pk) {
                $query->where($pk, '=', $this->original[$pk]);
            }
            return $query;
        } else {
            return parent::setKeysForSaveQuery($query);
        }
    }
}
