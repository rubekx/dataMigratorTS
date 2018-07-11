<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class SolEncaminhamento extends Model
{
  protected $connection= 'mysql2';
  protected $table = 'tb_solicitacao_encaminhamento';
}
