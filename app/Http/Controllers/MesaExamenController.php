<?php

namespace App\Http\Controllers;

use App\Models\CondicionAlumno;
use App\Models\ConfiguracionCuota;
use App\Models\EstadoInscripcion;
use App\Models\InscripcionMesa;
use App\Models\Materia;
use App\Models\MesaExamen;
use App\Models\Mes;
use App\Models\Persona;
use App\Models\TurnoExamen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MesaExamenController extends Controller
{
    public function index(Request $request): View
    {
        $alumno = Auth::user()->persona;
        $turnos = TurnoExamen::orderBy('id_turno')->get();
        $turnoId = (int) $request->query('turno', $turnos->firstWhere('nombre_turno', 'Turno Julio')?->id_turno ?? $turnos->first()?->id_turno);

        // Mismo criterio que "Mis cuotas": tiene que estar al día hasta el
        // mes actual (no alcanza con no deber nada de antes) — sin importar
        // si esas cuotas llegaron a generarse.
        // Todo se mira en la carrera elegida (selector "Mi carrera").
        $idCarrera = $alumno->carreraElegida()?->id_carrera;
        $mesesAdeudados = $alumno->mesesAdeudados(null, $idCarrera);
        $cuotasEstado = $mesesAdeudados->isEmpty() ? 'al_dia' : 'debe';
        $cursadaCompleta = $alumno->tieneCursadaCompleta($idCarrera);

        $mesas = MesaExamen::with('materia.nombreMateria', 'estadoMesa', 'turnoExamen', 'llamadoExamen', 'anioLectivo')
            ->where('id_turno', $turnoId)
            ->whereHas('materia', fn ($q) => $q->where('id_carrera', $idCarrera))
            ->whereHas('estadoMesa', fn ($q) => $q->where('nombre_estado', 'Programada'))
            // Las extraordinarias son solo para quienes terminaron de cursar.
            ->when(! $cursadaCompleta, fn ($q) => $q->where('extraordinaria', false))
            ->orderBy('fecha_examen')
            ->get()
            ->map(function (MesaExamen $mesa) use ($alumno) {
                $mesa->ya_aprobada = $this->yaAprobada($mesa->materia, $alumno);
                $mesa->bloqueo = $this->materiaSinCursar($mesa->materia, $alumno);
                $mesa->correlativas_faltantes = $mesa->bloqueo ? collect() : $mesa->materia->correlativasFaltantes($alumno);
                $mesa->bloqueo_pago = $this->bloqueoDePago($mesa, $alumno);
                $mesa->ya_inscripto = InscripcionMesa::where('id_mesa', $mesa->id_mesa)
                    ->where('id_persona_alumno', $alumno->id_persona)
                    ->exists();

                return $mesa;
            });

        return view('mesas-examen.index', [
            'mesas' => $mesas,
            'turno' => $turnoId,
            'turnos' => $turnos,
            'cuotasEstado' => $cuotasEstado,
            'mesesAdeudados' => $mesesAdeudados,
        ]);
    }

    public function inscribir(Request $request, MesaExamen $mesa): RedirectResponse
    {
        $alumno = Auth::user()->persona;

        if ($mesa->extraordinaria && ! $alumno->tieneCursadaCompleta($mesa->materia->id_carrera)) {
            return back()->withErrors(['mesa' => 'Esta mesa es extraordinaria: solo pueden anotarse los alumnos que ya terminaron de cursar la carrera.']);
        }

        if ($this->yaAprobada($mesa->materia, $alumno)) {
            return back()->withErrors(['mesa' => 'Ya tenés esta materia aprobada: no te podés anotar de nuevo.']);
        }

        if ($this->materiaSinCursar($mesa->materia, $alumno)) {
            return back()->withErrors(['mesa' => 'No estás en condiciones de rendir esta materia: todavía no la cursaste o no estás regularizado.']);
        }

        $correlativasFaltantes = $mesa->materia->correlativasFaltantes($alumno);

        if ($correlativasFaltantes->isNotEmpty()) {
            return back()->withErrors(['mesa' => 'Te falta regularizar o aprobar: ' . $correlativasFaltantes->pluck('nombre')->join(', ') . '.']);
        }

        if ($alumno->superaLimiteDeuda($mesa->materia->id_carrera)) {
            return back()->withErrors(['mesa' => 'Tenés ' . Persona::MESES_MAXIMOS_DEUDA_PARA_INSCRIBIRSE . ' cuotas o más sin pagar. Consultá con secretaría.']);
        }

        if ($motivo = $this->bloqueoDePago($mesa, $alumno)) {
            return back()->withErrors(['mesa' => $motivo . ' Consultá con secretaría.']);
        }

        $yaInscripto = InscripcionMesa::where('id_mesa', $mesa->id_mesa)
            ->where('id_persona_alumno', $alumno->id_persona)
            ->exists();

        if (! $yaInscripto && $mesa->cupo_maximo !== null) {
            $inscriptosActivos = $mesa->inscripciones()
                ->whereHas('estadoInscripcion', fn ($q) => $q->whereIn('nombre_estado', ['Aceptado', 'En proceso']))
                ->count();

            if ($inscriptosActivos >= $mesa->cupo_maximo) {
                return back()->withErrors(['mesa' => 'La mesa alcanzó su cupo máximo de inscriptos.']);
            }
        }

        $estadoEnProceso = EstadoInscripcion::where('nombre_estado', 'En proceso')->firstOrFail();

        InscripcionMesa::firstOrCreate(
            ['id_mesa' => $mesa->id_mesa, 'id_persona_alumno' => $alumno->id_persona],
            ['fecha_inscripcion' => now(), 'id_estado_inscripcion' => $estadoEnProceso->id_estado_inscripcion]
        );

        return redirect()->route('inscripciones.index')
            ->with('status', 'Tu inscripción a ' . $mesa->materia->nombre . ' quedó registrada y pendiente de aprobación.');
    }

    /**
     * Para rendir hay que tener paga la matrícula del año de la mesa y la
     * cuota del mes ANTERIOR al del examen (no hace falta esperar a que se
     * genere/venza la del propio mes de la mesa) — vale para todos: regular,
     * recursante y cursada completa. Devuelve el motivo del bloqueo, o null
     * si está ok.
     */
    private function bloqueoDePago(MesaExamen $mesa, Persona $alumno): ?string
    {
        $idAnio = $mesa->id_anio_lectivo;
        $idCarrera = $mesa->materia->id_carrera; // se mira solo la carrera de la materia

        if ($alumno->planCaducado($idCarrera)) {
            return 'Tu plan de estudios caducó: secretaría te tiene que pasar al plan vigente.';
        }

        if (! $alumno->matriculaAlDia($idAnio, $idCarrera)) {
            return 'Tenés que pagar la matrícula ' . ($mesa->anioLectivo?->anio ?? '') . ' para poder rendir.';
        }

        $mesRequerido = $mesa->fecha_examen->month - 1;

        // Si el mes anterior queda antes de que arranque el período de cobro
        // (por defecto marzo), todavía no hay ninguna cuota que exigir.
        $mesDesde = ConfiguracionCuota::de($idAnio, $idCarrera)?->mes_desde ?? 3;

        if ($mesRequerido >= $mesDesde && ! $alumno->cuotaDelMesPagada($idAnio, $mesRequerido, $idCarrera)) {
            $nombreMes = Mes::find($mesRequerido)?->nombre_mes ?? 'ese mes';

            return "Tenés que pagar la cuota de {$nombreMes} (el mes anterior a la mesa) para poder rendir.";
        }

        return null;
    }

    /**
     * True si el alumno ya tiene esta materia aprobada (Promocionada, Final
     * Aprobado o Equivalencia): no tiene sentido que se anote de nuevo a su
     * mesa.
     */
    private function yaAprobada(Materia $materia, Persona $alumno): bool
    {
        return CondicionAlumno::esAprobada($materia->historialDe($alumno)?->condicion?->nombre_condicion);
    }

    /**
     * Devuelve la propia materia si el alumno todavía no la cursó/regularizó
     * (o su regularidad venció), o null si está en condiciones de rendirla.
     */
    private function materiaSinCursar(Materia $materia, Persona $alumno): ?Materia
    {
        $historial = $materia->historialDe($alumno);
        $condicionNombre = $historial?->condicion?->nombre_condicion;
        $puedeRendir = CondicionAlumno::esAprobada($condicionNombre)
            || (CondicionAlumno::esRegular($condicionNombre) && ! ($historial?->regularidad_vencida ?? false));

        return $puedeRendir ? null : $materia;
    }
}
