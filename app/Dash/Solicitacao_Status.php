<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class Solicitacao_Status extends Model
{
  protected $connection= 'mysql2';
  protected $table = 'tb_solicitacao_status';
}
