<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\Cuota;
use App\Models\MovimientoCaja;
use App\Models\User;
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
            $alumnoSeleccionado = User::where('rol', 'alumno')
                ->with(['inscripcionesCarrera.carrera', 'cuotas' => fn ($q) => $q->orderBy('fecha_vencimiento')])
                ->find($request->query('alumno'));
        } elseif ($busqueda) {
            $resultados = User::where('rol', 'alumno')
                ->where(fn ($q) => $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('apellido', 'like', "%{$busqueda}%")
                    ->orWhere('dni', 'like', "%{$busqueda}%"))
                ->with('inscripcionesCarrera.carrera')
                ->withCount(['cuotas as cuotas_pendientes_count' => fn ($q) => $q->where('estado', 'pendiente')])
                ->orderBy('apellido')
                ->limit(8)
                ->get();
        }

        $historial = Cuota::where('estado', 'pagado')
            ->with('user')
            ->when($request->query('h_alumno'), fn ($q, $v) => $q->whereHas('user', fn ($w) => $w->where('nombre', 'like', "%{$v}%")->orWhere('apellido', 'like', "%{$v}%")))
            ->orderByDesc('fecha_pago')
            ->paginate(8, ['*'], 'historial')
            ->withQueryString();

        return view('director.cuotas.index', [
            'busqueda' => $busqueda,
            'resultados' => $resultados,
            'alumnoSeleccionado' => $alumnoSeleccionado,
            'historial' => $historial,
            'filtroHistorialAlumno' => $request->query('h_alumno'),
        ]);
    }

    public function cobrar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'fecha_pago' => ['required', 'date'],
            'medio_pago' => ['required', 'in:efectivo,transferencia'],
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
            foreach ($seleccionadas as $cuotaId => $item) {
                $cuota = Cuota::where('user_id', $data['user_id'])
                    ->where('estado', 'pendiente')
                    ->find($cuotaId);

                if (! $cuota) {
                    continue;
                }

                $monto = round((float) ($item['monto'] ?? $cuota->monto), 2);
                $recargo = round((float) ($item['recargo'] ?? 0), 2);

                $cuota->update([
                    'monto' => $monto,
                    'recargo' => $recargo,
                    'estado' => 'pagado',
                    'fecha_pago' => $data['fecha_pago'],
                    'medio_pago' => $data['medio_pago'],
                ]);

                $concepto = "Cobro {$cuota->concepto} — {$cuota->user->apellido}, {$cuota->user->nombre}";
                if ($recargo > 0) {
                    $concepto .= ' (incluye recargo $' . number_format($recargo, 0, ',', '.') . ')';
                }

                MovimientoCaja::create([
                    'tipo' => 'ingreso',
                    'concepto' => $concepto,
                    'monto' => $monto + $recargo,
                    'fecha_movimiento' => $data['fecha_pago'],
                    'registrado_por_id' => Auth::id(),
                    'cuota_id' => $cuota->id,
                ]);

                $totalCobrado += $monto + $recargo;
                $cantidad++;
            }
        });

        return redirect()->route('director.cuotas.index')
            ->with('status', "Se registró el cobro de {$cantidad} cuota(s) por $" . number_format($totalCobrado, 0, ',', '.') . ' y se asentó en el Libro de Caja.');
    }
}
