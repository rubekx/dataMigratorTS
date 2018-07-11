<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class Solicitacao_Datas extends Model
{
  protected $connection= 'mysql2';
  protected $table = 'tb_solicitacao_data_timestamp';
}
