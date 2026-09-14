<?php

namespace App\Http\Controllers;

use App\Models\EstadoInscripcion;
use App\Models\InscripcionMesa;
use App\Models\MesaExamen;
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
        $mesesAdeudados = $alumno->mesesAdeudados();
        $cuotasEstado = $mesesAdeudados->isEmpty() ? 'al_dia' : 'debe';

        $mesas = MesaExamen::with('materia.nombreMateria', 'estadoMesa', 'turnoExamen', 'llamadoExamen')
            ->where('id_turno', $turnoId)
            ->whereHas('estadoMesa', fn ($q) => $q->where('nombre_estado', 'Programada'))
            ->orderBy('fecha_examen')
            ->get()
            ->map(function (MesaExamen $mesa) use ($alumno) {
                $mesa->bloqueo = $mesa->materia->correlativaFaltante($alumno);
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

        if ($mesa->materia->correlativaFaltante($alumno)) {
            return back()->withErrors(['mesa' => 'No cumplís las correlativas necesarias para esta mesa.']);
        }

        if ($alumno->mesesAdeudados()->isNotEmpty()) {
            return back()->withErrors(['mesa' => 'Tu situación de pagos no está al día. Consultá con secretaría.']);
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
}
