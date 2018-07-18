<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class Solcod_Ibge extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'tb_solicitacao_solcod_ibge';
    protected $primaryKey = 'codigo';
    public $timestamps = false;
}
