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
        $alumno = Auth::user();
        $inscripcionCarrera = $alumno->inscripcionesCarrera()->with('carrera')->latest()->first();

        $totalMaterias = $inscripcionCarrera
            ? $inscripcionCarrera->carrera->materias()->count()
            : 0;
        $materiasAprobadas = $alumno->historialMaterias()->where('condicion', 'aprobada')->count();
        $porcentaje = $totalMaterias > 0 ? (int) round($materiasAprobadas / $totalMaterias * 100) : 0;

        $mesasDisponibles = MesaExamen::where('estado', 'programada')->count();
        $inscripcionesActivas = $alumno->inscripcionesMesa()->where('estado', 'en_proceso')->count();

        $proximaCuota = $alumno->cuotas()->where('estado', 'pendiente')->orderBy('fecha_vencimiento')->first();
        $cuotaVencida = $alumno->cuotas()->where('estado', 'vencido')->exists();

        $proximasMesas = $alumno->historialMaterias()
            ->whereIn('condicion', ['regular'])
            ->with('materia.mesasExamen')
            ->get()
            ->pluck('materia')
            ->flatMap(fn ($materia) => $materia->mesasExamen->where('estado', 'programada')->map(fn ($mesa) => [
                'mesa' => $mesa,
                'materia' => $materia,
                'bloqueo' => $materia->correlativaFaltante($alumno),
            ]))
            ->sortBy(fn ($item) => $item['mesa']->fecha_examen)
            ->take(3);

        $actividad = collect()
            ->merge($alumno->cuotas()->where('estado', 'pagado')->latest('fecha_pago')->take(1)->get()->map(fn ($c) => [
                'titulo' => 'Pago de cuota registrado',
                'detalle' => "{$c->concepto} · $" . number_format($c->monto, 0, ',', '.'),
                'fecha' => $c->fecha_pago,
            ]))
            ->merge($alumno->inscripcionesMesa()->with('mesaExamen.materia')->latest('fecha_inscripcion')->take(1)->get()->map(fn ($i) => [
                'titulo' => $i->estado === 'en_proceso' ? 'Inscripción en revisión' : 'Inscripción a mesa actualizada',
                'detalle' => $i->mesaExamen->materia->nombre,
                'fecha' => $i->fecha_inscripcion,
            ]))
            ->merge($alumno->historialMaterias()->where('condicion', 'aprobada')->with('materia')->latest('fecha_ultima_modificacion')->take(1)->get()->map(fn ($h) => [
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
