<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProfileTeam extends Model
{
    protected $table = 'profiles_teams';

    public function profile()
    {
        return $this->belongsTo('App\Profile');
    }

    public function team()
    {
        return $this->belongsTo('App\Team');
    }
}
