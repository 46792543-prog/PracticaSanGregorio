<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FichaCampoOpcion extends Model
{
    protected $table = 'ficha_campo_opcion';
    protected $primaryKey = 'id_opcion';

    protected $fillable = [
        'id_campo',
        'valor',
        'orden',
    ];

    public function campo(): BelongsTo
    {
        return $this->belongsTo(FichaCampo::class, 'id_campo', 'id_campo');
    }
}
