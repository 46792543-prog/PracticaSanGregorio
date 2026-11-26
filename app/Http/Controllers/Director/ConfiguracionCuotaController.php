<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\AumentoCuota;
use App\Models\Carrera;
use App\Models\CondicionAlumno;
use App\Models\ConfiguracionCuota;
use App\Models\CuotaAlumno;
use App\Models\InscripcionCarrera;
use App\Models\Mes;
use App\Support\GeneradorCuotas;
use App\Support\RecalculadorCuotas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Configuración económica de cuotas (monto mensual, aumentos, día de
 * vencimiento e interés por mora) por período lectivo. Accede quien entra
 * al panel de dirección (Director o Secretario Administrativo, ver
 * middleware EnsureDirector): al guardarla, el sistema genera
 * automáticamente la cuota correspondiente a todos los alumnos con
 * inscripción activa en ese año, sin cargarlas una por una.
 */
class ConfiguracionCuotaController extends Controller
{
    public function index(): View
    {
        $anioActivo = AnioLectivo::activo();

        return view('director.configuracion-cuotas.index', [
            'anios' => AnioLectivo::orderByDesc('anio')->get(),
            'anioActivoId' => $anioActivo?->id_anio_lectivo,
            // Clave "año-carrera": una configuración por cada combinación.
            'configuraciones' => ConfiguracionCuota::with('anioLectivo', 'aumentos', 'carrera')->get()->keyBy(fn ($c) => "{$c->id_anio_lectivo}-{$c->id_carrera}"),
            'carreras' => Carrera::orderBy('nombre_carrera')->get(),
            'meses' => Mes::orderBy('id_mes')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'id_carrera' => ['required', 'exists:carrera,id_carrera'],
            'monto_mensual' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'mes_desde' => ['required', 'integer', 'between:1,12'],
            'mes_hasta' => ['required', 'integer', 'between:1,12', 'gte:mes_desde'],
            'dia_vencimiento' => ['required', 'integer', 'min:1', 'max:31'],
            'porcentaje_interes_mora' => ['required', 'numeric', 'min:0', 'max:100'],
            'concepto' => ['nullable', 'string', 'max:120', 'regex:/^[\pL\s\'-]+$/u'],
        ], [
            'monto_mensual.required' => 'Ingresá el monto de la cuota mensual.',
            'monto_mensual.numeric' => 'El monto tiene que ser un número.',
            'monto_mensual.min' => 'El monto tiene que ser mayor a $0.',
            'monto_mensual.max' => 'El monto no puede superar $1.000.000.',
            'mes_hasta.gte' => 'El mes hasta tiene que ser igual o posterior al mes desde.',
            'porcentaje_interes_mora.max' => 'El interés por mora no puede superar el 100%.',
            'concepto.regex' => 'El concepto solo puede tener letras.',
        ]);

        $this->validarContraAumentos($data);

        $configuracion = ConfiguracionCuota::updateOrCreate(
            ['id_anio_lectivo' => $data['id_anio_lectivo'], 'id_carrera' => $data['id_carrera']],
            [
                'monto_mensual' => round((float) $data['monto_mensual'], 2),
                'mes_desde' => $data['mes_desde'],
                'mes_hasta' => $data['mes_hasta'],
                'dia_vencimiento' => $data['dia_vencimiento'],
                'porcentaje_interes_mora' => round((float) $data['porcentaje_interes_mora'], 2),
                'concepto' => $data['concepto'] ?: null,
            ]
        );

        $configuracion->load('anioLectivo', 'aumentos', 'carrera');

        $creadas = GeneradorCuotas::generar($configuracion);
        $actualizadas = RecalculadorCuotas::aplicar($configuracion);

        $mensaje = "Se guardó la configuración de cuotas de {$configuracion->carrera->nombre_carrera} para el año {$configuracion->anioLectivo->anio}.";
        $mensaje .= $creadas > 0
            ? " Se generaron {$creadas} cuota(s) nueva(s) para los alumnos con inscripción activa."
            : ' No se generaron cuotas nuevas: ya estaban todas generadas.';

        if ($actualizadas > 0) {
            $mensaje .= " Se actualizó el monto de {$actualizadas} cuota(s) impaga(s) al valor nuevo.";
        }

        return redirect()->route('director.configuracion-cuotas.index')->with('status', $mensaje);
    }

