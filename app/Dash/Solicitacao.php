<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class Solicitacao extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'tb_solicitacao';
    protected $primaryKey = 'codigo';
    public $timestamps = false;
}
