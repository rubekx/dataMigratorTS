<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Vinkla\Hashids\Facades\Hashids;

class Profile extends Model
{
    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    public function cbo()
    {
        return $this->belongsTo('App\CBO');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function status()
    {
        return $this->belongsTo('App\Status');
    }

    public function centerProfile()
    {
        return $this->hasOne('App\CenterProfile');
    }

    public function hashId()
    {
        return Hashids::encode($this->id);
    }

    public function profile_team()
    {
        return $this->hasOne('App\ProfileTeam');
    }

    public function solicitationForward()
    {
        return $this->hasMany('App\SolicitationForward');
    }

    public function solicitation()
    {
        return $this->hasMany('App\Solicitation');
    }

    public function observation()
    {
        return $this->hasMany('App\SolicitationObservation');
    }

}
