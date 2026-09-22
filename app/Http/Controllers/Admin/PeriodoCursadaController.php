<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\PeriodoDictado;
use App\Models\PeriodoInscripcionCursada;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeriodoCursadaController extends Controller
{
    public function index(): View
    {
        $anioLectivo = AnioLectivo::whereHas('estadoAnio', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->orderByDesc('anio')
            ->first();

        $periodos = PeriodoDictado::orderBy('id_periodo')->get()->map(function (PeriodoDictado $periodo) use ($anioLectivo) {
            $periodo->habilitacion = $anioLectivo
                ? PeriodoInscripcionCursada::firstOrNew([
                    'id_anio_lectivo' => $anioLectivo->id_anio_lectivo,
                    'id_periodo' => $periodo->id_periodo,
                ])
                : null;

            return $periodo;
        });

        return view('admin.cursada.index', [
            'anioLectivo' => $anioLectivo,
            'periodos' => $periodos,
        ]);
    }

    public function toggle(Request $request, PeriodoDictado $periodo): RedirectResponse
    {
        $data = $request->validate([
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'abierto' => ['required', 'boolean'],
        ]);

        PeriodoInscripcionCursada::updateOrCreate(
            ['id_anio_lectivo' => $data['id_anio_lectivo'], 'id_periodo' => $periodo->id_periodo],
            ['abierto' => $data['abierto']]
        );

        return back()->with('status', $data['abierto']
            ? "Inscripción a cursada abierta para {$periodo->nombre_periodo}."
            : "Inscripción a cursada cerrada para {$periodo->nombre_periodo}.");
    }
}
