<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
  protected $connection= 'mysql2';
  protected $table = 'tb_solicitante_perfil';
}
