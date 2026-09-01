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
        $alumnosActivos = InscripcionCarrera::whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->distinct('id_persona_alumno')->count('id_persona_alumno');
        $alumnosActivosMesPasado = InscripcionCarrera::whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->where('fecha_inscripcion', '<', now()->startOfMonth())
            ->distinct('id_persona_alumno')->count('id_persona_alumno');

        $ingresosMes = MovimientoCaja::whereHas('concepto.tipoMovimiento', fn ($q) => $q->where('nombre_tipo', 'Ingreso'))
            ->whereBetween('fecha_movimiento', [now()->startOfMonth(), now()->endOfMonth()])->sum('monto');
        $ingresosMesPasado = MovimientoCaja::whereHas('concepto.tipoMovimiento', fn ($q) => $q->where('nombre_tipo', 'Ingreso'))
            ->whereBetween('fecha_movimiento', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->sum('monto');
        $variacionIngresos = $ingresosMesPasado > 0 ? round((($ingresosMes - $ingresosMesPasado) / $ingresosMesPasado) * 100) : null;

        return view('director.panel.index', [
            'alumnosActivos' => $alumnosActivos,
            'alumnosNuevos' => max(0, $alumnosActivos - $alumnosActivosMesPasado),
            'ingresosMes' => $ingresosMes,
            'variacionIngresos' => $variacionIngresos,
        ]);
    }
}
