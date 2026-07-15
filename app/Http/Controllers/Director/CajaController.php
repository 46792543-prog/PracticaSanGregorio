<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\MovimientoCaja;
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

        $movimientos = MovimientoCaja::with('registradoPor')
            ->whereBetween('fecha_movimiento', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])
            ->when($request->query('turno'), fn ($q, $turno) => $q->where('turno', $turno))
            ->orderByDesc('fecha_movimiento')
            ->get();

        return view('director.caja.index', [
            'mes' => $mes,
            'resumenHoy' => $resumenHoy,
            'resumenSemana' => $resumenSemana,
            'resumenMes' => $resumenMes,
            'movimientos' => $movimientos,
            'turno' => $request->query('turno'),
        ]);
    }

    public function storeGasto(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'concepto' => ['required', 'string', 'max:150'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha_movimiento' => ['required', 'date'],
            'turno' => ['nullable', 'string', 'max:20'],
        ]);

        MovimientoCaja::create($data + [
            'tipo' => 'gasto',
            'registrado_por_id' => Auth::id(),
        ]);

        return back()->with('status', 'Gasto registrado correctamente en el Libro de Caja.');
    }

    private function resumen($desde, $hasta): array
    {
        $ingresos = MovimientoCaja::where('tipo', 'ingreso')->whereBetween('fecha_movimiento', [$desde, $hasta])->sum('monto');
        $gastos = MovimientoCaja::where('tipo', 'gasto')->whereBetween('fecha_movimiento', [$desde, $hasta])->sum('monto');
        $cantidad = MovimientoCaja::whereBetween('fecha_movimiento', [$desde, $hasta])->count();

        return [
            'ingresos' => $ingresos,
            'gastos' => $gastos,
            'saldo' => $ingresos - $gastos,
            'cantidad' => $cantidad,
        ];
    }
}
