<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioCursada;
use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\PeriodoDictado;
use App\Models\PeriodoInscripcionCursada;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class PeriodoCursadaController extends Controller
{
    private const SESSION_KEY = 'cursada_anio_preferido';

    public function index(Request $request): View
    {
        $anioLectivo = AnioLectivo::whereHas('estadoAnio', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->orderByDesc('anio')
            ->first();

        $carreras = Carrera::orderBy('nombre_carrera')->get();
        $carrera = $carreras->firstWhere('id_carrera', (int) $request->query('id_carrera')) ?? $carreras->first();
        $aniosCursada = AnioCursada::orderBy('id_anio_cursada')->get();
        $anioCursadaSeleccionada = $aniosCursada->firstWhere('id_anio_cursada', (int) $request->query('id_anio_cursada')) ?? $aniosCursada->first();

        $materias = $carrera
            ? Materia::where('id_carrera', $carrera->id_carrera)
                ->where('activa', true)
                ->with('nombreMateria')
                ->get()
            : collect();

        $preferencias = Session::get(self::SESSION_KEY, []);

        $periodos = PeriodoDictado::orderBy('id_periodo')->get()->map(function (PeriodoDictado $periodo) use ($anioLectivo, $carrera, $aniosCursada, $materias, $anioCursadaSeleccionada, $preferencias) {
            $idPreferido = $preferencias[$periodo->id_periodo] ?? null;

            // Cada tarjeta recuerda el año que se le guardó explícitamente (botón
            // "Guardar"); si todavía no se guardó ninguno, usa el que vino por la
            // URL (ej. después de abrir/cerrar un período).
            $periodo->anioCursadaPorDefecto = ($idPreferido ? $aniosCursada->firstWhere('id_anio_cursada', $idPreferido) : null)
                ?? $anioCursadaSeleccionada;

            $periodo->habilitacionesPorAnio = $aniosCursada->map(fn (AnioCursada $anioCursada) => [
                'anioCursada' => $anioCursada,
                'habilitacion' => $anioLectivo && $carrera
                    ? PeriodoInscripcionCursada::firstOrNew([
                        'id_anio_lectivo' => $anioLectivo->id_anio_lectivo,
                        'id_periodo' => $periodo->id_periodo,
                        'id_carrera' => $carrera->id_carrera,
                        'id_anio_cursada' => $anioCursada->id_anio_cursada,
                    ])
                    : null,
                'materias' => $materias->where('id_periodo', $periodo->id_periodo)
                    ->where('id_anio_cursada', $anioCursada->id_anio_cursada)
                    ->sortBy('numero_orden')
                    ->values(),
            ]);

            return $periodo;
        });

        return view('admin.cursada.index', [
            'anioLectivo' => $anioLectivo,
            'carreras' => $carreras,
            'carrera' => $carrera,
            'periodos' => $periodos,
            'anioCursadaSeleccionada' => $anioCursadaSeleccionada,
        ]);
    }

    public function toggle(Request $request, PeriodoDictado $periodo): RedirectResponse
    {
        $data = $request->validate([
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'id_carrera' => ['required', 'exists:carrera,id_carrera'],
            'id_anio_cursada' => ['required', 'exists:anio_cursada,id_anio_cursada'],
            'abierto' => ['required', 'boolean'],
        ]);

        PeriodoInscripcionCursada::updateOrCreate(
            [
                'id_anio_lectivo' => $data['id_anio_lectivo'],
                'id_periodo' => $periodo->id_periodo,
                'id_carrera' => $data['id_carrera'],
                'id_anio_cursada' => $data['id_anio_cursada'],
            ],
            ['abierto' => $data['abierto']]
        );

        $carrera = Carrera::find($data['id_carrera'])->nombre_carrera;
        $anioCursada = AnioCursada::find($data['id_anio_cursada'])->nombre_anio;

        return redirect()->route('admin.cursada.index', ['id_carrera' => $data['id_carrera'], 'id_anio_cursada' => $data['id_anio_cursada']])->with('status', $data['abierto']
            ? "Inscripción a cursada abierta para {$periodo->nombre_periodo} ({$anioCursada}) en {$carrera}."
            : "Inscripción a cursada cerrada para {$periodo->nombre_periodo} ({$anioCursada}) en {$carrera}.");
    }

    public function guardarPreferenciaAnio(Request $request, PeriodoDictado $periodo): RedirectResponse
    {
        $data = $request->validate([
            'id_carrera' => ['required', 'exists:carrera,id_carrera'],
            'id_anio_cursada' => ['required', 'exists:anio_cursada,id_anio_cursada'],
        ]);

        $preferencias = Session::get(self::SESSION_KEY, []);
        $preferencias[$periodo->id_periodo] = (int) $data['id_anio_cursada'];
        Session::put(self::SESSION_KEY, $preferencias);

        $anioCursada = AnioCursada::find($data['id_anio_cursada'])->nombre_anio;

        return redirect()->route('admin.cursada.index', ['id_carrera' => $data['id_carrera'], 'id_anio_cursada' => $data['id_anio_cursada']])
            ->with('status', "La tarjeta de {$periodo->nombre_periodo} va a mostrar {$anioCursada} de ahora en más.");
    }
}
