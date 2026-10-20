<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'id_persona', 'email', 'password', 'debe_cambiar_clave', 'id_rol', 'id_estado',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'debe_cambiar_clave' => 'boolean',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function estadoUsuario()
    {
        return $this->belongsTo(EstadoUsuario::class, 'id_estado', 'id_estado');
    }

    // Proxies de identidad: la persona es quien tiene estos datos, no el usuario.
    public function getNombreAttribute()
    {
        return $this->persona->nombre ?? null;
    }

    public function getApellidoAttribute()
    {
        return $this->persona->apellido ?? null;
    }

    public function getDniAttribute()
    {
        return $this->persona->dni ?? null;
    }

    public function getFechaNacimientoAttribute()
    {
        return $this->persona->fecha_nacimiento ?? null;
    }

    public function getTelefonoAttribute()
    {
        return $this->persona->telefono ?? null;
    }

    public function getDireccionAttribute()
    {
        return $this->persona->direccion ?? null;
    }

    public function getLocalidadAttribute()
    {
        return $this->persona->localidad ?? null;
    }

    public function esAlumno(): bool
    {
        return $this->rol?->nombre_rol === 'Alumno';
    }

    public function esSecretario(): bool
    {
        return $this->rol?->nombre_rol === 'Secretario';
    }

    public function esDirector(): bool
    {
        return $this->rol?->nombre_rol === 'Director';
    }

    public function esStaff(): bool
    {
        return in_array($this->rol?->nombre_rol, ['Secretario', 'Director'], true);
    }

    /** Panel al que va cada usuario al iniciar sesión (o si intenta entrar a uno que no le corresponde). */
    public function rutaInicio(): string
    {
        return match (true) {
            $this->esDirector() => route('director.panel.index'),
            $this->esStaff() => route('admin.panel.index'),
            default => route('panel.index'),
        };
    }

    public function scopeAlumnos($query)
    {
        return $query->whereHas('rol', fn ($q) => $q->where('nombre_rol', 'Alumno'));
    }

    public function scopeSecretarios($query)
    {
        return $query->whereHas('rol', fn ($q) => $q->where('nombre_rol', 'Secretario'));
    }

    public function scopeDirectores($query)
    {
        return $query->whereHas('rol', fn ($q) => $q->where('nombre_rol', 'Director'));
    }
}
