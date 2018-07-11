<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
  public $timestamps = false;

    public function fu()
    {
        return $this->belongsTo('App\FU');
    }

    public function center()
    {
        return $this->belongsTo('App\Center');
    }

    public function unit()
    {
        return $this->hasMany('App\Unit');
    }

}
