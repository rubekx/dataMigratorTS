<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Vinkla\Hashids\Facades\Hashids;

class Solicitation extends Model
{
    public function status()
    {
        return $this->belongsTo('App\Status');
    }

    public function profile()
    {
        return $this->belongsTo('App\Profile');
    }

    public function statusHistory()
    {
        return $this->belongsTo('App\StatusHistory');
    }

    public function hashId()
    {
        return Hashids::encode($this->id);
    }

    public function observation()
    {
        return $this->hasMany('App\SolicitationObservation');
    }

    public function answer()
    {
        return $this->hasOne('App\Answer');
    }

    public function evaluation()
    {
        return $this->hasOne('App\Evaluation');
    }

    public function solicitationForward()
    {
        return $this->hasOne('App\SolicitationForward');
    }

    public function ciap1()
    {
        return $this->belongsTo('App\CIAP',  'ciap1_id');
    }

    public function ciap2()
    {
        return $this->belongsTo('App\CIAP',  'ciap2_id');
    }

    public function ciap3()
    {
        return $this->belongsTo('App\CIAP',  'ciap3_id');
    }

    public function cid1()
    {
        return $this->belongsTo('App\CID',  'cid1_id');
    }

    public function cid2()
    {
        return $this->belongsTo('App\CID',  'cid2_id');
    }

    public function patientForward()
    {
        return $this->hasOne('App\PatientForward',  'solicitation_id');
    }

}
