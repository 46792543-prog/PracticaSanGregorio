<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCorrelativa extends Model
{
    protected $table = 'tipo_correlativa';
    protected $primaryKey = 'id_tipo_correlativa';

    protected $fillable = ['nombre_tipo'];
}
