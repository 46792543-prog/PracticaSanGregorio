<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $tabla = $request->query('tabla');
        $accion = $request->query('accion');
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $eventos = Auditoria::with('usuario.persona')
            ->when($tabla, fn ($q) => $q->where('tabla', $tabla))
            ->when($accion, fn ($q) => $q->where('accion', $accion))
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->orderByDesc('id_auditoria')
            ->paginate(25)
            ->withQueryString();

        return view('director.auditoria.index', [
            'eventos' => $eventos,
            'tablas' => Auditoria::select('tabla')->distinct()->orderBy('tabla')->pluck('tabla'),
            'filtros' => ['tabla' => $tabla, 'accion' => $accion, 'desde' => $desde, 'hasta' => $hasta],
        ]);
    }
}
