<?php

namespace App\Http\Controllers\Director;

use App\Exports\CajaExport;
use App\Http\Controllers\Controller;
use App\Models\ConceptoCaja;
use App\Models\MedioPago;
use App\Models\MovimientoCaja;
use App\Models\TipoMovimiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class CajaController extends Controller
{
    public function index(Request $request): View
    {
        $mes = $request->query('mes') ? \Illuminate\Support\Carbon::parse($request->query('mes') . '-01') : now();

        $resumenHoy = $this->resumen(now()->startOfDay(), now()->endOfDay());
        $resumenSemana = $this->resumen(now()->startOfWeek(), now()->endOfWeek());
        $resumenMes = $this->resumen($mes->copy()->startOfMonth(), $mes->copy()->endOfMonth());

        // Filtro: "Ingreso", "Egreso" o el id de una categoría de egreso.
        $filtro = $request->query('filtro');
        $categorias = $this->categoriasEgreso();
        $movimientos = MovimientoCaja::with(['secretarioRegistra.usuario', 'concepto.tipoMovimiento'])
            ->whereBetween('fecha_movimiento', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])
            ->when(in_array($filtro, ['Ingreso', 'Egreso'], true), fn ($q) => $q->whereHas('concepto.tipoMovimiento', fn ($t) => $t->where('nombre_tipo', $filtro)))
            ->when(ctype_digit((string) $filtro), fn ($q) => $q->where('id_concepto', (int) $filtro))
            // Lo último que se registró va primero (N° 01), aunque la fecha del pago sea de otro día.
            ->orderByDesc('id_movimiento')
            ->paginate(20)
            ->withQueryString();

        return view('director.caja.index', [
            'mes' => $mes,
            'resumenHoy' => $resumenHoy,
            'resumenSemana' => $resumenSemana,
            'resumenMes' => $resumenMes,
            'movimientos' => $movimientos,
            'filtro' => $filtro,
            'filtroNombre' => in_array($filtro, ['Ingreso', 'Egreso'], true) ? "Solo {$filtro}s" : $categorias->firstWhere('id_concepto', (int) $filtro)?->nombre_concepto,
            'categorias' => $categorias,
            // Las que se cargan a mano (los honorarios docentes se asientan solos desde Pago de Docentes).
            'categoriasManuales' => $categorias->where('nombre_concepto', '!=', MovimientoCaja::HONORARIOS_DOCENTES)->values(),
            // Egresos del mes por categoría (en qué se usa el dinero).
            'egresosPorCategoria' => MovimientoCaja::with('concepto')
                ->whereBetween('fecha_movimiento', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])
                ->whereHas('concepto.tipoMovimiento', fn ($q) => $q->where('nombre_tipo', 'Egreso'))
                ->get()->groupBy('id_concepto')
                ->map(fn ($movs) => ['id' => $movs->first()->id_concepto, 'nombre' => $movs->first()->concepto->nombre_concepto, 'total' => $movs->sum('monto')])
                ->sortByDesc('total'),
        ]);
    }

    public function pdf(Request $request): Response
    {
        $mes = $request->query('mes') ? Carbon::parse($request->query('mes') . '-01') : now();

        $resumenMes = $this->resumen($mes->copy()->startOfMonth(), $mes->copy()->endOfMonth());

        $movimientos = MovimientoCaja::with(['secretarioRegistra.usuario', 'concepto.tipoMovimiento'])
            ->whereBetween('fecha_movimiento', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])
            ->orderBy('fecha_movimiento')->orderBy('id_movimiento') // cronológico; mismo momento: en el orden en que se registró
            ->get();

        $pdf = Pdf::loadView('director.caja.pdf', [
            'mes' => $mes,
            'resumenMes' => $resumenMes,
            'movimientos' => $movimientos,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("libro-caja-{$mes->format('Y-m')}.pdf");
    }

    public function excel(Request $request)
    {
        $mes = $request->query('mes') ? Carbon::parse($request->query('mes') . '-01') : now();

        $movimientos = MovimientoCaja::with(['secretarioRegistra.usuario', 'concepto.tipoMovimiento'])
            ->whereBetween('fecha_movimiento', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])
            ->orderBy('fecha_movimiento')->orderBy('id_movimiento') // cronológico; mismo momento: en el orden en que se registró
            ->get();

        return Excel::download(new CajaExport($movimientos), "libro-caja-{$mes->format('Y-m')}.xlsx");
    }

    // Registrar un egreso (cualquier salida de dinero), con su categoría.
    public function storeEgreso(Request $request): RedirectResponse
    {
        $idsManuales = $this->categoriasEgreso()->where('nombre_concepto', '!=', MovimientoCaja::HONORARIOS_DOCENTES)->pluck('id_concepto')->all();
        $data = $request->validate([
            'id_concepto' => ['required', Rule::in($idsManuales)],
            'concepto' => ['required', 'string', 'min:3', 'max:150'],
            'beneficiario' => ['nullable', 'string', 'max:100'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'fecha_movimiento' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'id_concepto.required' => 'Elegí la categoría del egreso.',
            'id_concepto.in' => 'Elegí una categoría de la lista. Los honorarios docentes se registran desde Pago de Docentes.',
            'concepto.required' => 'Escribí el concepto (en qué se usó el dinero).',
            'concepto.min' => 'El concepto es muy corto.',
            'monto.min' => 'El monto tiene que ser mayor a $0.',
            'monto.max' => 'El monto no puede superar $9.999.999,99.',
            'fecha_movimiento.before_or_equal' => 'La fecha no puede ser futura.',
        ]);

        $medioPago = MedioPago::firstOrCreate(['nombre_medio' => 'Efectivo']);

        MovimientoCaja::create([
            'id_concepto' => (int) $data['id_concepto'], // la categoría es un concepto de caja de tipo Egreso
            'descripcion_detalle' => trim($data['concepto']),
            'beneficiario' => trim((string) ($data['beneficiario'] ?? '')) ?: null,
            'monto' => round((float) $data['monto'], 2),
            'fecha_movimiento' => now()->setDateFrom($data['fecha_movimiento']), // fecha elegida + hora en que se registra
            'id_secretario_registra' => Auth::user()->id_persona,
            'id_medio_pago' => $medioPago->id_medio_pago,
        ]);

        return back()->with('status', 'Egreso registrado en el Libro de Caja.');
    }

    // Nueva categoría de egreso (desde el "+"). Responde JSON.
    public function storeCategoria(Request $request): JsonResponse
    {
        $nombre = $this->validarCategoria($request);
        $categoria = ConceptoCaja::create(['nombre_concepto' => $nombre, 'id_tipo_movimiento' => TipoMovimiento::firstOrCreate(['nombre_tipo' => 'Egreso'])->id_tipo_movimiento]);

        return response()->json(['id' => $categoria->id_concepto, 'nombre' => $categoria->nombre_concepto]);
    }

    // Renombrar una categoría de egreso (desde el lápiz). Sus egresos pasan al nombre nuevo.
    public function updateCategoria(Request $request, ConceptoCaja $concepto): JsonResponse
    {
        abort_unless($this->categoriasEgreso()->contains('id_concepto', $concepto->id_concepto), 404);
        if ($concepto->nombre_concepto === MovimientoCaja::HONORARIOS_DOCENTES) {
            throw ValidationException::withMessages(['nombre' => 'Esta categoría la usa Pago de Docentes: no se puede cambiar el nombre.']);
        }
        $concepto->update(['nombre_concepto' => $this->validarCategoria($request, $concepto)]);

        return response()->json(['id' => $concepto->id_concepto, 'nombre' => $concepto->nombre_concepto]);
    }

    // Nombre de categoría: obligatorio, sin repetir (sin importar mayúsculas) y distinto de los honorarios docentes.
    private function validarCategoria(Request $request, ?ConceptoCaja $actual = null): string
    {
        $nombre = trim(preg_replace('/\s+/', ' ', (string) $request->input('nombre')));
        $request->merge(['nombre' => $nombre])->validate([
            'nombre' => ['required', 'string', 'min:3', 'max:60'],
        ], [
            'nombre.required' => 'Escribí el nombre de la categoría.',
            'nombre.min' => 'El nombre es muy corto.',
            'nombre.max' => 'El nombre puede tener hasta 60 letras.',
        ]);
        $repetida = ConceptoCaja::whereRaw('LOWER(nombre_concepto) = ?', [mb_strtolower($nombre)])
            ->when($actual, fn ($q) => $q->whereKeyNot($actual->id_concepto))->exists();
        if ($repetida || mb_strtolower($nombre) === mb_strtolower(MovimientoCaja::HONORARIOS_DOCENTES)) {
            throw ValidationException::withMessages(['nombre' => 'Ya existe una categoría (o concepto de caja) con ese nombre.']);
        }

        return $nombre;
    }

    // Categorías de egreso: conceptos de caja de tipo Egreso ("Otros" al final).
    private function categoriasEgreso(): \Illuminate\Support\Collection
    {
        return ConceptoCaja::whereHas('tipoMovimiento', fn ($q) => $q->where('nombre_tipo', 'Egreso'))
            ->orderByRaw("nombre_concepto = 'Otros'")->orderBy('nombre_concepto')->get();
    }

    private function resumen($desde, $hasta): array
    {
        $ingresos = MovimientoCaja::whereHas('concepto.tipoMovimiento', fn ($q) => $q->where('nombre_tipo', 'Ingreso'))
            ->whereBetween('fecha_movimiento', [$desde, $hasta])->sum('monto');
        $egresos = MovimientoCaja::whereHas('concepto.tipoMovimiento', fn ($q) => $q->where('nombre_tipo', 'Egreso'))
            ->whereBetween('fecha_movimiento', [$desde, $hasta])->sum('monto');
        $cantidad = MovimientoCaja::whereBetween('fecha_movimiento', [$desde, $hasta])->count();

        return [
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'saldo' => $ingresos - $egresos,
            'cantidad' => $cantidad,
        ];
    }
}
