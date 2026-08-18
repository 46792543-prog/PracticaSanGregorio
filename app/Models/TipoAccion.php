<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoAccion extends Model
{
    protected $table = 'tipo_accion';
    protected $primaryKey = 'id_tipo_accion';

    protected $fillable = ['nombre_tipo'];
}
