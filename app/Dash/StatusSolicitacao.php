<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class StatusSolicitacao extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'tb_solicitacao_status';
    protected $primaryKey = 'codigo';
    public $timestamps = false;
}
