<?php

namespace App\Dash;

use Illuminate\Database\Eloquent\Model;

class SmgtUpdates extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'tb_atualizacao';
    public $timestamps = false;
}
