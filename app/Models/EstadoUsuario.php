<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoUsuario extends Model
{
    protected $table = 'estado_usuario';
    protected $primaryKey = 'id_estado';

    protected $fillable = ['nombre_estado'];
}
