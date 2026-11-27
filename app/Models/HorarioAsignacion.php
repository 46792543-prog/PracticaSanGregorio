<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Una clase de una designación: un día, en un módulo de la grilla (con su hora).
class HorarioAsignacion extends Model
{
    protected $table = 'horario_asignacion';
    protected $primaryKey = 'id_horario';

    protected $fillable = [
        'id_asignacion',
        'id_horario_modulo',
        'dia_semana',
        'hora_desde',
        'hora_fin',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionProfesorMateria::class, 'id_asignacion', 'id_asignacion');
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(HorarioModulo::class, 'id_horario_modulo', 'id_horario_modulo');
    }

    // Minutos que dura la clase.
    public function getMinutosAttribute(): int
    {
        [$h1, $m1] = array_map('intval', explode(':', substr((string) $this->hora_desde, 0, 5)));
        [$h2, $m2] = array_map('intval', explode(':', substr((string) $this->hora_fin, 0, 5)));

        return max(0, ($h2 * 60 + $m2) - ($h1 * 60 + $m1));
    }
}
