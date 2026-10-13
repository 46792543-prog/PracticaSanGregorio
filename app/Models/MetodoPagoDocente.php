<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetodoPagoDocente extends Model
{
    protected $table = 'metodo_pago_docente';
    protected $primaryKey = 'id_metodo_pago_docente';

    protected $fillable = ['nombre_metodo'];
}
