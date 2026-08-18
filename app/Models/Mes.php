<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mes extends Model
{
    protected $table = 'mes';
    protected $primaryKey = 'id_mes';

    protected $fillable = ['nombre_mes'];
}
