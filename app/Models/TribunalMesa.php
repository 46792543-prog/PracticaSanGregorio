<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TribunalMesa extends Model
{
    protected $table = 'tribunal_mesa';
    protected $primaryKey = 'id_tribunal';

    protected $fillable = [
        'id_mesa',
        'id_profesor',
        'id_rol_tribunal',
    ];

    public function mesaExamen(): BelongsTo
    {
        return $this->belongsTo(MesaExamen::class, 'id_mesa', 'id_mesa');
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class, 'id_profesor', 'id_profesor');
    }

    public function rolTribunal(): BelongsTo
    {
        return $this->belongsTo(RolTribunal::class, 'id_rol_tribunal', 'id_rol_tribunal');
    }
}
