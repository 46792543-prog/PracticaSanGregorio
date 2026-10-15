<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\PeriodoDictado;
use App\Models\PeriodoInscripcionCursada;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeriodoCursadaController extends Controller
{
    public function index(Request $request): View
    {
        $anioLectivo = AnioLectivo::whereHas('estadoAnio', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->orderByDesc('anio')
            ->first();

        $carreras = Carrera::orderBy('nombre_carrera')->get();
        $carrera = $carreras->firstWhere('id_carrera', (int) $request->query('id_carrera')) ?? $carreras->first();

        $periodos = PeriodoDictado::orderBy('id_periodo')->get()->map(function (PeriodoDictado $periodo) use ($anioLectivo, $carrera) {
            $periodo->habilitacion = $anioLectivo && $carrera
                ? PeriodoInscripcionCursada::firstOrNew([
                    'id_anio_lectivo' => $anioLectivo->id_anio_lectivo,
                    'id_periodo' => $periodo->id_periodo,
                    'id_carrera' => $carrera->id_carrera,
                ])
                : null;

            return $periodo;
        });

        return view('admin.cursada.index', [
            'anioLectivo' => $anioLectivo,
            'carreras' => $carreras,
            'carrera' => $carrera,
            'periodos' => $periodos,
        ]);
    }

    public function toggle(Request $request, PeriodoDictado $periodo): RedirectResponse
    {
        $data = $request->validate([
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'id_carrera' => ['required', 'exists:carrera,id_carrera'],
            'abierto' => ['required', 'boolean'],
        ]);

        PeriodoInscripcionCursada::updateOrCreate(
            ['id_anio_lectivo' => $data['id_anio_lectivo'], 'id_periodo' => $periodo->id_periodo, 'id_carrera' => $data['id_carrera']],
            ['abierto' => $data['abierto']]
        );

        $carrera = Carrera::find($data['id_carrera'])->nombre_carrera;

        return redirect()->route('admin.cursada.index', ['id_carrera' => $data['id_carrera']])->with('status', $data['abierto']
            ? "Inscripción a cursada abierta para {$periodo->nombre_periodo} en {$carrera}."
            : "Inscripción a cursada cerrada para {$periodo->nombre_periodo} en {$carrera}.");
    }
}
