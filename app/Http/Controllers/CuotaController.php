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
        $totalPagado = $pagadas->sum('monto');

        // Los meses adeudados se calculan por calendario (desde que se
        // inscribió hasta el mes actual), no solo mirando qué cuotas llegó
        // a generar secretaría — así no figura "Al día" solo porque todavía
        // no le cargaron nada.
        $mesesAdeudados = $alumno->mesesAdeudados();
        $cuotasEstado = $mesesAdeudados->isEmpty() ? 'al_dia' : 'debe';

        return view('cuotas.index', [
            'cuotas' => $cuotas,
            'anioLectivo' => $anioLectivo,
            'proximaCuota' => $proximaCuota,
            'cuotasEstado' => $cuotasEstado,
            'mesesAdeudados' => $mesesAdeudados,
            'totalPagado' => $totalPagado,
            'cantidadPagadas' => $pagadas->count(),
        ]);
    }
}
