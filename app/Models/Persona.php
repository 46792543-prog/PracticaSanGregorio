<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'persona';
    protected $primaryKey = 'id_persona';

    protected $fillable = [
        'dni', 'fecha_nacimiento', 'nombre', 'apellido', 'telefono', 'direccion', 'localidad',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id_persona', 'id_persona');
    }

    public function profesor()
    {
        return $this->hasOne(Profesor::class, 'id_persona', 'id_persona');
    }

    public function inscripcionesCarrera()
    {
        return $this->hasMany(InscripcionCarrera::class, 'id_persona_alumno', 'id_persona');
    }

    public function historialAlumno()
    {
        return $this->hasMany(HistorialAlumno::class, 'id_persona_alumno', 'id_persona');
    }

    public function cuotas()
    {
        return $this->hasMany(CuotaAlumno::class, 'id_persona_alumno', 'id_persona');
    }

    public function inscripcionesMesa()
    {
        return $this->hasMany(InscripcionMesa::class, 'id_persona_alumno', 'id_persona');
    }

    public function documentacion()
    {
        return $this->hasMany(ControlDocumentacion::class, 'id_persona_alumno', 'id_persona');
    }

    public function acciones()
    {
        return $this->hasMany(Acciones::class, 'id_persona', 'id_persona');
    }

    public function equivalencias()
    {
        return $this->hasMany(Equivalencia::class, 'id_persona_alumno', 'id_persona');
    }
}
