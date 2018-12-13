<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'statu';
    public $timestamps = false;

    public function statusHistory()
    {
        return $this->hasMany('App\StatusHistory');
    }

    public function solicitation()
    {
        return $this->hasMany('App\Solicitation');
    }

}
