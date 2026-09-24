<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Support\CentralAlertas;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class AlertaController extends Controller
{
    public function index(Request $request): View
    {
        $tipo = $request->query('tipo');

        $alertas = CentralAlertas::activas()
            ->when($tipo, fn ($c) => $c->where('tipo', $tipo))
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

        return view('director.alertas.index', [
            'alertas' => $paginado,
            'tipo' => $tipo,
            'tipos' => [
                'cuota_vencida' => 'Cuotas vencidas',
                'baja_alumno' => 'Bajas de alumnos',
                'baja_profesor' => 'Bajas de profesores',
            ],
        ]);
    }
}
