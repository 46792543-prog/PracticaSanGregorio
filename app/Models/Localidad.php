<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Localidad extends Model
{
    protected $table = 'localidad';
    protected $primaryKey = 'id_localidad';

    protected $fillable = ['id_provincia', 'nombre'];

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class, 'id_provincia', 'id_provincia');
    }
}
