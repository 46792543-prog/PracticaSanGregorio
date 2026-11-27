<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Licencia / inasistencia de un docente entre dos fechas. Sus "clases" son las
 * horas cátedra que pierde (según el Horario). Se descuenta si:
 *  - el tipo es sin goce de sueldo, o
 *  - es con goce pero la documentación está Pendiente (hasta que la presente).
 */
class LicenciaDocente extends Model
{
    // Lo que se elige en pantalla. Las licencias sin goce no llevan documentación (se guardan como "No corresponde").
    public const DOCUMENTACION = ['Presentada', 'Pendiente'];

    protected $table = 'licencia_docente';
    protected $primaryKey = 'id_licencia';

    protected $fillable = ['id_profesor', 'id_tipo_licencia', 'desde', 'hasta', 'documentacion', 'fecha_presentacion', 'comprobante', 'observaciones', 'id_persona_registra'];

    protected $casts = ['desde' => 'date', 'hasta' => 'date', 'fecha_presentacion' => 'date'];

    public function getDescuentaAttribute(): bool
    {
        return ! $this->tipo->con_goce || $this->documentacion === 'Pendiente';
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'id_profesor', 'id_profesor');
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoLicencia::class, 'id_tipo_licencia', 'id_tipo_licencia');
    }

    public function registra(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona_registra', 'id_persona');
    }

    public function clases(): HasMany
    {
        return $this->hasMany(InasistenciaDocente::class, 'id_licencia', 'id_licencia');
    }
}
