<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CID extends Model
{
    protected $table = 'cids';
    public $timestamps = false;

    public function solicitation()
    {
        return $this->hasMany('App\Solicitation');
    }
}
