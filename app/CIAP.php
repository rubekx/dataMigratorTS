<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CIAP extends Model
{
    protected $table = 'ciaps';
    public $timestamps = false;

    public function solicitation()
    {
        return $this->hasMany('App\Solicitation');
    }
}
