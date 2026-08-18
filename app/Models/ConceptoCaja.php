<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConceptoCaja extends Model
{
    protected $table = 'concepto_caja';
    protected $primaryKey = 'id_concepto';

    protected $fillable = ['id_tipo_movimiento', 'nombre_concepto'];

    public function tipoMovimiento()
    {
        return $this->belongsTo(TipoMovimiento::class, 'id_tipo_movimiento', 'id_tipo_movimiento');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class, 'id_concepto', 'id_concepto');
    }
}
