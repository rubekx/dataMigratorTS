<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Unit extends Model
{
    protected $table = 'units';

    public function city()
    {
        return $this->belongsTo('App\City');
    }

    public function team()
    {
        return $this->hasMany('App\Team');
    }

    public function hashId()
    {
        return Hashids::encode($this->id);
    }
}
