<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Vinkla\Hashids\Facades\Hashids;

class Answer extends Model
{
    public function solicitation()
    {
        return $this->belongsTo('App\Solicitation');
    }

    public function profile()
    {
        return $this->belongsTo('App\Profile');
    }


    public function hashId()
    {
        return Hashids::encode($this->id);
    }
}
