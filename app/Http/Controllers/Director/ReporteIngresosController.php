<?php

namespace App\Http\Controllers\Director;

use App\Exports\IngresosExport;
use App\Http\Controllers\Controller;
use App\Models\ConceptoCaja;
use App\Models\MedioPago;
use App\Models\MovimientoCaja;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReporteIngresosController extends Controller
{
    public function index(Request $request): View
    {
        [$desde, $hasta] = $this->rango($request);

        $todos = $this->consulta($request, $desde, $hasta)->orderByDesc('fecha_movimiento')->get();

        $ingresos = $this->paginar($todos, (int) $request->query('page', 1))->withQueryString();

        return view('director.reportes.ingresos.index', [
            'desde' => $desde,
            'hasta' => $hasta,
            'conceptoId' => $request->query('concepto'),
            'medioPagoId' => $request->query('medio_pago'),
            'ingresos' => $ingresos,
            'resumen' => $this->resumen($todos),
            'porConcepto' => $this->totalesPorConcepto($todos),
            'porMedioPago' => $this->totalesPorMedioPago($todos),
            'conceptos' => ConceptoCaja::whereHas('tipoMovimiento', fn ($q) => $q->where('nombre_tipo', 'Ingreso'))
                ->orderBy('nombre_concepto')->get(),
            'mediosPago' => MedioPago::orderBy('nombre_medio')->get(),
        ]);
    }

    public function pdf(Request $request): Response
    {
        [$desde, $hasta] = $this->rango($request);

        $ingresos = $this->consulta($request, $desde, $hasta)->orderBy('fecha_movimiento')->get();

        $pdf = Pdf::loadView('director.reportes.ingresos.pdf', [
            'desde' => $desde,
            'hasta' => $hasta,
            'ingresos' => $ingresos,
            'resumen' => $this->resumen($ingresos),
            'porConcepto' => $this->totalesPorConcepto($ingresos),
            'porMedioPago' => $this->totalesPorMedioPago($ingresos),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("reporte-ingresos-{$desde->format('Y-m-d')}_{$hasta->format('Y-m-d')}.pdf");
    }

    public function excel(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);

        $ingresos = $this->consulta($request, $desde, $hasta)->orderBy('fecha_movimiento')->get();

        return Excel::download(
            new IngresosExport($ingresos),
            "reporte-ingresos-{$desde->format('Y-m-d')}_{$hasta->format('Y-m-d')}.xlsx"
        );
    }

    private function rango(Request $request): array
    {
        $desde = $request->query('desde')
            ? Carbon::parse($request->query('desde'))->startOfDay()
            : now()->startOfMonth();

        $hasta = $request->query('hasta')
            ? Carbon::parse($request->query('hasta'))->endOfDay()
            : now()->endOfMonth();

        return [$desde, $hasta];
    }

    private function consulta(Request $request, Carbon $desde, Carbon $hasta)
    {
        return MovimientoCaja::with(['secretarioRegistra.usuario', 'concepto', 'medioPago'])
            ->whereHas('concepto.tipoMovimiento', fn ($q) => $q->where('nombre_tipo', 'Ingreso'))
            ->whereBetween('fecha_movimiento', [$desde, $hasta])
            ->when($request->query('concepto'), fn ($q, $v) => $q->where('id_concepto', $v))
            ->when($request->query('medio_pago'), fn ($q, $v) => $q->where('id_medio_pago', $v));
    }

    private function paginar(Collection $items, int $pagina, int $porPagina = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($pagina, $porPagina)->values(),
            $items->count(),
            $porPagina,
            $pagina,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    private function resumen(Collection $movimientos): array
    {
        return [
            'total' => (float) $movimientos->sum('monto'),
            'cantidad' => $movimientos->count(),
            'promedio' => $movimientos->count() ? (float) $movimientos->avg('monto') : 0.0,
        ];
    }

    private function totalesPorConcepto(Collection $movimientos): Collection
    {
        return $movimientos
            ->groupBy(fn (MovimientoCaja $m) => $m->concepto->nombre_concepto)
            ->map(fn (Collection $grupo, string $nombre) => [
                'concepto' => $nombre,
                'total' => (float) $grupo->sum('monto'),
                'cantidad' => $grupo->count(),
            ])
            ->sortByDesc('total')
            ->values();
    }

    private function totalesPorMedioPago(Collection $movimientos): Collection
    {
        return $movimientos
            ->groupBy(fn (MovimientoCaja $m) => $m->medioPago->nombre_medio ?? 'Sin especificar')
            ->map(fn (Collection $grupo, string $nombre) => [
                'medio' => $nombre,
                'total' => (float) $grupo->sum('monto'),
                'cantidad' => $grupo->count(),
            ])
            ->sortByDesc('total')
            ->values();
    }
}
