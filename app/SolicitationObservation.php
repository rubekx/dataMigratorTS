<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SolicitationObservation extends Model
{
    //

    public function profile()
    {
        return $this->belongsTo('App\Profile');
    }

    public function current_status()
    {
        return $this->belongsTo('App\Status');
    }

    public function solicitation()
    {
        return $this->belongsTo('App\Solicitation');
    }
}
