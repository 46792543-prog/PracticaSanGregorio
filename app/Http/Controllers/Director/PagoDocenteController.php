<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\ConceptoCaja;
use App\Models\EstadoPagoDocente;
use App\Models\MedioPago;
use App\Models\MetodoPagoDocente;
use App\Models\MovimientoCaja;
use App\Models\PagoDocente;
use App\Models\Profesor;
use App\Models\TipoMovimiento;
use App\Support\Auditor;
use App\Support\FechaEsp;
use App\Support\LiquidacionDocente;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PagoDocenteController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q'));
        $fechaPago = $request->query('fecha_pago');
        $periodo = $request->query('periodo');

        $pagos = PagoDocente::with('profesor.persona', 'metodoPago', 'estadoPago')
            ->when($busqueda, fn ($q) => $q->whereHas('profesor.persona', fn ($w) => $w->where('nombre', 'like', "%{$busqueda}%")
                ->orWhere('apellido', 'like', "%{$busqueda}%")
                ->orWhere('dni', 'like', "%{$busqueda}%")))
            ->when($fechaPago, fn ($q, $v) => $q->whereDate('fecha_pago', $v))
            ->when($periodo, fn ($q, $v) => $q->whereYear('periodo', substr($v, 0, 4))->whereMonth('periodo', substr($v, 5, 2)))
            ->orderByDesc('fecha_pago')
            ->paginate(15)
            ->withQueryString();

        $profesores = Profesor::with('persona')->where('activo', true)->get()->sortBy(fn ($p) => $p->apellido);

        // Para que el desplegable del modal sepa, por docente, en qué períodos
        // (AAAA-MM) ya tiene un pago cargado y no deba dejarlo elegir de nuevo
        // (un docente recibe un solo sueldo por mes).
        $periodosPagadosPorProfesor = PagoDocente::select('id_profesor', 'periodo')
            ->get()
            ->groupBy('id_profesor')
            ->map(fn ($pagos) => $pagos->map(fn (PagoDocente $p) => $p->periodo->format('Y-m'))->values());

        return view('director.pagos-docentes.index', [
            'pagos' => $pagos,
            'busqueda' => $busqueda,
            'fechaPago' => $fechaPago,
            'periodo' => $periodo,
            'profesores' => $profesores,
            'periodosPagadosPorProfesor' => $periodosPagadosPorProfesor,
            'metodosPago' => MetodoPagoDocente::orderBy('nombre_metodo')->get(),
            'estadosPago' => EstadoPagoDocente::orderBy('id_estado_pago_docente')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $pago = PagoDocente::create($this->validar($request) + ['id_director_registra' => Auth::user()->id_persona]);
        $this->asentarEnCajaSiPagado($pago);

        return back()->with('status', 'Se registró el pago al docente correctamente.');
    }

    // Editar un pago (para corregir errores de tipeo). El egreso de caja se
    // borra y se vuelve a generar con los datos corregidos.
    public function update(Request $request, PagoDocente $pago): RedirectResponse
    {
        $datos = $this->validar($request, $pago);

        // Motivo obligatorio: queda guardado en Auditoría junto con el cambio.
        $motivo = $request->validate(['motivo' => ['required', 'string', 'min:5', 'max:500']], [
            'motivo.required' => 'Indicá el motivo de la edición del pago.',
            'motivo.min' => 'El motivo es muy corto: contá brevemente por qué se edita.',
        ])['motivo'];

        // Si no se cambió ningún dato, no hay nada que guardar ni auditar.
        if (! $pago->fill($datos)->isDirty()) {
            return back()->with('status', 'No se modificó ningún dato del pago.');
        }

        Auditor::conMotivo($motivo);
        $pago->save();
        $pago->movimientoCaja?->delete();
        $this->asentarEnCajaSiPagado($pago->fresh());

        return back()->with('status', 'Se actualizó el pago al docente.');
    }

    // Eliminar un pago, junto con su egreso en el Libro de Caja (si tenía).
    public function destroy(PagoDocente $pago): RedirectResponse
    {
        $pago->movimientoCaja?->delete();
        $pago->delete();

        return back()->with('status', 'Se eliminó el pago al docente.');
    }

    // Valida los datos del formulario (alta y edición) y los devuelve listos
    // para guardar. Un docente cobra un solo sueldo por mes: si ya tiene otro
    // pago en ese período (sin contar el que se está editando), no se deja.
    private function validar(Request $request, ?PagoDocente $pago = null): array
    {
        $data = $request->validate([
            'id_profesor' => ['required', 'exists:profesor,id_profesor'],
            'periodo' => ['required', 'date_format:Y-m'],
            'fecha_pago' => ['required', 'date'],
            'id_metodo_pago_docente' => ['required', 'exists:metodo_pago_docente,id_metodo_pago_docente'],
            'id_estado_pago_docente' => ['required', 'exists:estado_pago_docente,id_estado_pago_docente'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        // El monto no se escribe: sale de las horas cátedra designadas del docente
        // en ese mes (horas semanales × 4 × valor de la hora según el tipo).
        $liquidacion = LiquidacionDocente::calcular((int) $data['id_profesor'], Carbon::parse($data['periodo'] . '-01'));
        if ($liquidacion['detalle']->isEmpty()) {
            throw ValidationException::withMessages(['id_profesor' => 'Este docente no tiene horas cátedra designadas en ese mes: no hay monto para liquidar.']);
        }
        if ($liquidacion['tipos_sin_valor']->isNotEmpty()) {
            throw ValidationException::withMessages(['id_profesor' => 'Falta cargar el valor de la hora cátedra para: ' . $liquidacion['tipos_sin_valor']->join(', ') . ' (en Liquidación de horas).']);
        }

        if ($liquidacion['total'] <= 0) {
            throw ValidationException::withMessages(['id_profesor' => 'Las inasistencias injustificadas cubren todas sus horas del mes: no hay monto para pagar.']);
        }

        $data['periodo'] .= '-01';
        $data['monto'] = $liquidacion['total'];
        $data['observaciones'] = ($data['observaciones'] ?? null) ?: null;

        $yaTienePago = PagoDocente::where('id_profesor', $data['id_profesor'])
            ->where('periodo', $data['periodo'])
            ->when($pago, fn ($q) => $q->whereKeyNot($pago->id_pago_docente))
            ->exists();

        if ($yaTienePago) {
            throw ValidationException::withMessages(['periodo' => 'Este docente ya tiene un pago registrado para ese período. Un profesor solo puede recibir un sueldo por mes.']);
        }

        return $data;
    }

    /**
     * El sueldo de un docente sale de la caja igual que cualquier otro
     * gasto: si el estado elegido es "Pagado", se asienta solo como Egreso
     * en el Libro de Caja. Si quedó en otro estado (ej. "Pendiente"), no se
     * toca la caja; si después se edita a "Pagado", el egreso se genera ahí.
     */
    private function asentarEnCajaSiPagado(PagoDocente $pago): void
    {
        if (strcasecmp($pago->estadoPago->nombre_estado, 'pagado') !== 0) {
            return;
        }

        $tipoEgreso = TipoMovimiento::where('nombre_tipo', 'Egreso')->firstOrFail();
        $concepto = ConceptoCaja::firstOrCreate(
            ['nombre_concepto' => 'Pago a docente'],
            ['id_tipo_movimiento' => $tipoEgreso->id_tipo_movimiento]
        );

        $medioPago = MedioPago::whereRaw('LOWER(nombre_medio) = ?', [mb_strtolower($pago->metodoPago->nombre_metodo)])->first()
            ?? MedioPago::where('nombre_medio', 'Efectivo')->firstOrFail();

        $descripcion = "Pago a {$pago->profesor->apellido}, {$pago->profesor->nombre} — período " . FechaEsp::mesAnio($pago->periodo);

        $movimiento = MovimientoCaja::create([
            'id_concepto' => $concepto->id_concepto,
            'monto' => $pago->monto,
            'fecha_movimiento' => now()->setDateFrom($pago->fecha_pago), 
            'descripcion_detalle' => $descripcion,
            'id_secretario_registra' => Auth::user()->id_persona,
            'id_medio_pago' => $medioPago->id_medio_pago,
        ]);

        // Enlace interno con la caja: se guarda sin auditar, para que en
        // Auditoría quede una sola fila con los cambios del pago y el motivo.
        $pago->forceFill(['id_movimiento_caja' => $movimiento->id_movimiento])->saveQuietly();
    }

    public function storeMetodoPago(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_metodo' => ['required', 'string', 'max:30', 'unique:metodo_pago_docente,nombre_metodo'],
        ]);

        MetodoPagoDocente::create($data);

        return back()->with('status', 'Método de pago agregado correctamente.');
    }

    public function storeEstadoPago(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_estado' => ['required', 'string', 'max:30', 'unique:estado_pago_docente,nombre_estado'],
        ]);

        EstadoPagoDocente::create($data);

        return back()->with('status', 'Estado agregado correctamente.');
    }
}
