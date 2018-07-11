<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class Equipe extends Model
{
  protected $connection= 'mysql2';
  protected $table = 'tb_solicitante_equipe';
}
