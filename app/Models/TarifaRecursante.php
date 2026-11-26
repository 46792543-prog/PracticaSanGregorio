<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Precio de la cuota de un recursante según cuántas materias cursa, por año
 * lectivo y carrera (ver migración create_tarifa_recursante_table).
 */
class TarifaRecursante extends Model
{
    protected $table = 'tarifa_recursante';
    protected $primaryKey = 'id_tarifa_recursante';

    protected $fillable = ['id_anio_lectivo', 'id_carrera', 'cantidad_materias', 'monto'];

    protected $casts = [
        'cantidad_materias' => 'integer',
        'monto' => 'decimal:2',
    ];

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    // Lo que paga ESE recursante: el sistema cuenta las materias que está
    // cursando en la carrera y busca el precio de esa cantidad. Null si no
    // cursa ninguna o si no hay precio cargado para esa cantidad.
    public static function montoPara(Persona $alumno, int $idAnioLectivo, int $idCarrera): ?float
    {
        $monto = self::where('id_anio_lectivo', $idAnioLectivo)
            ->where('id_carrera', $idCarrera)
            ->where('cantidad_materias', $alumno->materiasCursando($idAnioLectivo, $idCarrera))
            ->value('monto');

        return $monto !== null ? round((float) $monto, 2) : null;
    }
}
