<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoMesa extends Model
{
    protected $table = 'estado_mesa';
    protected $primaryKey = 'id_estado_mesa';

    protected $fillable = ['nombre_estado'];
}
