<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Acta;
use App\Models\Carrera;
use App\Models\DetalleActa;
use App\Models\EstadoMesa;
use App\Models\MesaExamen;
use App\Models\TipoActa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ActaController extends Controller
{
    public function index(Request $request): View
    {
        $mesas = MesaExamen::with('materia.carrera', 'materia.nombreMateria', 'estadoMesa', 'llamadoExamen', 'turnoExamen', 'anioLectivo')
            ->whereHas('estadoMesa', fn ($q) => $q->whereIn('nombre_estado', ['Finalizada', 'Programada']))
            ->when($request->query('carrera'), fn ($q, $c) => $q->whereHas('materia', fn ($w) => $w->where('id_carrera', $c)))
            ->orderByDesc('fecha_examen')
            ->get();

        $mesaSeleccionada = null;
        $inscripcionesAceptadas = collect();
        $acta = null;

        if ($request->query('mesa')) {
            $mesaSeleccionada = MesaExamen::with('materia.carrera', 'materia.nombreMateria', 'turnoExamen', 'anioLectivo')->find($request->query('mesa'));
        }
        $mesaSeleccionada ??= $mesas->first();

        if ($mesaSeleccionada) {
            $inscripcionesAceptadas = $mesaSeleccionada->inscripciones()
                ->whereHas('estadoInscripcion', fn ($q) => $q->whereIn('nombre_estado', ['Aceptado', 'En proceso']))
                ->with('personaAlumno')
                ->get();

            $acta = Acta::with('detalles')->where('id_mesa', $mesaSeleccionada->id_mesa)->first();
        }

        return view('admin.actas.index', [
            'mesas' => $mesas,
            'carreras' => Carrera::orderBy('nombre_carrera')->get(),
            'mesaSeleccionada' => $mesaSeleccionada,
            'inscripcionesAceptadas' => $inscripcionesAceptadas,
            'acta' => $acta,
            'filtroCarrera' => $request->query('carrera'),
        ]);
    }

    public function guardar(Request $request, MesaExamen $mesa): RedirectResponse
    {
        $this->guardarActa($request, $mesa, 'borrador');

        return redirect()->route('admin.actas.index', ['mesa' => $mesa->id_mesa])->with('status', 'Borrador guardado.');
    }

    public function generar(Request $request, MesaExamen $mesa): RedirectResponse
    {
        $this->guardarActa($request, $mesa, 'generada');

        $estadoFinalizada = EstadoMesa::where('nombre_estado', 'Finalizada')->firstOrFail();
        $mesa->update(['id_estado_mesa' => $estadoFinalizada->id_estado_mesa]);

        return redirect()->route('admin.actas.index', ['mesa' => $mesa->id_mesa])->with('status', 'Acta generada correctamente.');
    }

    private function guardarActa(Request $request, MesaExamen $mesa, string $estado): void
    {
        $data = $request->validate([
            'libro' => ['nullable', 'string', 'max:20'],
            'folio' => ['nullable', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'notas' => ['array'],
            'notas.*.nota_escrito' => ['nullable', 'integer', 'between:1,10'],
            'notas.*.nota_oral' => ['nullable', 'integer', 'between:1,10'],
            'notas.*.nota_final' => ['nullable', 'integer', 'between:1,10'],
            'notas.*.resultado' => ['nullable', 'in:aprobado,desaprobado,ausente'],
        ]);

        $tipoActa = TipoActa::where('nombre_tipo', 'Acta de Examen Final')->firstOrFail();

        $acta = Acta::updateOrCreate(
            ['id_mesa' => $mesa->id_mesa],
            [
                'libro' => $data['libro'] ?? '',
                'folio' => $data['folio'] ?? '',
                'observaciones' => $data['observaciones'] ?? null,
                'id_tipo_acta' => $tipoActa->id_tipo_acta,
                'estado' => $estado,
                'id_secretario_creador' => Auth::user()->id_persona,
                'fecha_generacion' => $estado === 'generada' ? now() : null,
            ]
        );

        // Solo se aceptan notas de alumnos realmente inscriptos (Aceptado/En proceso) en
        // esta mesa: evita que un id_persona_alumno ajeno termine en el acta oficial.
        $alumnosInscriptos = $mesa->inscripciones()
            ->whereHas('estadoInscripcion', fn ($q) => $q->whereIn('nombre_estado', ['Aceptado', 'En proceso']))
            ->pluck('id_persona_alumno');

        foreach ($data['notas'] ?? [] as $personaId => $nota) {
            $notaEscrito = $nota['nota_escrito'] ?? null;
            $notaOral = $nota['nota_oral'] ?? null;
            $notaFinal = $nota['nota_final'] ?? null;
            $resultado = $nota['resultado'] ?? null;

            // Fila sin ningún dato cargado todavía (el secretario no llegó a ese alumno):
            // no se crea/actualiza el detalle de acta para no asentar nada de más.
            if ($notaEscrito === null && $notaOral === null && $notaFinal === null && $resultado === null) {
                continue;
            }

            if (! $alumnosInscriptos->contains($personaId)) {
                continue;
            }

            // nota_final y resultado son NOT NULL en la tabla: si falta alguno de los
            // dos, la fila queda incompleta y no se guarda (mejor que inventar un valor).
            if ($notaFinal === null || $resultado === null) {
                continue;
            }

            DetalleActa::updateOrCreate(
                ['id_acta' => $acta->id_acta, 'id_persona_alumno' => $personaId],
                [
                    'nota_escrito' => $notaEscrito,
                    'nota_oral' => $notaOral,
                    'nota_final' => $notaFinal,
                    'resultado' => $resultado,
                ]
            );
        }
    }
}
