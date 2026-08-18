<?php

namespace App\Models;

use App\Models\Concerns\HasCompositeKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Correlativa extends Model
{
    use HasCompositeKey;

    protected $table = 'correlativa';
    protected $primaryKey = ['id_materia_principal', 'id_materia_requisito'];
    public $incrementing = false;

    protected $fillable = [
        'id_materia_principal',
        'id_materia_requisito',
        'id_tipo_correlativa',
        'requiere_regularizada',
        'requiere_aprobada',
    ];

    protected $casts = [
        'requiere_regularizada' => 'boolean',
        'requiere_aprobada' => 'boolean',
    ];

    public function materiaPrincipal(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'id_materia_principal', 'id_materia');
    }

    public function materiaRequisito(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'id_materia_requisito', 'id_materia');
    }

    public function tipoCorrelativa(): BelongsTo
    {
        return $this->belongsTo(TipoCorrelativa::class, 'id_tipo_correlativa', 'id_tipo_correlativa');
    }
}
