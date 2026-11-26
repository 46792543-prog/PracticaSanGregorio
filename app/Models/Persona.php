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
     * Un alumno puede cursar dos carreras: cuotas, matrícula, condición y
     * deuda van por carrera. Si no se indica cuál, se usa la de su
     * inscripción activa más reciente (la única, si cursa una sola).
     */
    private function carreraOPorDefecto(?int $idCarrera): ?int
    {
        return $idCarrera ?? $this->inscripcionCarreraActiva()?->id_carrera;
    }

    // Cuotas de una carrera.
    public function cuotasDeCarrera(?int $idCarrera = null)
    {
        $idCarrera = $this->carreraOPorDefecto($idCarrera);

        return $this->cuotas()->when($idCarrera, fn ($q) => $q->where('id_carrera', $idCarrera));
    }

    // Matrícula de una carrera en un año lectivo.
    public function matriculaDe(int $idAnioLectivo, ?int $idCarrera = null): ?MatriculaAlumno
    {
        $idCarrera = $this->carreraOPorDefecto($idCarrera);

        return $this->matriculas()->where('id_anio_lectivo', $idAnioLectivo)
            ->when($idCarrera, fn ($q) => $q->where('id_carrera', $idCarrera))
            ->first();
    }

    /**
     * True si para ese año lectivo no hay matrícula generada (no se bloquea,
     * nunca se configuró) o si la que hay ya está pagada. False solo cuando
     * existe una matrícula generada y sigue impaga — ahí es cuando se
     * bloquea el cobro de cuotas de ese año (ver CuotaController::cobrar()).
     */
    public function matriculaPagada(int $idAnioLectivo, ?int $idCarrera = null): bool
    {
        $matricula = $this->matriculaDe($idAnioLectivo, $idCarrera);

        return ! $matricula || $matricula->pagado;
    }

    /**
     * Para inscribirse a cursar o a rendir: la matrícula de ese año tiene
     * que estar pagada. Si se configuró matrícula para ese año y al alumno
     * todavía no se le generó, también cuenta como impaga. Solo si nunca se
     * configuró matrícula para ese año no se bloquea nada.
     */
    public function matriculaAlDia(int $idAnioLectivo, ?int $idCarrera = null): bool
    {
        $idCarrera = $this->carreraOPorDefecto($idCarrera);
        $matricula = $this->matriculaDe($idAnioLectivo, $idCarrera);

        if ($matricula) {
            return (bool) $matricula->pagado;
        }

        return ! ConfiguracionMatricula::de($idAnioLectivo, $idCarrera);
    }

    /**
     * True si la cuota de ese mes (y año lectivo) está generada y pagada.
     */
    public function cuotaDelMesPagada(int $idAnioLectivo, int $mes, ?int $idCarrera = null): bool
    {
        return $this->cuotasDeCarrera($idCarrera)
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
    public function inscripcionCarreraActiva(?int $idCarrera = null): ?InscripcionCarrera
    {
        return $this->inscripcionesCarrera()
            ->with('condicion')
            ->when($idCarrera, fn ($q) => $q->where('id_carrera', $idCarrera))
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->latest('id_inscripcion_carrera')
            ->first();
    }

    // Carrera que el alumno eligió ver en su portal (selector "Mi carrera",
    // guardado en la sesión). Si no eligió o ya no está activa, la más reciente.
    public function carreraElegida(): ?InscripcionCarrera
    {
        $elegida = session('carrera_alumno');

        return ($elegida ? $this->inscripcionCarreraActiva((int) $elegida) : null) ?? $this->inscripcionCarreraActiva();
    }

    // Cuántas materias está RECURSANDO en una carrera y año (para el precio del recursante).
    public function materiasCursando(int $idAnioLectivo, int $idCarrera): int
    {
        return $this->historialAlumno()
            ->where('id_anio_lectivo', $idAnioLectivo)
            ->whereHas('materia', fn ($q) => $q->where('id_carrera', $idCarrera))
            ->whereHas('condicion', fn ($q) => $q->where('nombre_condicion', CondicionAlumno::RECURSANDO))
            ->count();
    }

    // Condición automática: Recursante si recursa al menos una materia de la
    // carrera; si deja de recursar, vuelve a Regular (Cursada completa no se toca).
    public function sincronizarCondicionRecursante(?int $idCarrera): void
    {
        $inscripcion = $idCarrera ? $this->inscripcionCarreraActiva($idCarrera) : null;
        $actual = $inscripcion?->condicion?->nombre_condicion;

        if (! $inscripcion || $actual === CondicionAlumno::CURSADA_COMPLETA) {
            return;
        }

        $recursa = $this->historialAlumno()
            ->whereHas('materia', fn ($q) => $q->where('id_carrera', $idCarrera))
            ->whereHas('condicion', fn ($q) => $q->where('nombre_condicion', CondicionAlumno::RECURSANDO))
            ->exists();
        $nueva = $recursa ? CondicionAlumno::RECURSANTE : CondicionAlumno::REGULAR;

        if ($nueva !== $actual) {
            $inscripcion->update(['id_condicion' => CondicionAlumno::where('nombre_condicion', $nueva)->value('id_condicion')]);
        }
    }

    // ¿El plan de estudios con el que cursa esa carrera ya caducó? (hay que pasarlo a otro plan)
    public function planCaducado(?int $idCarrera = null): bool
    {
        return $this->inscripcionCarreraActiva($idCarrera)?->plan?->estado() === 'Caducado';
    }

    // Condición (Regular / Recursante / Cursada completa) en una carrera.
    public function condicionInscripcion(?int $idCarrera = null): ?string
    {
        return $this->inscripcionCarreraActiva($idCarrera)?->condicion?->nombre_condicion;
    }

    public function esRecursante(?int $idCarrera = null): bool
    {
        return $this->condicionInscripcion($idCarrera) === CondicionAlumno::RECURSANTE;
    }

    public function tieneCursadaCompleta(?int $idCarrera = null): bool
    {
        return $this->condicionInscripcion($idCarrera) === CondicionAlumno::CURSADA_COMPLETA;
    }

    /**
     * Año que el alumno está cursando ahora: el año lectivo de su
     * inscripción, o el año calendario si es más nuevo (la inscripción
     * guarda el año en que se lo cargó en el sistema y no se actualiza al
     * promoverlo de año).
     */
    public function anioLectivoActualDeCursada(): ?int
    {
        $inscripcion = $this->inscripcionCarreraActiva()?->loadMissing('anioLectivo');

        return $inscripcion ? max(now()->year, (int) $inscripcion->anioLectivo?->anio) : null;
    }

    /**
     * Número del año de la carrera que cursa ahora (1 = Primer Año, 2 =
     * Segundo, ...), según el orden de los años de cursada.
     */
    public function ordenAnioDeCursada(): ?int
    {
        $idAnioCursada = $this->inscripcionCarreraActiva()?->id_anio_cursada;

        return $idAnioCursada ? AnioCursada::where('id_anio_cursada', '<=', $idAnioCursada)->count() : null;
    }

    /**
     * Año en que entró a la carrera. No alcanza con el año lectivo de la
     * inscripción: es el año en que lo cargaron en el sistema, y puede que
     * lo hayan inscripto directo en 2do o 3er año. Se toma el más antiguo
     * entre:
     *  - el año actual menos los años que ya cursó (cursa Segundo Año en
     *    2026 → entró en 2025),
     *  - el año lectivo más viejo de su historial académico,
     *  - el año lectivo de la inscripción.
     */
    public function anioIngresoCarrera(): ?int
    {
        $candidatos = collect();

        $primeraInscripcion = $this->inscripcionesCarrera()->with('anioLectivo')->orderBy('id_inscripcion_carrera')->first();
        $candidatos->push($primeraInscripcion?->anioLectivo?->anio);

        $anioActual = $this->anioLectivoActualDeCursada();
        $orden = $this->ordenAnioDeCursada();
        if ($anioActual && $orden) {
            $candidatos->push($anioActual - ($orden - 1));
        }

        $candidatos->push($this->historialAlumno()
            ->join('anio_lectivo', 'anio_lectivo.id_anio_lectivo', '=', 'historial_alumno.id_anio_lectivo')
            ->min('anio_lectivo.anio'));

        $anio = $candidatos->filter()->map(fn ($a) => (int) $a)->min();

        return $anio ?: null;
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
    public function estadoMensualCuotas(?int $hastaMes = null, ?int $idCarrera = null): \Illuminate\Support\Collection
    {
        $idCarrera = $this->carreraOPorDefecto($idCarrera);
        $anioLectivoActivo = AnioLectivo::activo();

        if (! $anioLectivoActivo) {
            return collect();
        }

        $hastaMes ??= now()->month;

        // Cursada completa: no paga cuotas mensuales, solo las que se le
        // generan a mano (la del mes de cada mesa que rinde). Su deuda son
        // esas cuotas generadas e impagas, no todos los meses del año.
        if ($this->tieneCursadaCompleta($idCarrera)) {
            return $this->cuotasDeCarrera($idCarrera)
                ->with('mes')
                ->where('id_anio_lectivo', $anioLectivoActivo->id_anio_lectivo)
                ->where('id_mes', '<=', min($hastaMes, 12))
                ->orderBy('id_mes')
                ->get()
                ->map(fn (CuotaAlumno $cuota) => ['mes' => $cuota->mes, 'pagado' => (bool) $cuota->pagado]);
        }

        $inscripcion = $this->inscripcionesCarrera()
            ->where('id_anio_lectivo', $anioLectivoActivo->id_anio_lectivo)
            ->when($idCarrera, fn ($q) => $q->where('id_carrera', $idCarrera))
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->first();

        if (! $inscripcion) {
            return collect();
        }

        // Período de cobro configurado por el director para ese año (por
        // defecto marzo a diciembre). Todos deben desde mes_desde, sin
        // importar el día en que se los cargó en el sistema.
        $configuracion = ConfiguracionCuota::de($anioLectivoActivo->id_anio_lectivo, $idCarrera);
        $inicioCiclo = $configuracion?->mes_desde ?? 3;
        $finCiclo = $configuracion?->mes_hasta ?? 12;

        if ($hastaMes < $inicioCiclo) {
            return collect();
        }

        $mesesPagados = $this->cuotasDeCarrera($idCarrera)
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
    public function mesesAdeudados(?int $hastaMes = null, ?int $idCarrera = null): \Illuminate\Support\Collection
    {
        return $this->estadoMensualCuotas($hastaMes, $idCarrera)
            ->reject(fn ($item) => $item['pagado'])
            ->pluck('mes');
    }

    /**
     * True si el alumno acumula 3 o más meses adeudados (no hace falta que
     * sean consecutivos) — a partir de ahí queda bloqueado para inscribirse
     * a cursar materias o a rendir exámenes.
     */
    // La deuda bloquea solo la carrera que se debe, no la otra.
    public function superaLimiteDeuda(?int $idCarrera = null): bool
    {
        return $this->mesesAdeudados(null, $idCarrera)->count() >= self::MESES_MAXIMOS_DEUDA_PARA_INSCRIBIRSE;
    }

    /**
     * Meses que debe del año lectivo activo y que ni siquiera tienen la cuota
     * generada (no se pueden cobrar hasta generarla).
     */
    public function mesesSinCuotaGenerada(?int $idCarrera = null): \Illuminate\Support\Collection
    {
        $anioLectivoActivo = AnioLectivo::activo();

        if (! $anioLectivoActivo) {
            return collect();
        }

        $mesesConCuota = $this->cuotasDeCarrera($idCarrera)
            ->where('id_anio_lectivo', $anioLectivoActivo->id_anio_lectivo)
            ->pluck('id_mes');

        return $this->mesesAdeudados(null, $idCarrera)
            ->reject(fn (Mes $mes) => $mesesConCuota->contains($mes->id_mes))
            ->values()
            ->each(fn (Mes $mes) => $mes->setAttribute('id_anio_lectivo', $anioLectivoActivo->id_anio_lectivo));
    }
}
