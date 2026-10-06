<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoDocente extends Model
{
    protected $table = 'pago_docente';
    protected $primaryKey = 'id_pago_docente';

    protected $fillable = [
        'id_profesor',
        'periodo',
        'fecha_pago',
        'monto',
        'id_metodo_pago_docente',
        'id_estado_pago_docente',
        'observaciones',
        'id_director_registra',
    ];

    protected $casts = [
        'periodo' => 'date',
        'fecha_pago' => 'date',
        'monto' => 'decimal:2',
    ];

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'id_profesor', 'id_profesor');
    }

    public function metodoPago(): BelongsTo
    {
        return $this->belongsTo(MetodoPagoDocente::class, 'id_metodo_pago_docente', 'id_metodo_pago_docente');
    }

    public function estadoPago(): BelongsTo
    {
        return $this->belongsTo(EstadoPagoDocente::class, 'id_estado_pago_docente', 'id_estado_pago_docente');
    }

    public function directorRegistra(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_director_registra', 'id_persona');
    }
}
