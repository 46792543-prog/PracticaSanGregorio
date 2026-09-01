<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CuotaController extends Controller
{
    public function index(): View
    {
        $alumno = Auth::user()->persona;

        $cuotas = $alumno->cuotas()->with(['anioLectivo', 'mes'])
            ->orderBy('id_anio_lectivo')
            ->orderBy('id_mes')
            ->get();

        $anioLectivo = $cuotas->first()?->anioLectivo?->anio ?? now()->year;
        $pendientes = $cuotas->where('pagado', false);
        $pagadas = $cuotas->where('pagado', true);
        $proximaCuota = $pendientes->sortBy('fecha_vencimiento')->first();
        $tieneVencidas = $pendientes->contains(fn ($c) => $c->vencida);
        $totalPagado = $pagadas->sum('monto');

        return view('cuotas.index', [
            'cuotas' => $cuotas,
            'anioLectivo' => $anioLectivo,
            'proximaCuota' => $proximaCuota,
            'tieneVencidas' => $tieneVencidas,
            'totalPagado' => $totalPagado,
            'cantidadPagadas' => $pagadas->count(),
        ]);
    }
}
