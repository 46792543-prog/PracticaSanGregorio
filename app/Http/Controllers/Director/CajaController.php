<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\ConceptoCaja;
use App\Models\MovimientoCaja;
use App\Models\TipoMovimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CajaController extends Controller
{
    public function index(Request $request): View
    {
        $mes = $request->query('mes') ? \Illuminate\Support\Carbon::parse($request->query('mes') . '-01') : now();

        $resumenHoy = $this->resumen(now()->startOfDay(), now()->endOfDay());
        $resumenSemana = $this->resumen(now()->startOfWeek(), now()->endOfWeek());
        $resumenMes = $this->resumen($mes->copy()->startOfMonth(), $mes->copy()->endOfMonth());

        $movimientos = MovimientoCaja::with(['secretarioRegistra.usuario', 'concepto.tipoMovimiento'])
            ->whereBetween('fecha_movimiento', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])
            ->orderByDesc('fecha_movimiento')
            ->get();

        return view('director.caja.index', [
            'mes' => $mes,
            'resumenHoy' => $resumenHoy,
            'resumenSemana' => $resumenSemana,
            'resumenMes' => $resumenMes,
            'movimientos' => $movimientos,
        ]);
    }

    public function storeGasto(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'concepto' => ['required', 'string', 'max:30'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'fecha_movimiento' => ['required', 'date'],
        ]);

        $tipoEgreso = TipoMovimiento::where('nombre_tipo', 'Egreso')->firstOrFail();

        $concepto = ConceptoCaja::firstOrCreate(
            ['nombre_concepto' => $data['concepto']],
            ['id_tipo_movimiento' => $tipoEgreso->id_tipo_movimiento]
        );

        MovimientoCaja::create([
            'id_concepto' => $concepto->id_concepto,
            'monto' => $data['monto'],
            'fecha_movimiento' => $data['fecha_movimiento'],
            'id_secretario_registra' => Auth::user()->id_persona,
        ]);

        return back()->with('status', 'Gasto registrado correctamente en el Libro de Caja.');
    }

    private function resumen($desde, $hasta): array
    {
        $ingresos = MovimientoCaja::whereHas('concepto.tipoMovimiento', fn ($q) => $q->where('nombre_tipo', 'Ingreso'))
            ->whereBetween('fecha_movimiento', [$desde, $hasta])->sum('monto');
        $gastos = MovimientoCaja::whereHas('concepto.tipoMovimiento', fn ($q) => $q->where('nombre_tipo', 'Egreso'))
            ->whereBetween('fecha_movimiento', [$desde, $hasta])->sum('monto');
        $cantidad = MovimientoCaja::whereBetween('fecha_movimiento', [$desde, $hasta])->count();

        return [
            'ingresos' => $ingresos,
            'gastos' => $gastos,
            'saldo' => $ingresos - $gastos,
            'cantidad' => $cantidad,
        ];
    }
}
