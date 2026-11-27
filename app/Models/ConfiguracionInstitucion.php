<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ConfiguracionInstitucion extends Model
{
    protected $table = 'configuracion_institucion';
    protected $primaryKey = 'id_configuracion';

    protected $fillable = [
        'nombre_institucion',
        'logo_path',
        'direccion',
        'nombre_director',
        'telefono_contacto',
        'email_contacto',
        'horario_atencion',
        'landing_anios_formacion',
        'landing_docentes_cantidad',
        'landing_alumnos_cantidad',
        'landing_egresados_cantidad',
        'fecha_ultima_modificacion',
        'id_secretario_modifica',
        'tope_valor_hora_catedra',
    ];

    protected $casts = [
        'fecha_ultima_modificacion' => 'datetime',
    ];

    public function secretarioModifica(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_secretario_modifica', 'id_persona');
    }

    /**
     * URL del logo, o null si no hay uno cargado o si el archivo no está en esta máquina
     * (ej. una base importada de otra compu): así se muestra el escudo por defecto en vez
     * de una imagen rota.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path || ! Storage::disk('public')->exists($this->logo_path)) {
            return null;
        }

        return asset('storage/'.$this->logo_path);
    }

    /**
     * El logo como data URI (base64), para incrustar en PDFs generados con
     * DomPDF: a diferencia de logo_url, no depende de que el motor de PDF
     * pueda ir a buscar una URL externa.
     */
    public function getLogoBase64Attribute(): ?string
    {
        if (! $this->logo_path || ! Storage::disk('public')->exists($this->logo_path)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($this->logo_path) ?: 'image/png';
        $contenido = Storage::disk('public')->get($this->logo_path);

        return 'data:'.$mime.';base64,'.base64_encode($contenido);
    }
}
