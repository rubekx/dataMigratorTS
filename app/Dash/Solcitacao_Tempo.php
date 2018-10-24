<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class Solcitacao_Tempo extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'tb_solicitacao_tempo';
    protected $primaryKey = 'codigo';
    public $timestamps = false;
}
