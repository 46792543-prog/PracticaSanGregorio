<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionMatricula extends Model
{
    protected $table = 'configuracion_matricula';
    protected $primaryKey = 'id_configuracion_matricula';

    protected $fillable = [
        'id_anio_lectivo',
        'id_carrera', // null = monto general (todas las carreras sin configuración propia)
        'monto',
        'mes_habilitacion',
        'dia_limite',
        'concepto',
    ];

    protected $casts = [
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

    /**
     * Configuración vigente de un año para una carrera: la propia de esa
     * carrera si existe, si no la general (id_carrera null).
     */
    public static function de(?int $idAnioLectivo, ?int $idCarrera): ?self
    {
        return self::where('id_anio_lectivo', $idAnioLectivo)->where('id_carrera', $idCarrera)->first()
            ?? self::where('id_anio_lectivo', $idAnioLectivo)->whereNull('id_carrera')->first();
    }
}
