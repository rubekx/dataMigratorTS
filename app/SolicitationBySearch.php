<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Vinkla\Hashids\Facades\Hashids;

class SolicitationBySearch extends Model
{
    //
    protected $table = 'solicitations_by_search';

    public function answer()
    {
        return $this->belongsTo('App\Answer');
    }

    public function solicitation()
    {
        return $this->belongsTo('App\Solicitation');
    }

    public function faq()
    {
        return $this->belongsTo('App\FAQ');
    }

    public function hashId()
    {
        return Hashids::encode($this->id);
    }
}
