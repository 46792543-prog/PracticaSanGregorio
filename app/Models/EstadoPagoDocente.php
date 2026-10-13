<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoPagoDocente extends Model
{
    protected $table = 'estado_pago_docente';
    protected $primaryKey = 'id_estado_pago_docente';

    protected $fillable = ['nombre_estado'];
}
