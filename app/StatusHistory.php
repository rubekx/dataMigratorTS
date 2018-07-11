<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{
    //
    protected $table = 'statuses_history';

    public function solicitation()
    {
        return $this->belongsTo('App\Solicitation');
    }
}
