<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\EstadoPagoDocente;
use App\Models\MetodoPagoDocente;
use App\Models\PagoDocente;
use App\Models\Profesor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return view('director.pagos-docentes.index', [
            'pagos' => $pagos,
            'busqueda' => $busqueda,
            'fechaPago' => $fechaPago,
            'periodo' => $periodo,
            'profesores' => Profesor::with('persona')->get()->sortBy(fn ($p) => $p->apellido),
            'metodosPago' => MetodoPagoDocente::orderBy('nombre_metodo')->get(),
            'estadosPago' => EstadoPagoDocente::orderBy('id_estado_pago_docente')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_profesor' => ['required', 'exists:profesor,id_profesor'],
            'periodo' => ['required', 'date_format:Y-m'],
            'fecha_pago' => ['required', 'date'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'id_metodo_pago_docente' => ['required', 'exists:metodo_pago_docente,id_metodo_pago_docente'],
            'id_estado_pago_docente' => ['required', 'exists:estado_pago_docente,id_estado_pago_docente'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        PagoDocente::create([
            'id_profesor' => $data['id_profesor'],
            'periodo' => $data['periodo'] . '-01',
            'fecha_pago' => $data['fecha_pago'],
            'monto' => round((float) $data['monto'], 2),
            'id_metodo_pago_docente' => $data['id_metodo_pago_docente'],
            'id_estado_pago_docente' => $data['id_estado_pago_docente'],
            'observaciones' => $data['observaciones'] ?: null,
            'id_director_registra' => Auth::user()->id_persona,
        ]);

        return back()->with('status', 'Se registró el pago al docente correctamente.');
    }

    public function update(Request $request, PagoDocente $pagoDocente): RedirectResponse
    {
        $data = $request->validate([
            'id_profesor' => ['required', 'exists:profesor,id_profesor'],
            'periodo' => ['required', 'date_format:Y-m'],
            'fecha_pago' => ['required', 'date'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'id_metodo_pago_docente' => ['required', 'exists:metodo_pago_docente,id_metodo_pago_docente'],
            'id_estado_pago_docente' => ['required', 'exists:estado_pago_docente,id_estado_pago_docente'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        $pagoDocente->update([
            'id_profesor' => $data['id_profesor'],
            'periodo' => $data['periodo'] . '-01',
            'fecha_pago' => $data['fecha_pago'],
            'monto' => round((float) $data['monto'], 2),
            'id_metodo_pago_docente' => $data['id_metodo_pago_docente'],
            'id_estado_pago_docente' => $data['id_estado_pago_docente'],
            'observaciones' => $data['observaciones'] ?: null,
        ]);

        return back()->with('status', 'Se actualizó el pago correctamente.');
    }

    public function destroy(PagoDocente $pagoDocente): RedirectResponse
    {
        $pagoDocente->delete();

        return back()->with('status', 'Se eliminó el pago correctamente.');
    }

    public function actualizarEstado(Request $request, PagoDocente $pagoDocente): RedirectResponse
    {
        $data = $request->validate([
            'id_estado_pago_docente' => ['required', 'exists:estado_pago_docente,id_estado_pago_docente'],
        ]);

        $pagoDocente->update(['id_estado_pago_docente' => $data['id_estado_pago_docente']]);

        return back()->with('status', 'Se actualizó el estado del pago correctamente.');
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
