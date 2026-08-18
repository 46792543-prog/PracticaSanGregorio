<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnioCursada extends Model
{
    protected $table = 'anio_cursada';
    protected $primaryKey = 'id_anio_cursada';

    protected $fillable = ['nombre_anio'];
}
