<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dni',
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'telefono',
        'direccion',
        'localidad',
        'email',
        'password',
        'rol',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'fecha_nacimiento' => 'date',
    ];

    public function inscripcionesCarrera(): HasMany
    {
        return $this->hasMany(InscripcionCarrera::class);
    }

    public function documentosAlumno(): HasMany
    {
        return $this->hasMany(DocumentoAlumno::class);
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(Cuota::class);
    }

    public function historialMaterias(): HasMany
    {
        return $this->hasMany(HistorialMateria::class);
    }

    public function inscripcionesMesa(): HasMany
    {
        return $this->hasMany(InscripcionMesa::class);
    }

    public function esAlumno(): bool
    {
        return $this->rol === 'alumno';
    }

    public function esSecretario(): bool
    {
        return $this->rol === 'secretario';
    }

    public function esDirector(): bool
    {
        return $this->rol === 'director';
    }

    public function esStaff(): bool
    {
        return in_array($this->rol, ['secretario', 'director']);
    }
}
