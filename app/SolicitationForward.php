<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Vinkla\Hashids\Facades\Hashids;

class SolicitationForward extends Model
{
    public function solicitation()
    {
        return $this->belongsTo('App\Solicitation');
    }
    
    public function consultant()
    {
        return $this->belongsTo('App\Profile', 'consultant_profile_id');
    }
    
    public function regulator()
    {
        return $this->belongsTo('App\Profile', 'regulator_profile_id');
    }

    public function hashId()
    {
        return Hashids::encode($this->solicitation_id);
    }

}


