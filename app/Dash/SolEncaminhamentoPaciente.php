<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class SolEncaminhamentoPaciente extends Model
{
  protected $connection= 'mysql2';
  protected $table = 'tb_solicitacao_encaminhamento_paciente';
}
