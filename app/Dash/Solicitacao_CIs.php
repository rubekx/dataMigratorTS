<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class Solicitacao_CIs extends Model
{
  protected $connection= 'mysql2';
  protected $table = 'tb_solicitacao_ciap_cid';
}
