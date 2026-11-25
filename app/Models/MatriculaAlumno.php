<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatriculaAlumno extends Model
{
    protected $table = 'matricula_alumno';
    protected $primaryKey = 'id_matricula';

    protected $fillable = [
        'id_persona_alumno',
        'id_anio_lectivo',
        'id_carrera', // una matrícula por carrera y por año
        'monto',
        'pagado',
        'fecha_pago',
        'id_movimiento_caja',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'pagado' => 'boolean',
        'fecha_pago' => 'date',
    ];

    public function personaAlumno(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_alumno', 'id_persona');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class, 'id_anio_lectivo', 'id_anio_lectivo');
    }

    public function configuracion(): ?ConfiguracionMatricula
    {
        return ConfiguracionMatricula::where('id_anio_lectivo', $this->id_anio_lectivo)->first();
    }

    /**
     * Primer día del mes desde el que se puede cobrar (Configuración de
     * Matrícula → "Mes desde el que se habilita el pago"). Null si no hay
     * configuración para ese año.
     */
    public function fechaHabilitacion(): ?Carbon
    {
        $configuracion = $this->configuracion();
        $anio = $this->anioLectivo?->anio;

        return $configuracion && $anio
            ? Carbon::create($anio, $configuracion->mes_habilitacion, 1)->startOfDay()
            : null;
    }

    /**
     * Fecha límite de pago: el "día límite" del mes de habilitación.
     */
    public function fechaVencimiento(): ?Carbon
    {
        $configuracion = $this->configuracion();
        $anio = $this->anioLectivo?->anio;

        return $configuracion && $anio
            ? Carbon::create($anio, $configuracion->mes_habilitacion, $configuracion->dia_limite)->endOfDay()
            : null;
    }

    public function estaHabilitada(): bool
    {
        $desde = $this->fechaHabilitacion();

        return ! $desde || now()->greaterThanOrEqualTo($desde);
    }

    public function estaVencida(): bool
    {
        $hasta = $this->fechaVencimiento();

        return ! $this->pagado && $hasta && now()->greaterThan($hasta);
    }

    public function movimientoCaja(): BelongsTo
    {
        return $this->belongsTo(MovimientoCaja::class, 'id_movimiento_caja', 'id_movimiento');
    }
}
