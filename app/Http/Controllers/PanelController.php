<?php

namespace App\Http\Controllers;

use App\Models\MesaExamen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function index(Request $request): View
    {
        $alumno = Auth::user()->persona;
        $inscripcionCarrera = $alumno->inscripcionesCarrera()->with('carrera', 'anioCursada')->latest('id_inscripcion_carrera')->first();

        $totalMaterias = $inscripcionCarrera
            ? $inscripcionCarrera->carrera->materias()->count()
            : 0;
        $materiasAprobadas = $alumno->historialAlumno()->whereHas('condicion', fn ($q) => $q->where('nombre_condicion', 'Aprobada'))->count();
        $porcentaje = $totalMaterias > 0 ? (int) round($materiasAprobadas / $totalMaterias * 100) : 0;

        $mesasDisponibles = MesaExamen::whereHas('estadoMesa', fn ($q) => $q->where('nombre_estado', 'Programada'))->count();
        $inscripcionesActivas = $alumno->inscripcionesMesa()->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'En proceso'))->count();

        $cuotasPendientes = $alumno->cuotas()->where('pagado', false)->get();
        $proximaCuota = $cuotasPendientes->sortBy('fecha_vencimiento')->first();
        $cuotaVencida = $cuotasPendientes->contains(fn ($c) => $c->vencida);

        $proximasMesas = $alumno->historialAlumno()
            ->whereHas('condicion', fn ($q) => $q->where('nombre_condicion', 'Regular'))
            ->with('materia.mesasExamen.estadoMesa', 'materia.mesasExamen.turnoExamen')
            ->get()
            ->pluck('materia')
            ->flatMap(fn ($materia) => $materia->mesasExamen
                ->filter(fn ($mesa) => $mesa->estadoMesa->nombre_estado === 'Programada')
                ->map(fn ($mesa) => [
                    'mesa' => $mesa,
                    'materia' => $materia,
                    'bloqueo' => $materia->correlativaFaltante($alumno),
                ]))
            ->sortBy(fn ($item) => $item['mesa']->fecha_examen)
            ->take(3);

        $actividad = collect()
            ->merge($alumno->cuotas()->where('pagado', true)->with('mes')->latest('fecha_pago')->take(1)->get()->map(fn ($c) => [
                'titulo' => 'Pago de cuota registrado',
                'detalle' => ($c->concepto ?: "Cuota {$c->mes->nombre_mes}") . ' · $' . number_format($c->monto, 0, ',', '.'),
                'fecha' => $c->fecha_pago,
            ]))
            ->merge($alumno->inscripcionesMesa()->with('mesaExamen.materia.nombreMateria', 'estadoInscripcion')->latest('fecha_inscripcion')->take(1)->get()->map(fn ($i) => [
                'titulo' => $i->estadoInscripcion->nombre_estado === 'En proceso' ? 'Inscripción en revisión' : 'Inscripción a mesa actualizada',
                'detalle' => $i->mesaExamen->materia->nombre,
                'fecha' => $i->fecha_inscripcion,
            ]))
            ->merge($alumno->historialAlumno()->whereHas('condicion', fn ($q) => $q->where('nombre_condicion', 'Aprobada'))->with('materia.nombreMateria')->latest('fecha_ultima_modificacion')->take(1)->get()->map(fn ($h) => [
                'titulo' => 'Nota registrada',
                'detalle' => "{$h->materia->nombre} · Aprobada ({$h->nota_cursada})",
                'fecha' => $h->fecha_ultima_modificacion,
            ]))
            ->sortByDesc('fecha')
            ->take(4);

        return view('panel.index', [
            'alumno' => $alumno,
            'inscripcionCarrera' => $inscripcionCarrera,
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
}
