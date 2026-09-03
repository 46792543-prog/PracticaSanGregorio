<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\ConceptoCaja;
use App\Models\CuotaAlumno;
use App\Models\MedioPago;
use App\Models\Mes;
use App\Models\MovimientoCaja;
use App\Models\TipoMovimiento;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CuotaController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q'));
        $alumnoSeleccionado = null;
        $resultados = collect();

        if ($request->query('alumno')) {
            $alumnoSeleccionado = Usuario::alumnos()
                ->with([
                    'persona.inscripcionesCarrera.carrera',
                    'persona.cuotas' => fn ($q) => $q->with('mes')->orderBy('id_anio_lectivo')->orderBy('id_mes'),
                ])
                ->find($request->query('alumno'));
        } elseif ($busqueda) {
            $resultados = Usuario::alumnos()
                ->whereHas('persona', fn ($q) => $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('apellido', 'like', "%{$busqueda}%")
                    ->orWhere('dni', 'like', "%{$busqueda}%"))
                ->with('persona.inscripcionesCarrera.carrera')
                ->get()
                ->sortBy(fn ($u) => $u->persona->apellido)
                ->take(8)
                ->values()
                ->map(function (Usuario $u) {
                    $u->cuotas_pendientes_count = $u->persona->cuotas()->where('pagado', false)->count();

                    return $u;
                });
        }

        $historial = CuotaAlumno::where('pagado', true)
            ->with('personaAlumno', 'mes', 'movimientoCaja.medioPago')
            ->when($request->query('h_alumno'), fn ($q, $v) => $q->whereHas('personaAlumno', fn ($w) => $w->where('nombre', 'like', "%{$v}%")->orWhere('apellido', 'like', "%{$v}%")))
            ->orderByDesc('id_cuota')
            ->paginate(8, ['*'], 'historial')
            ->withQueryString();

        return view('director.cuotas.index', [
            'busqueda' => $busqueda,
            'resultados' => $resultados,
            'alumnoSeleccionado' => $alumnoSeleccionado,
            'historial' => $historial,
            'filtroHistorialAlumno' => $request->query('h_alumno'),
            'anios' => AnioLectivo::orderByDesc('anio')->get(),
            'meses' => Mes::orderBy('id_mes')->get(),
        ]);
    }

    public function generar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'alumno_usuario_id' => ['required', 'exists:usuario,id_usuario'],
            'persona_id' => ['required', 'exists:persona,id_persona'],
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'meses' => ['required', 'array', 'min:1'],
            'meses.*' => ['integer', 'exists:mes,id_mes'],
            'concepto' => ['nullable', 'string', 'max:20', 'regex:/^[\pL0-9\s]+$/u'],
            'monto' => ['required', 'numeric', 'min:0'],
            'dia_vencimiento' => ['required', 'integer', 'min:1', 'max:28'],
        ]);

        $anio = AnioLectivo::findOrFail($data['id_anio_lectivo']);
        $creadas = 0;
        $omitidas = 0;

        DB::transaction(function () use ($data, $anio, &$creadas, &$omitidas) {
            foreach ($data['meses'] as $idMes) {
                $existe = CuotaAlumno::where('id_persona_alumno', $data['persona_id'])
                    ->where('id_anio_lectivo', $anio->id_anio_lectivo)
                    ->where('id_mes', $idMes)
                    ->exists();

                if ($existe) {
                    $omitidas++;

                    continue;
                }

                CuotaAlumno::create([
                    'id_persona_alumno' => $data['persona_id'],
                    'id_anio_lectivo' => $anio->id_anio_lectivo,
                    'id_mes' => $idMes,
                    'concepto' => $data['concepto'] ?: null,
                    'fecha_vencimiento' => sprintf('%04d-%02d-%02d', $anio->anio, $idMes, $data['dia_vencimiento']),
                    'monto' => round((float) $data['monto'], 2),
                    'recargo' => 0,
                    'pagado' => false,
                ]);

                $creadas++;
            }
        });

        $mensaje = $creadas > 0
            ? "Se generaron {$creadas} cuota(s) nueva(s)."
            : 'No se generó ninguna cuota nueva.';

        if ($omitidas > 0) {
            $mensaje .= " Se omitieron {$omitidas} porque ya existían para ese alumno y año.";
        }

        return redirect()->route('director.cuotas.index', ['alumno' => $data['alumno_usuario_id']])
            ->with('status', $mensaje);
    }

    public function actualizar(Request $request, CuotaAlumno $cuota): RedirectResponse
    {
        abort_if($cuota->pagado, 403, 'No se puede modificar una cuota que ya fue cobrada.');

        $data = $request->validate([
            'alumno_usuario_id' => ['required', 'exists:usuario,id_usuario'],
            'concepto' => ['nullable', 'string', 'max:20', 'regex:/^[\pL0-9\s]+$/u'],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha_vencimiento' => ['nullable', 'date'],
        ]);

        $cuota->update([
            'concepto' => $data['concepto'] ?: null,
            'monto' => round((float) $data['monto'], 2),
            'fecha_vencimiento' => $data['fecha_vencimiento'] ?: null,
        ]);

        return redirect()->route('director.cuotas.index', ['alumno' => $data['alumno_usuario_id']])
            ->with('status', 'Se actualizó la cuota correctamente.');
    }

    public function eliminar(Request $request, CuotaAlumno $cuota): RedirectResponse
    {
        abort_if($cuota->pagado, 403, 'No se puede eliminar una cuota que ya fue cobrada.');

        $data = $request->validate([
            'alumno_usuario_id' => ['required', 'exists:usuario,id_usuario'],
        ]);

        $cuota->delete();

        return redirect()->route('director.cuotas.index', ['alumno' => $data['alumno_usuario_id']])
            ->with('status', 'Se eliminó la cuota correctamente.');
    }

    public function cobrar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'persona_id' => ['required', 'exists:persona,id_persona'],
            'fecha_pago' => ['required', 'date'],
            'medio_pago' => ['required', 'in:Efectivo,Transferencia'],
            'cuotas' => ['required', 'array', 'min:1'],
            'cuotas.*.pagar' => ['nullable'],
            'cuotas.*.monto' => ['nullable', 'numeric', 'min:0'],
            'cuotas.*.recargo' => ['nullable', 'numeric', 'min:0'],
        ]);

        $seleccionadas = collect($data['cuotas'])->filter(fn ($item) => ! empty($item['pagar']));

        if ($seleccionadas->isEmpty()) {
            return back()->withErrors(['cuotas' => 'Seleccioná al menos una cuota para registrar el cobro.']);
        }

        $totalCobrado = 0;
        $cantidad = 0;

        DB::transaction(function () use ($seleccionadas, $data, &$totalCobrado, &$cantidad) {
            $medioPago = MedioPago::where('nombre_medio', $data['medio_pago'])->firstOrFail();
            $tipoIngreso = TipoMovimiento::where('nombre_tipo', 'Ingreso')->firstOrFail();
            $conceptoCuota = ConceptoCaja::firstOrCreate(
                ['nombre_concepto' => 'Cuota mensual'],
                ['id_tipo_movimiento' => $tipoIngreso->id_tipo_movimiento]
            );

            foreach ($seleccionadas as $cuotaId => $item) {
                $cuota = CuotaAlumno::where('id_persona_alumno', $data['persona_id'])
                    ->where('pagado', false)
                    ->find($cuotaId);

                if (! $cuota) {
                    continue;
                }

                $monto = round((float) ($item['monto'] ?? $cuota->monto), 2);
                $recargo = round((float) ($item['recargo'] ?? 0), 2);
                $conceptoTexto = $cuota->concepto ?: "Cuota {$cuota->mes->nombre_mes}";

                $descripcion = "{$conceptoTexto} — {$cuota->personaAlumno->apellido}, {$cuota->personaAlumno->nombre}";
                if ($recargo > 0) {
                    $descripcion .= ' (incluye recargo $' . number_format($recargo, 0, ',', '.') . ')';
                }

                $movimiento = MovimientoCaja::create([
                    'id_concepto' => $conceptoCuota->id_concepto,
                    'monto' => $monto + $recargo,
                    'fecha_movimiento' => $data['fecha_pago'],
                    'descripcion_detalle' => $descripcion,
                    'id_secretario_registra' => Auth::user()->id_persona,
                    'id_medio_pago' => $medioPago->id_medio_pago,
                ]);

                $cuota->update([
                    'monto' => $monto,
                    'recargo' => $recargo,
                    'pagado' => true,
                    'fecha_pago' => $data['fecha_pago'],
                    'id_movimiento_caja' => $movimiento->id_movimiento,
                ]);

                $totalCobrado += $monto + $recargo;
                $cantidad++;
            }
        });

        return redirect()->route('director.cuotas.index')
            ->with('status', "Se registró el cobro de {$cantidad} cuota(s) por $" . number_format($totalCobrado, 0, ',', '.') . ' y se asentó en el Libro de Caja.');
    }
}
