<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class Solicitacao_Datas_Timestamp extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'tb_solicitacao_data_timestamp';
    protected $primaryKey = 'codigo';
    public $timestamps = false;
}
