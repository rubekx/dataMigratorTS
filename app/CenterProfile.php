<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CenterProfile extends Model
{
    protected $table = 'centers_profiles';

    public function profile()
    {
        return $this->belongsTo('App\Profile');
    }


    public function center()
    {
        return $this->belongsTo('App\Center');
    }
}
