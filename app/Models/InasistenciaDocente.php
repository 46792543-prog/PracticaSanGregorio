<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Una clase que el docente pierde por una licencia: designación, día y horas cátedra.
class InasistenciaDocente extends Model
{
    protected $table = 'inasistencia_docente';
    protected $primaryKey = 'id_inasistencia';

    protected $fillable = ['id_licencia', 'id_asignacion', 'fecha', 'horas_catedra'];

    protected $casts = ['fecha' => 'date', 'horas_catedra' => 'integer'];

    public function licencia(): BelongsTo
    {
        return $this->belongsTo(LicenciaDocente::class, 'id_licencia', 'id_licencia');
    }

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionProfesorMateria::class, 'id_asignacion', 'id_asignacion');
    }
}
