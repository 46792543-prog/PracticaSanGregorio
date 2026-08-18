<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioAsignacion extends Model
{
    protected $table = 'horario_asignacion';
    protected $primaryKey = 'id_horario';

    protected $fillable = [
        'id_asignacion',
        'dia_semana',
        'hora_desde',
        'hora_fin',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionProfesorMateria::class, 'id_asignacion', 'id_asignacion');
    }
}
