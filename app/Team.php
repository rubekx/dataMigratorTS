<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Team extends Model
{
    public function unit()
    {
        return $this->belongsTo('App\Unit');
    }

    public function type()
    {
        return $this->belongsTo('App\Type');
    }

    public function hashId()
    {
        return Hashids::encode($this->id);
    }

    public function profile_team()
    {
        return $this->hasMany('App\ProfileTeam');
    }
}
