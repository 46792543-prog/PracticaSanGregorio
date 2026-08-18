<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoCarrera extends Model
{
    protected $table = 'estado_carrera';
    protected $primaryKey = 'id_estado_carrera';

    protected $fillable = ['nombre_estado'];
}
