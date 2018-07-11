<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Vinkla\Hashids\Facades\Hashids;

class Center extends Model
{
    protected $table = 'centers';

    public function fu()
    {
        return $this->belongsTo('App\FU');
    }

    public function city()
    {
        return $this->hasMany('App\City');
    }

    public function hashId()
    {
        return Hashids::encode($this->id);
    }

}
