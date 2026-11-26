<?php

namespace App\Http\Controllers;

use App\Models\CondicionAlumno;
use App\Models\MesaExamen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function index(Request $request): View
    {
        $alumno = Auth::user()->persona;
        $inscripcionCarrera = $alumno->carreraElegida()?->load('carrera', 'anioCursada');
        $idCarrera = $inscripcionCarrera?->id_carrera;
        $deLaCarrera = fn ($q) => $q->whereHas('materia', fn ($m) => $m->where('id_carrera', $idCarrera));

        $totalMaterias = $inscripcionCarrera
            ? $inscripcionCarrera->materiasDelPlan()->count()
            : 0;
        $materiasAprobadas = $alumno->historialAlumno()->tap($deLaCarrera)->whereHas('condicion', fn ($q) => $q->whereIn('nombre_condicion', CondicionAlumno::APROBADA))->count();
        $porcentaje = $totalMaterias > 0 ? (int) round($materiasAprobadas / $totalMaterias * 100) : 0;

        $mesasDisponibles = MesaExamen::whereHas('estadoMesa', fn ($q) => $q->where('nombre_estado', 'Programada'))->count();
        $inscripcionesActivas = $alumno->inscripcionesMesa()->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'En proceso'))->count();

        $cuotasPendientes = $alumno->cuotasDeCarrera($idCarrera)->where('pagado', false)->get();
        $proximaCuota = $cuotasPendientes->sortBy('fecha_vencimiento')->first();
        $cuotaVencida = $cuotasPendientes->contains(fn ($c) => $c->vencida);

        $proximasMesas = $alumno->historialAlumno()->tap($deLaCarrera)
            ->whereHas('condicion', fn ($q) => $q->whereIn('nombre_condicion', \App\Models\CondicionAlumno::REGULARIZADA))
            ->with('materia.mesasExamen.estadoMesa', 'materia.mesasExamen.turnoExamen')
            ->get()
            ->pluck('materia')
            ->flatMap(fn ($materia) => $materia->mesasExamen
                ->filter(fn ($mesa) => $mesa->estadoMesa->nombre_estado === 'Programada')
                ->map(fn ($mesa) => [
                    'mesa' => $mesa,
                    'materia' => $materia,
                    'bloqueo' => $materia->correlativasFaltantes($alumno)->isNotEmpty(),
                ]))
            ->sortBy(fn ($item) => $item['mesa']->fecha_examen)
            ->take(3);

        $actividad = collect()
            ->merge($alumno->cuotasDeCarrera($idCarrera)->where('pagado', true)->with('mes')->latest('fecha_pago')->take(1)->get()->map(fn ($c) => [
                'titulo' => 'Pago de cuota registrado',
                'detalle' => $c->etiqueta . ' · $' . number_format($c->monto, 0, ',', '.'),
                'fecha' => $c->fecha_pago,
            ]))
            ->merge($alumno->inscripcionesMesa()->with('mesaExamen.materia.nombreMateria', 'estadoInscripcion')->latest('fecha_inscripcion')->take(1)->get()->map(fn ($i) => [
                'titulo' => $i->estadoInscripcion->nombre_estado === 'En proceso' ? 'Inscripción en revisión' : 'Inscripción a mesa actualizada',
                'detalle' => $i->mesaExamen->materia->nombre,
                'fecha' => $i->fecha_inscripcion,
            ]))
            ->merge($alumno->historialAlumno()->whereHas('condicion', fn ($q) => $q->whereIn('nombre_condicion', CondicionAlumno::APROBADA))->with('materia.nombreMateria', 'condicion')->latest('fecha_ultima_modificacion')->take(1)->get()->map(fn ($h) => [
                'titulo' => 'Nota registrada',
                'detalle' => "{$h->materia->nombre} · {$h->condicion->nombre_condicion} ({$h->nota_cursada})",
                'fecha' => $h->fecha_ultima_modificacion,
            ]))
            ->sortByDesc('fecha')
            ->take(4);

        return view('panel.index', [
            'alumno' => $alumno,
            'inscripcionCarrera' => $inscripcionCarrera,
            // Para elegir carrera desde el panel (solo si cursa más de una).
            'carrerasActivas' => $alumno->inscripcionesCarrera()->with('carrera')
                ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))->get(),
            'totalMaterias' => $totalMaterias,
            'materiasAprobadas' => $materiasAprobadas,
            'porcentaje' => $porcentaje,
            'mesasDisponibles' => $mesasDisponibles,
            'inscripcionesActivas' => $inscripcionesActivas,
            'proximaCuota' => $proximaCuota,
            'cuotaVencida' => $cuotaVencida,
            'proximasMesas' => $proximasMesas,
            'actividad' => $actividad,
        ]);
    }

    // Selector "Mi carrera" (si cursa dos): guarda la elegida y vuelve a la misma pantalla.
    public function elegirCarrera(Request $request): RedirectResponse
    {
        session(['carrera_alumno' => $request->integer('carrera')]);

        return back();
    }
}
