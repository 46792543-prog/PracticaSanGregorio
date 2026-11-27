<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AsignacionProfesorMateria;
use App\Models\Carrera;
use App\Models\ConfiguracionInstitucion;
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
            'tope' => ValorHoraCatedra::tope(),
            'historial' => ValorHoraCatedra::with('carrera')->orderByDesc('vigente_desde')->orderBy('id_carrera')->orderBy('tipo')->get(),
        ]);
    }

    // Cargar el valor de la hora cátedra de una carrera y tipo, desde un mes.
    public function storeValor(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_carrera' => ['required', 'exists:carrera,id_carrera'],
            'tipo' => ['required', Rule::in(AsignacionProfesorMateria::TIPOS)],
            'monto' => ['required', 'numeric', 'min:1', 'max:' . ValorHoraCatedra::tope()],
            'vigente_desde' => ['required', 'date_format:Y-m'],
        ], [
            'id_carrera.required' => 'Elegí la carrera.',
            'tipo.required' => 'Elegí el tipo de designación.',
            'monto.required' => 'Ingresá el valor de la hora cátedra.',
            'monto.min' => 'El valor tiene que ser mayor a $0.',
            'monto.max' => 'El valor no puede superar $' . number_format(ValorHoraCatedra::tope(), 0, ',', '.') . ' por hora cátedra (tope configurado).',
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

    // Cambiar el valor máximo que se puede cargar por hora cátedra.
    public function updateTope(Request $request): RedirectResponse
    {
        // No puede quedar por debajo de un valor que ya está cargado.
        $mayorCargado = (float) ValorHoraCatedra::max('monto');

        $data = $request->validate([
            'tope_valor_hora_catedra' => ['required', 'numeric', 'min:' . max(1, $mayorCargado), 'max:1000000'],
        ], [
            'tope_valor_hora_catedra.required' => 'Ingresá el valor máximo por hora cátedra.',
            'tope_valor_hora_catedra.min' => $mayorCargado > 0
                ? 'El tope no puede ser menor a $' . number_format($mayorCargado, 0, ',', '.') . ', que es un valor ya cargado.'
                : 'El tope tiene que ser mayor a $0.',
            'tope_valor_hora_catedra.max' => 'El tope no puede superar $1.000.000.',
        ]);

        ConfiguracionInstitucion::firstOrFail()->update(['tope_valor_hora_catedra' => round((float) $data['tope_valor_hora_catedra'], 2)]);

        return back()->with('status', 'Se guardó el tope: hasta $' . number_format((float) $data['tope_valor_hora_catedra'], 0, ',', '.') . ' por hora cátedra.');
    }

    // Editar un valor del historial (lápiz): el monto y desde qué mes rige.
    public function updateValor(Request $request, ValorHoraCatedra $valor): RedirectResponse
    {
        $data = $request->validate([
            'monto' => ['required', 'numeric', 'min:1', 'max:' . ValorHoraCatedra::tope()],
            'vigente_desde' => ['required', 'date_format:Y-m'],
        ], [
            'monto.required' => 'Ingresá el valor de la hora cátedra.',
            'monto.min' => 'El valor tiene que ser mayor a $0.',
            'monto.max' => 'El valor no puede superar $' . number_format(ValorHoraCatedra::tope(), 0, ',', '.') . ' por hora cátedra (tope configurado).',
            'vigente_desde.required' => 'Elegí desde qué mes rige.',
        ]);

        // No puede quedar repetido: misma carrera, tipo y mes que otro valor ya cargado.
        $repetido = ValorHoraCatedra::where('id_carrera', $valor->id_carrera)->where('tipo', $valor->tipo)
            ->whereDate('vigente_desde', $data['vigente_desde'] . '-01')
            ->where($valor->getKeyName(), '!=', $valor->getKey())->exists();
        if ($repetido) {
            return back()->withErrors(['monto' => "Ya hay un valor de {$valor->tipo} para esa carrera desde ese mes. Editá ese o elegí otro mes."]);
        }

        $valor->update(['monto' => round((float) $data['monto'], 2), 'vigente_desde' => $data['vigente_desde'] . '-01']);

        return back()->with('status', "Se actualizó el valor de la hora cátedra ({$valor->tipo} · {$valor->carrera->nombre_carrera}).");
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
