<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FU extends Model
{
    protected $table = 'fus';
    public $timestamps = false;

    public function center()
    {
        return $this->hasMany('App\Center');
    }

    public function city()
    {
        return $this->hasMany('App\City');
    }

}
