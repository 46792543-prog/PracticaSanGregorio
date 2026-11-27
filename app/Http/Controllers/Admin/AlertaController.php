<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComunicacionAlumno;
use App\Support\CentralAlertas;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class AlertaController extends Controller
{
    public function index(Request $request): View
    {
        // Antes de armar las alertas: cuotas que le falten a alumnos inscriptos después de la
        // configuración (nunca duplica) y mora al día, así sus cuotas vencidas aparecen acá.
        \App\Models\ConfiguracionCuota::all()->each(fn ($c) => \App\Support\GeneradorCuotas::generar($c));
        \App\Support\RecalculadorCuotas::aplicarATodos();

        $tipo = $request->query('tipo');

        $alertas = CentralAlertas::activas(paraDirector: false)
            ->when($tipo, fn ($c) => $c->where('tipo', $tipo))
            // Buscar por nombre, apellido (en el título de la alerta) o DNI.
            ->when($busqueda = trim((string) $request->query('q')), fn ($c) => $c->filter(fn ($al) => mb_stripos($al['titulo'], $busqueda) !== false
                || (ctype_digit($busqueda) && str_contains($al['dni'] ?? '', $busqueda))))
            ->values();

        $porPagina = 20;
        $pagina = (int) $request->query('page', 1);
        $paginado = new LengthAwarePaginator(
            $alertas->forPage($pagina, $porPagina),
            $alertas->count(),
            $porPagina,
            $pagina,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.alertas.index', [
            'alertas' => $paginado,
            'tipo' => $tipo,
            'busqueda' => $busqueda,
            // Historial de correos enviados a mano (con su resultado).
            'historial' => ComunicacionAlumno::with('alumno', 'enviadoPor')->latest('id_comunicacion')
                ->paginate(10, ['*'], 'historial')->withQueryString(),
            'tipos' => [
                'cuota_vencida' => 'Cuotas vencidas',
                'baja_alumno' => 'Bajas de alumnos',
                'baja_profesor' => 'Bajas de profesores',
            ],
        ]);
    }
}
