<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AsignacionProfesorMateria;
use App\Models\Carrera;
use App\Models\PagoDocente;
use App\Models\ValorHoraCatedra;
use App\Support\LiquidacionDocente;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Liquidación de horas (panel de Dirección): valor de la hora cátedra por tipo
 * de designación y cuánto le corresponde cobrar a cada docente en un mes.
 */
class LiquidacionHorasController extends Controller
{
    public function index(Request $request): View
    {
        $periodo = $this->periodo($request->query('periodo'));
        $liquidacion = LiquidacionDocente::delMes($periodo);

        // Pagos ya registrados ese mes, para marcar Pagado / Pendiente.
        $pagos = PagoDocente::with('estadoPago')->whereDate('periodo', $periodo->copy()->startOfMonth())->get()->keyBy('id_profesor');

        return view('director.liquidacion-horas.index', [
            'periodo' => $periodo,
            'liquidacion' => $liquidacion,
            'pagos' => $pagos,
            'tipos' => AsignacionProfesorMateria::TIPOS,
            'carreras' => $carreras = Carrera::orderBy('nombre_carrera')->get(),
            // Valor que rige en el mes, por carrera y tipo: [id_carrera][tipo] => ValorHoraCatedra|null
            'valoresVigentes' => $carreras->mapWithKeys(fn ($c) => [$c->id_carrera => collect(AsignacionProfesorMateria::TIPOS)
                ->mapWithKeys(fn ($t) => [$t => ValorHoraCatedra::para($c->id_carrera, $t, $periodo)])]),
            'historial' => ValorHoraCatedra::with('carrera')->orderByDesc('vigente_desde')->orderBy('id_carrera')->orderBy('tipo')->get(),
        ]);
    }

    // Cargar el valor de la hora cátedra de una carrera y tipo, desde un mes.
    public function storeValor(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_carrera' => ['required', 'exists:carrera,id_carrera'],
            'tipo' => ['required', Rule::in(AsignacionProfesorMateria::TIPOS)],
            'monto' => ['required', 'numeric', 'min:1', 'max:1000000'],
            'vigente_desde' => ['required', 'date_format:Y-m'],
        ], [
            'id_carrera.required' => 'Elegí la carrera.',
            'tipo.required' => 'Elegí el tipo de designación.',
            'monto.required' => 'Ingresá el valor de la hora cátedra.',
            'monto.min' => 'El valor tiene que ser mayor a $0.',
            'monto.max' => 'El valor no puede superar $1.000.000.',
            'vigente_desde.required' => 'Elegí desde qué mes rige.',
        ]);

        // Misma carrera, tipo y mes: se corrige el valor (no se duplica).
        ValorHoraCatedra::updateOrCreate(
            ['id_carrera' => $data['id_carrera'], 'tipo' => $data['tipo'], 'vigente_desde' => $data['vigente_desde'] . '-01'],
            ['monto' => round((float) $data['monto'], 2)]
        );

        $carrera = Carrera::find($data['id_carrera'])->nombre_carrera;

        return back()->with('status', "Se guardó el valor de la hora cátedra ({$data['tipo']} · {$carrera}) desde " . Carbon::parse($data['vigente_desde'] . '-01')->translatedFormat('F Y') . '.');
    }

    public function destroyValor(ValorHoraCatedra $valor): RedirectResponse
    {
        $valor->delete();

        return back()->with('status', 'Se eliminó el valor de la hora cátedra.');
    }

    // Para Pago de docentes: cuánto le corresponde a un docente en un mes (JSON).
    public function calcular(Request $request): JsonResponse
    {
        $request->validate(['profesor' => ['required', 'exists:profesor,id_profesor'], 'periodo' => ['required', 'date_format:Y-m']]);

        return response()->json(LiquidacionDocente::calcular($request->integer('profesor'), $this->periodo($request->query('periodo'))));
    }

    private function periodo(?string $valor): Carbon
    {
        return $valor && preg_match('/^\d{4}-\d{2}$/', $valor) ? Carbon::parse($valor . '-01') : now()->startOfMonth();
    }
}
