<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\InscripcionCarrera;
use App\Models\MovimientoCaja;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function index(): View
    {
        $alumnosActivos = InscripcionCarrera::where('estado', 'activo')->distinct('user_id')->count('user_id');
        $alumnosActivosMesPasado = InscripcionCarrera::where('estado', 'activo')
            ->where('created_at', '<', now()->startOfMonth())
            ->distinct('user_id')->count('user_id');

        $ingresosMes = MovimientoCaja::where('tipo', 'ingreso')->whereBetween('fecha_movimiento', [now()->startOfMonth(), now()->endOfMonth()])->sum('monto');
        $ingresosMesPasado = MovimientoCaja::where('tipo', 'ingreso')->whereBetween('fecha_movimiento', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->sum('monto');
        $variacionIngresos = $ingresosMesPasado > 0 ? round((($ingresosMes - $ingresosMesPasado) / $ingresosMesPasado) * 100) : null;

        return view('director.panel.index', [
            'alumnosActivos' => $alumnosActivos,
            'alumnosNuevos' => max(0, $alumnosActivos - $alumnosActivosMesPasado),
            'ingresosMes' => $ingresosMes,
            'variacionIngresos' => $variacionIngresos,
        ]);
    }
}
