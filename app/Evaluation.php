<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    function satisfactionStatus()
    {
        return $this->belongsTo('App\Status', 'satisfaction_status_id');
    }

    public function solicitation()
    {
        return $this->belongsTo('App\Solicitation');
    }

}