    // Carga un aumento: desde ese mes en adelante la cuota vale el monto nuevo.
    public function storeAumento(Request $request): RedirectResponse
    {
        $request->validate([
            'aumento_id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'aumento_id_carrera' => ['required', 'exists:carrera,id_carrera'],
        ]);

        // El aumento es de la configuración de ese año y esa carrera.
        $configuracion = ConfiguracionCuota::de($request->integer('aumento_id_anio_lectivo'), $request->integer('aumento_id_carrera'));

        if (! $configuracion) {
            throw ValidationException::withMessages(['aumento_id_carrera' => 'Primero guardá la configuración de cuotas de esa carrera para ese año lectivo.']);
        }

        return $this->guardarAumento($request, $configuracion->aumentos()->make());
    }

    // Edita el mes o el monto de un aumento ya cargado (botón del lápiz).
    public function updateAumento(Request $request, AumentoCuota $aumento): RedirectResponse
    {
        return $this->guardarAumento($request, $aumento);
    }

    // Valida y guarda un aumento (nuevo o editado) y recalcula las cuotas impagas.
    private function guardarAumento(Request $request, AumentoCuota $aumento): RedirectResponse
    {
        $data = $request->validate([
            'aumento_mes_desde' => ['required', 'integer', 'between:1,12'],
            'aumento_monto' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
        ], ['aumento_monto.max' => 'El monto no puede superar $1.000.000.']);

        $configuracion = $aumento->configuracionCuota()->with('aumentos', 'anioLectivo')->first();
        $mes = (int) $data['aumento_mes_desde'];
        $monto = round((float) $data['aumento_monto'], 2);

        // Los demás aumentos del año (sin contar el que se está editando).
        $otros = $configuracion->aumentos->where('id_aumento_cuota', '!=', $aumento->id_aumento_cuota);
        $anterior = $otros->where('mes_desde', '<', $mes)->last();   // aumento previo a este mes
        $siguiente = $otros->where('mes_desde', '>', $mes)->first(); // aumento posterior

        // Validación 1: el mes tiene que estar dentro del período, después del primer mes, y sin repetirse.
        if ($mes <= $configuracion->mes_desde || $mes > $configuracion->mes_hasta || $otros->contains('mes_desde', $mes)) {
            throw ValidationException::withMessages(['aumento_mes_desde' => 'Elegí un mes posterior a ' . Mes::find($configuracion->mes_desde)->nombre_mes
                . ', hasta ' . Mes::find($configuracion->mes_hasta)->nombre_mes . ', que no tenga ya otro aumento.']);
        }

        // Validación 2: tiene que ser un aumento: más que el precio anterior y menos que el aumento siguiente.
        $minimo = (float) ($anterior->monto ?? $configuracion->monto_mensual);
        if ($monto <= $minimo || ($siguiente && $monto >= (float) $siguiente->monto)) {
            throw ValidationException::withMessages(['aumento_monto' => 'El monto tiene que ser mayor a $' . number_format($minimo, 0, ',', '.')
                . ($siguiente ? ' y menor a $' . number_format((float) $siguiente->monto, 0, ',', '.') . ' (aumento siguiente)' : '') . '.']);
        }

        $aumento->fill(['mes_desde' => $mes, 'monto' => $monto])->save();
        $actualizadas = RecalculadorCuotas::aplicar($configuracion->load('aumentos'));

        return redirect()->route('director.configuracion-cuotas.index')
            ->with('status', 'Se guardó el aumento desde ' . Mes::find($mes)->nombre_mes . ". Se actualizaron {$actualizadas} cuota(s) impaga(s).");
    }

    // Borra un aumento: las cuotas impagas vuelven al precio anterior.
    public function destroyAumento(AumentoCuota $aumento): RedirectResponse
    {
        $configuracion = $aumento->configuracionCuota;
        $aumento->delete();
        $actualizadas = RecalculadorCuotas::aplicar($configuracion->load('aumentos', 'anioLectivo'));

        return redirect()->route('director.configuracion-cuotas.index')
            ->with('status', "Se eliminó el aumento. Se actualizaron {$actualizadas} cuota(s) impaga(s).");
    }

    // Si el año ya tiene aumentos, el monto base tiene que ser menor al
    // primero y el período tiene que incluir todos los meses con aumento.
    private function validarContraAumentos(array $data): void
    {
        $aumentos = ConfiguracionCuota::de($data['id_anio_lectivo'], $data['id_carrera'])?->aumentos;

        if ($aumentos?->isNotEmpty() && (
            (float) $data['monto_mensual'] >= (float) $aumentos->first()->monto
            || $data['mes_desde'] >= $aumentos->first()->mes_desde
            || $data['mes_hasta'] < $aumentos->last()->mes_desde
        )) {
            throw ValidationException::withMessages(['monto_mensual' => 'Este año ya tiene aumentos cargados: el monto tiene que ser menor al primer aumento '
                . 'y el período tiene que incluir los meses de los aumentos. Si no, eliminá los aumentos primero.']);
        }
    }
}
