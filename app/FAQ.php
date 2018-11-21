<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class FAQ extends Model
{
    protected $table = 'faq';


    public function hashId()
    {
        return Hashids::encode($this->id);
    }

}
