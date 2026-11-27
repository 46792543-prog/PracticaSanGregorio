<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrera extends Model
{
    use HasFactory;

    protected $table = 'carrera';
    protected $primaryKey = 'id_carrera';

    protected $fillable = [
        'nombre_carrera',
        'familia_profesional',
        'duracion_anos',
        'resolucion_ministerial',
        'id_estado_carrera',
    ];

    public function getNombreAttribute()
    {
        return $this->nombre_carrera;
    }

    public function estadoCarrera(): BelongsTo
    {
        return $this->belongsTo(EstadoCarrera::class, 'id_estado_carrera', 'id_estado_carrera');
    }

    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class, 'id_carrera', 'id_carrera');
    }

    // Turnos en que se dicta la carrera (las mismas materias en cada turno).
    public function turnos(): BelongsToMany
    {
        return $this->belongsToMany(TurnoCursada::class, 'carrera_turno', 'id_carrera', 'id_turno_cursada')->orderBy('turno_cursada.id_turno_cursada');
    }

    public function ofreceTurno(int|string|null $idTurno): bool
    {
        return $this->turnos()->where('turno_cursada.id_turno_cursada', $idTurno)->exists();
    }

    public function planes(): HasMany
    {
        return $this->hasMany(PlanEstudio::class, 'id_carrera', 'id_carrera')->orderByDesc('anio_desde');
    }

    // Plan al que entra un alumno nuevo ese año (el más nuevo que acepte inscripciones).
    public function planParaInscribir(int $anio): ?PlanEstudio
    {
        return $this->planes()->get()->first(fn (PlanEstudio $p) => $p->aceptaInscripciones($anio));
    }

    public function inscripcionesCarrera(): HasMany
    {
        return $this->hasMany(InscripcionCarrera::class, 'id_carrera', 'id_carrera');
    }

    public function documentoRequisitos(): HasMany
    {
        return $this->hasMany(DocumentoRequisito::class, 'id_carrera', 'id_carrera');
    }
}
