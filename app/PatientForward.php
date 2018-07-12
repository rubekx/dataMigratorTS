<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientForward extends Model
{
    protected $table = "patient_forwards";
    public $timestamps = false;

    public function solicitation()
    {
        return $this->belongsTo('App\Solicitation');
    }

    public function patient()
    {
        return $this->belongsTo('App\Patient');
    }

}
