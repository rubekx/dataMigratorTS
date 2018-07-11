<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class Resposta extends Model
{
  protected $connection= 'mysql2';
  protected $table = 'tb_solicitacao_resposta';
}
