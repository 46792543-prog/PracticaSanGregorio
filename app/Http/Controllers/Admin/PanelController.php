<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Acta;
use App\Models\Carrera;
use App\Models\CuotaAlumno;
use App\Models\HistorialAlumno;
use App\Models\InscripcionCarrera;
use App\Models\InscripcionMesa;
use App\Models\MesaExamen;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function index(): View
    {
        $alumnosActivos = InscripcionCarrera::whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->distinct('id_persona_alumno')->count('id_persona_alumno');
        $carreras = Carrera::count();
        $mesasHoy = MesaExamen::whereDate('fecha_examen', today())->count();
        $mesasEnCurso = MesaExamen::whereDate('fecha_inicio_inscripcion', '<=', today())
            ->whereDate('fecha_fin_inscripcion', '>=', today())
            ->count();
        $actasPorGenerar = MesaExamen::whereHas('estadoMesa', fn ($q) => $q->where('nombre_estado', 'Finalizada'))
            ->whereDoesntHave('acta')->count();

        $mesasDeHoy = MesaExamen::with('materia.nombreMateria')->whereDate('fecha_examen', today())->get();
        if ($mesasDeHoy->isEmpty()) {
            $mesasDeHoy = MesaExamen::with('materia.nombreMateria')
                ->whereHas('estadoMesa', fn ($q) => $q->where('nombre_estado', 'Programada'))
                ->orderBy('fecha_examen')->take(5)->get();
        }

        $actividad = collect()
            ->merge(InscripcionMesa::with('personaAlumno', 'mesaExamen.materia.nombreMateria')->latest('fecha_inscripcion')->take(2)->get()->map(fn ($i) => [
                'titulo' => "{$i->personaAlumno->nombre} {$i->personaAlumno->apellido} inscripto/a a {$i->mesaExamen->materia->nombre}",
                'fecha' => $i->fecha_inscripcion,
                'icono' => '👤',
            ]))
            ->merge(CuotaAlumno::where('pagado', true)->with('personaAlumno')->latest('fecha_pago')->take(2)->get()->map(fn ($c) => [
                'titulo' => "Pago de cuota registrado — {$c->personaAlumno->nombre} {$c->personaAlumno->apellido}",
                'fecha' => $c->fecha_pago,
                'icono' => '💰',
            ]))
            ->merge(Acta::with('mesaExamen.materia.nombreMateria')->latest('fecha_generacion')->take(2)->get()->map(fn ($a) => [
                'titulo' => "Acta generada — {$a->mesaExamen->materia->nombre}",
                'fecha' => $a->fecha_generacion,
                'icono' => '📄',
            ]))
            ->merge(HistorialAlumno::whereHas('condicion', fn ($q) => $q->where('nombre_condicion', 'Aprobada'))->with('personaAlumno', 'materia.nombreMateria')->latest('fecha_ultima_modificacion')->take(2)->get()->map(fn ($h) => [
                'titulo' => "{$h->personaAlumno->nombre} {$h->personaAlumno->apellido} aprobó {$h->materia->nombre}",
                'fecha' => $h->fecha_ultima_modificacion,
                'icono' => '✅',
            ]))
            ->sortByDesc('fecha')
            ->take(6);

        return view('admin.panel.index', [
            'alumnosActivos' => $alumnosActivos,
            'carreras' => $carreras,
            'mesasHoy' => $mesasHoy,
            'mesasEnCurso' => $mesasEnCurso,
            'actasPorGenerar' => $actasPorGenerar,
            'mesasDeHoy' => $mesasDeHoy,
            'actividad' => $actividad,
        ]);
    }
}
