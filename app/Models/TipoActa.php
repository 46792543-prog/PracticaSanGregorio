<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoActa extends Model
{
    protected $table = 'tipo_acta';
    protected $primaryKey = 'id_tipo_acta';

    protected $fillable = ['nombre_tipo'];
}
