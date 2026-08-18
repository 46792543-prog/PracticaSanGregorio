<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionInstitucion extends Model
{
    protected $table = 'configuracion_institucion';
    protected $primaryKey = 'id_configuracion';

    protected $fillable = [
        'nombre_institucion',
        'direccion',
        'nombre_director',
        'telefono_contacto',
        'email_contacto',
        'fecha_ultima_modificacion',
        'id_secretario_modifica',
    ];

    protected $casts = [
        'fecha_ultima_modificacion' => 'datetime',
    ];

    public function secretarioModifica(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_secretario_modifica', 'id_persona');
    }
}
