<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurnoCursada extends Model
{
    protected $table = 'turno_cursada';
    protected $primaryKey = 'id_turno_cursada';

    protected $fillable = ['nombre_turno'];
}
