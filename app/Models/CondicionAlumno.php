<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CondicionAlumno extends Model
{
    protected $table = 'condicion_alumno';
    protected $primaryKey = 'id_condicion';

    protected $fillable = ['nombre_condicion'];
}
