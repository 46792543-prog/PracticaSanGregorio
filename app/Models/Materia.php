<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    use HasFactory;

    protected $table = 'materia';
    protected $primaryKey = 'id_materia';

    protected $fillable = [
        'id_carrera',
        'numero_orden',
        'id_anio_cursada',
        'id_periodo',
        'id_regimen',
        'id_nombre_materia',
        'version_plan',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id_carrera');
    }

    public function anioCursada(): BelongsTo
    {
        return $this->belongsTo(AnioCursada::class, 'id_anio_cursada', 'id_anio_cursada');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoDictado::class, 'id_periodo', 'id_periodo');
    }

    public function regimen(): BelongsTo
    {
        return $this->belongsTo(RegimenAprobacion::class, 'id_regimen', 'id_regimen');
    }

    public function nombreMateria(): BelongsTo
    {
        return $this->belongsTo(NombreMateria::class, 'id_nombre_materia', 'id_nombre_materia');
    }

    public function getNombreAttribute()
    {
        return $this->nombreMateria->nombre ?? null;
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialAlumno::class, 'id_materia', 'id_materia');
    }

    public function mesasExamen(): HasMany
    {
        return $this->hasMany(MesaExamen::class, 'id_materia', 'id_materia');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionProfesorMateria::class, 'id_materia', 'id_materia');
    }

    /**
     * Materias que esta materia necesita como correlativa.
     */
    public function requisitos(): BelongsToMany
    {
        return $this->belongsToMany(
            Materia::class,
            'correlativa',
            'id_materia_principal',
            'id_materia_requisito'
        )->withPivot('id_tipo_correlativa', 'requiere_regularizada', 'requiere_aprobada');
    }

    /**
     * Materias que tienen a esta materia como correlativa.
     */
    public function esRequisitoDe(): BelongsToMany
    {
        return $this->belongsToMany(
            Materia::class,
            'correlativa',
            'id_materia_requisito',
            'id_materia_principal'
        )->withPivot('id_tipo_correlativa', 'requiere_regularizada', 'requiere_aprobada');
    }

    /**
     * Devuelve la primera correlativa que el alumno todavía no cumple para
     * cursar/rendir esta materia, o null si tiene todas cumplidas.
     */
    public function correlativaFaltante(Persona $alumno): ?Materia
    {
        $historialPorMateria = $alumno->historialAlumno()
            ->whereIn('id_materia', $this->requisitos->pluck('id_materia'))
            ->get()
            ->keyBy('id_materia');

        foreach ($this->requisitos as $requisito) {
            $historial = $historialPorMateria->get($requisito->id_materia);
            $condicionNombre = $historial?->condicion?->nombre_condicion;
            $regularVigente = $condicionNombre === 'Regular' && ! ($historial?->regularidad_vencida ?? false);

            $cumplida = $requisito->pivot->requiere_aprobada
                ? $condicionNombre === 'Aprobada'
                : (! $requisito->pivot->requiere_regularizada || $condicionNombre === 'Aprobada' || $regularVigente);

            if (! $cumplida) {
                return $requisito;
            }
        }

        return null;
    }

    /**
     * Igual que correlativaFaltante() pero solo evalúa el requisito "para cursar"
     * (columna requiere_regularizada), ignorando requiere_aprobada que es para rendir.
     */
    public function correlativaFaltanteParaCursar(Persona $alumno): ?Materia
    {
        $historialPorMateria = $alumno->historialAlumno()
            ->whereIn('id_materia', $this->requisitos->pluck('id_materia'))
            ->get()
            ->keyBy('id_materia');

        foreach ($this->requisitos as $requisito) {
            if (! $requisito->pivot->requiere_regularizada) {
                continue;
            }

            $historial = $historialPorMateria->get($requisito->id_materia);
            $condicionNombre = $historial?->condicion?->nombre_condicion;
            $regularVigente = $condicionNombre === 'Regular' && ! ($historial?->regularidad_vencida ?? false);

            if (! ($condicionNombre === 'Aprobada' || $regularVigente)) {
                return $requisito;
            }
        }

        return null;
    }

    /**
     * Historial del alumno para esta materia puntual (el más reciente, por si
     * la recursó en más de un año lectivo).
     */
    public function historialDe(Persona $alumno): ?HistorialAlumno
    {
        return $alumno->historialAlumno()
            ->where('id_materia', $this->id_materia)
            ->latest('id_anio_lectivo')
            ->first();
    }

    /**
     * Motivo por el que el alumno no puede inscribirse a cursar esta materia
     * todavía, o null si no tiene ningún bloqueo. Se usa tanto en el
     * autoservicio del alumno como en la pantalla de secretaría (ahí solo se
     * muestra como aviso, no impide la inscripción manual).
     */
    public function bloqueoParaCursar(Persona $alumno): ?string
    {
        // En Primer Año no hay correlativas previas ni tiempo de haber
        // pagado cuotas o entregado papeles todavía — estos controles solo
        // tienen sentido a partir de 2do año, cuando el alumno ya viene
        // cursando y esas cosas ya deberían estar en regla.
        if ($this->anioCursada?->nombre_anio === 'Primer Año') {
            return null;
        }

        if ($requisito = $this->correlativaFaltanteParaCursar($alumno)) {
            return "Correlativa pendiente: {$requisito->nombre}";
        }

        if ($alumno->cuotas()->where('pagado', false)->exists()) {
            return 'Tiene cuotas pendientes de pago.';
        }

        $documentacionIncompleta = DocumentoRequisito::where('es_obligatorio', true)
            ->where(fn ($q) => $q->whereNull('id_carrera')->orWhere('id_carrera', $this->id_carrera))
            ->whereDoesntHave('controlDocumentacion', fn ($q) => $q->where('id_persona_alumno', $alumno->id_persona)
                ->whereHas('estadoDocumento', fn ($e) => $e->where('nombre_estado', 'Aprobado')))
            ->exists();

        if ($documentacionIncompleta) {
            return 'Tiene documentación obligatoria pendiente de entrega o aprobación.';
        }

        return null;
    }
}
