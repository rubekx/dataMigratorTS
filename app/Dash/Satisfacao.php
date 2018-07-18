<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class Satisfacao extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'tb_solicitacao_satisfacao';
    protected $primaryKey = 'codigo';
    public $timestamps = false;
}
