<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{
  protected $connection= 'mysql2'; 
  protected $table = 'tb_solicitante_pessoa';
}
