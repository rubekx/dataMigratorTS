<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class Regulacao extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'tb_solicitacao_regulador';
    protected $primaryKey = 'codigo';
    public $timestamps = false;
}
