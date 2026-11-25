<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    /** A partir de esta cantidad de meses adeudados (no hace falta que sean
     * consecutivos) el alumno queda bloqueado para inscribirse a cursar o a
     * rendir — ver Persona::superaLimiteDeuda(). */
    public const MESES_MAXIMOS_DEUDA_PARA_INSCRIBIRSE = 3;

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

    public function matriculas()
    {
        return $this->hasMany(MatriculaAlumno::class, 'id_persona_alumno', 'id_persona');
    }

    /**
     * True si para ese año lectivo no hay matrícula generada (no se bloquea,
     * nunca se configuró) o si la que hay ya está pagada. False solo cuando
     * existe una matrícula generada y sigue impaga — ahí es cuando se
     * bloquea el cobro de cuotas de ese año (ver CuotaController::cobrar()).
     */
    public function matriculaPagada(int $idAnioLectivo): bool
    {
        $matricula = $this->matriculas()->where('id_anio_lectivo', $idAnioLectivo)->first();

        return ! $matricula || $matricula->pagado;
    }

    /**
     * Para inscribirse a cursar o a rendir: la matrícula de ese año tiene
     * que estar pagada. Si se configuró matrícula para ese año y al alumno
     * todavía no se le generó, también cuenta como impaga. Solo si nunca se
     * configuró matrícula para ese año no se bloquea nada.
     */
    public function matriculaAlDia(int $idAnioLectivo): bool
    {
        $matricula = $this->matriculas()->where('id_anio_lectivo', $idAnioLectivo)->first();

        if ($matricula) {
            return (bool) $matricula->pagado;
        }

        return ! ConfiguracionMatricula::where('id_anio_lectivo', $idAnioLectivo)->exists();
    }

    /**
     * True si la cuota de ese mes (y año lectivo) está generada y pagada.
     */
    public function cuotaDelMesPagada(int $idAnioLectivo, int $mes): bool
    {
        return $this->cuotas()
            ->where('id_anio_lectivo', $idAnioLectivo)
            ->where('id_mes', $mes)
            ->where('pagado', true)
            ->exists();
    }

    /**
     * La inscripción a la carrera que está activa (la más reciente), sin
     * importar de qué año lectivo sea: el alumno con cursada completa puede
     * tenerla de un año anterior.
     */
    public function inscripcionCarreraActiva(): ?InscripcionCarrera
    {
        return $this->inscripcionesCarrera()
            ->with('condicion')
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->latest('id_inscripcion_carrera')
            ->first();
    }

    public function condicionInscripcion(): ?string
    {
        return $this->inscripcionCarreraActiva()?->condicion?->nombre_condicion;
    }

    public function esRecursante(): bool
    {
        return $this->condicionInscripcion() === CondicionAlumno::RECURSANTE;
    }

    public function tieneCursadaCompleta(): bool
    {
        return $this->condicionInscripcion() === CondicionAlumno::CURSADA_COMPLETA;
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

    public function fichaRespuestas()
    {
        return $this->hasMany(FichaRespuesta::class, 'id_persona_alumno', 'id_persona');
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

        $hastaMes ??= now()->month;

        // Cursada completa: no paga cuotas mensuales, solo las que se le
        // generan a mano (la del mes de cada mesa que rinde). Su deuda son
        // esas cuotas generadas e impagas, no todos los meses del año.
        if ($this->tieneCursadaCompleta()) {
            return $this->cuotas()
                ->with('mes')
                ->where('id_anio_lectivo', $anioLectivoActivo->id_anio_lectivo)
                ->where('id_mes', '<=', min($hastaMes, 12))
                ->orderBy('id_mes')
                ->get()
                ->map(fn (CuotaAlumno $cuota) => ['mes' => $cuota->mes, 'pagado' => (bool) $cuota->pagado]);
        }

        $inscripcion = $this->inscripcionesCarrera()
            ->where('id_anio_lectivo', $anioLectivoActivo->id_anio_lectivo)
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->first();

        if (! $inscripcion) {
            return collect();
        }

        // Período de cobro configurado por el director para ese año (por
        // defecto marzo a diciembre). Todos deben desde mes_desde, sin
        // importar el día en que se los cargó en el sistema.
        $configuracion = ConfiguracionCuota::where('id_anio_lectivo', $anioLectivoActivo->id_anio_lectivo)->first();
        $inicioCiclo = $configuracion?->mes_desde ?? 3;
        $finCiclo = $configuracion?->mes_hasta ?? 12;

        if ($hastaMes < $inicioCiclo) {
            return collect();
        }

        $mesesPagados = $this->cuotas()
            ->where('id_anio_lectivo', $anioLectivoActivo->id_anio_lectivo)
            ->where('pagado', true)
            ->pluck('id_mes');

        return Mes::whereBetween('id_mes', [$inicioCiclo, min($hastaMes, $finCiclo)])
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

    /**
     * True si el alumno acumula 3 o más meses adeudados (no hace falta que
     * sean consecutivos) — a partir de ahí queda bloqueado para inscribirse
     * a cursar materias o a rendir exámenes.
     */
    public function superaLimiteDeuda(): bool
    {
        return $this->mesesAdeudados()->count() >= self::MESES_MAXIMOS_DEUDA_PARA_INSCRIBIRSE;
    }

    /**
     * Meses que debe del año lectivo activo y que ni siquiera tienen la cuota
     * generada (no se pueden cobrar hasta generarla).
     */
    public function mesesSinCuotaGenerada(): \Illuminate\Support\Collection
    {
        $anioLectivoActivo = AnioLectivo::whereHas('estadoAnio', fn ($q) => $q->where('nombre_estado', 'Activo'))->first();

        if (! $anioLectivoActivo) {
            return collect();
        }

        $mesesConCuota = $this->cuotas()
            ->where('id_anio_lectivo', $anioLectivoActivo->id_anio_lectivo)
            ->pluck('id_mes');

        return $this->mesesAdeudados()
            ->reject(fn (Mes $mes) => $mesesConCuota->contains($mes->id_mes))
            ->values()
            ->each(fn (Mes $mes) => $mes->setAttribute('id_anio_lectivo', $anioLectivoActivo->id_anio_lectivo));
    }
}
