<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Vinkla\Hashids\Facades\Hashids;

class FileAttachment extends Model
{
    protected $table = 'file_attachments';

    public function solicitation()
    {
        return $this->belongsTo('App\Solicitation');
    }

    public function answer()
    {
        return $this->belongsTo('App\Answer');
    }

    public function solicitationForward()
    {
        return $this->belongsTo('App\SolicitationForward');
    }

    public function hashId()
    {
        return Hashids::encode($this->id);
    }
}
