<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CuotaController extends Controller
{
    public function index(): View
    {
        $alumno = Auth::user();

        $cuotas = $alumno->cuotas()->orderBy('fecha_vencimiento')->get();

        $anioLectivo = $cuotas->first()?->anioLectivo?->anio ?? now()->year;
        $proximaCuota = $cuotas->where('estado', 'pendiente')->sortBy('fecha_vencimiento')->first();
        $tieneVencidas = $cuotas->contains('estado', 'vencido');
        $pagadas = $cuotas->where('estado', 'pagado');
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
