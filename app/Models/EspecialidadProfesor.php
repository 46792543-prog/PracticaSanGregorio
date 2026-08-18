<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EspecialidadProfesor extends Model
{
    protected $table = 'especialidad_profesor';
    protected $primaryKey = 'id_especialidad';

    protected $fillable = ['nombre_especialidad'];
}
