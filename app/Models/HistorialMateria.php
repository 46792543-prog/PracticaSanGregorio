<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialMateria extends Model
{
    protected $table = 'historial_materias';

    protected $fillable = [
        'user_id',
        'materia_id',
        'anio_lectivo_id',
        'condicion',
        'nota_cursada',
        'fecha_ultima_modificacion',
    ];

    protected $casts = [
        'nota_cursada' => 'decimal:2',
        'fecha_ultima_modificacion' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function anioLectivo(): BelongsTo
    {
        return $this->belongsTo(AnioLectivo::class);
    }
}
