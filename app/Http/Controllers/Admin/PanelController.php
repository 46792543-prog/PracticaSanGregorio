<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Acta;
use App\Models\Carrera;
use App\Models\Cuota;
use App\Models\HistorialMateria;
use App\Models\InscripcionCarrera;
use App\Models\InscripcionMesa;
use App\Models\MesaExamen;
use App\Models\User;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function index(): View
    {
        $alumnosActivos = InscripcionCarrera::where('estado', 'activo')->distinct('user_id')->count('user_id');
        $carreras = Carrera::count();
        $mesasHoy = MesaExamen::whereDate('fecha_examen', today())->count();
        $mesasEnCurso = MesaExamen::whereDate('fecha_inicio_inscripcion', '<=', today())
            ->whereDate('fecha_fin_inscripcion', '>=', today())
            ->count();
        $actasPorGenerar = MesaExamen::where('estado', 'finalizada')->whereDoesntHave('acta')->count();

        $mesasDeHoy = MesaExamen::with('materia')->whereDate('fecha_examen', today())->get();
        if ($mesasDeHoy->isEmpty()) {
            $mesasDeHoy = MesaExamen::with('materia')->where('estado', 'programada')->orderBy('fecha_examen')->take(5)->get();
        }

        $actividad = collect()
            ->merge(InscripcionMesa::with('user', 'mesaExamen.materia')->latest('fecha_inscripcion')->take(2)->get()->map(fn ($i) => [
                'titulo' => "{$i->user->nombre} {$i->user->apellido} inscripto/a a {$i->mesaExamen->materia->nombre}",
                'fecha' => $i->fecha_inscripcion,
                'icono' => '👤',
            ]))
            ->merge(Cuota::where('estado', 'pagado')->with('user')->latest('fecha_pago')->take(2)->get()->map(fn ($c) => [
                'titulo' => "Pago de cuota registrado — {$c->user->nombre} {$c->user->apellido}",
                'fecha' => $c->fecha_pago,
                'icono' => '💰',
            ]))
            ->merge(Acta::with('mesaExamen.materia')->where('estado', 'generada')->latest('fecha_generacion')->take(2)->get()->map(fn ($a) => [
                'titulo' => "Acta generada — {$a->mesaExamen->materia->nombre}",
                'fecha' => $a->fecha_generacion,
                'icono' => '📄',
            ]))
            ->merge(HistorialMateria::where('condicion', 'aprobada')->with('user', 'materia')->latest('fecha_ultima_modificacion')->take(2)->get()->map(fn ($h) => [
                'titulo' => "{$h->user->nombre} {$h->user->apellido} aprobó {$h->materia->nombre}",
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
