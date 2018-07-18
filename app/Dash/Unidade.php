<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class Unidade extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'tb_solicitante_unidade';
    protected $primaryKey = 'codigo';
    public $timestamps = false;
}
