<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoAnioLectivo extends Model
{
    protected $table = 'estado_anio_lectivo';
    protected $primaryKey = 'id_estado_anio';

    protected $fillable = ['nombre_estado'];
}
