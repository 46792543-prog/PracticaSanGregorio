<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurnoExamen extends Model
{
    protected $table = 'turno_examen';
    protected $primaryKey = 'id_turno';

    protected $fillable = ['nombre_turno'];
}
