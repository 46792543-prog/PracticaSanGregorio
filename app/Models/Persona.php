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

    /**
     * Estado mes a mes (pagado o no) del ciclo lectivo activo, desde marzo
     * (o desde que se inscribió, si fue después) hasta $hastaMes — sin
     * importar si la cuota de cada mes llegó a generarse o no. Cada item es
     * ['mes' => Mes, 'pagado' => bool].
     */
    public function estadoMensualCuotas(?int $hastaMes = null): \Illuminate\Support\Collection
    {
        $anioLectivoActivo = AnioLectivo::whereHas('estadoAnio', fn ($q) => $q->where('nombre_estado', 'Activo'))->first();

        if (! $anioLectivoActivo) {
            return collect();
        }

        $inscripcion = $this->inscripcionesCarrera()
            ->where('id_anio_lectivo', $anioLectivoActivo->id_anio_lectivo)
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->first();

        if (! $inscripcion) {
            return collect();
        }

        $inicioCiclo = max(3, $inscripcion->fecha_inscripcion->month); // el ciclo lectivo arranca en marzo
        $hastaMes ??= now()->month;

        if ($hastaMes < $inicioCiclo) {
            return collect();
        }

        $mesesPagados = $this->cuotas()
            ->where('id_anio_lectivo', $anioLectivoActivo->id_anio_lectivo)
            ->where('pagado', true)
            ->pluck('id_mes');

        return Mes::whereBetween('id_mes', [$inicioCiclo, min($hastaMes, 12)])
            ->orderBy('id_mes')
            ->get()
            ->map(fn (Mes $mes) => ['mes' => $mes, 'pagado' => $mesesPagados->contains($mes->id_mes)]);
    }

    /**
     * Igual que estadoMensualCuotas() pero devuelve solo los meses que
     * todavía debe (los no pagados).
     */
    public function mesesAdeudados(?int $hastaMes = null): \Illuminate\Support\Collection
    {
        return $this->estadoMensualCuotas($hastaMes)
            ->reject(fn ($item) => $item['pagado'])
            ->pluck('mes');
    }
}
