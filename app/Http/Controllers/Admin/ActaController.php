<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Acta;
use App\Models\Carrera;
use App\Models\CondicionAlumno;
use App\Models\DetalleActa;
use App\Models\HistorialAlumno;
use App\Models\MesaExamen;
use App\Models\TipoActa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\View\View;

class ActaController extends Controller
{
    public function index(Request $request): View
    {
        $mesas = MesaExamen::with('materia.carrera', 'tribunal.profesor.persona', 'tribunal.rolTribunal')
            ->whereHas('estadoMesa', fn ($q) => $q->whereIn('nombre_estado', ['Finalizada', 'Programada']))
            ->when($request->query('carrera'), fn ($q, $c) => $q->whereHas('materia', fn ($w) => $w->where('id_carrera', $c)))
            ->orderByDesc('fecha_examen')
            ->get();

        $mesaSeleccionada = null;
        $inscripcionesAceptadas = collect();
        $acta = null;

        if ($request->query('mesa')) {
            $mesaSeleccionada = MesaExamen::with('materia.carrera', 'tribunal.profesor.persona', 'tribunal.rolTribunal')->find($request->query('mesa'));
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

    public function show(MesaExamen $mesa): View
    {
        return view('admin.actas.show', $this->cargarDatosMesa($mesa));
    }

    public function pdf(MesaExamen $mesa): Response
    {
        $pdf = Pdf::loadView('admin.actas.pdf', $this->cargarDatosMesa($mesa))->setPaper('a4');

        return $pdf->stream("acta-{$mesa->id_mesa}.pdf");
    }

    private function cargarDatosMesa(MesaExamen $mesa): array
    {
        $mesa->load('materia.carrera', 'turnoExamen', 'llamadoExamen', 'anioLectivo', 'tribunal.profesor.persona', 'tribunal.rolTribunal');

        $inscripcionesAceptadas = $mesa->inscripciones()
            ->whereHas('estadoInscripcion', fn ($q) => $q->whereIn('nombre_estado', ['Aceptado', 'En proceso']))
            ->with('personaAlumno')
            ->get();

        $acta = Acta::with('detalles')->where('id_mesa', $mesa->id_mesa)->first();

        return [
            'mesa' => $mesa,
            'inscripcionesAceptadas' => $inscripcionesAceptadas,
            'acta' => $acta,
            'tribunalPorRol' => $mesa->tribunal->keyBy(fn ($t) => $t->rolTribunal->nombre_rol ?? ''),
        ];
    }

    /**
     * Única forma de guardar un acta: la cierra de una — ya no existe un
     * paso intermedio de "borrador" (generaba confusión con dos botones).
     * Para imprimirla está el botón aparte "Imprimir acta", que no depende
     * de esto.
     */
    public function cerrar(Request $request, MesaExamen $mesa): RedirectResponse
    {
        $this->guardarActa($request, $mesa);

        $mesa->update(['id_estado_mesa' => \App\Models\EstadoMesa::where('nombre_estado', 'Finalizada')->value('id_estado_mesa')]);

        return redirect()->route('admin.actas.index', ['mesa' => $mesa->id_mesa])->with('status', 'Acta cerrada correctamente.');
    }

    private function guardarActa(Request $request, MesaExamen $mesa): void
    {
        $data = $request->validate([
            'libro' => ['nullable', 'string', 'max:20'],
            'folio' => ['nullable', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
            'notas' => ['array'],
            'notas.*.nota_escrito' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'notas.*.nota_oral' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'notas.*.nota_final' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'notas.*.resultado' => ['nullable', 'in:aprobado,desaprobado,ausente'],
        ]);

        $acta = Acta::updateOrCreate(
            ['id_mesa' => $mesa->id_mesa],
            [
                'libro' => $data['libro'] ?? null,
                'folio' => $data['folio'] ?? null,
                'id_tipo_acta' => TipoActa::where('nombre_tipo', 'Acta de Examen Final')->value('id_tipo_acta'),
                'observaciones' => $data['observaciones'] ?? null,
                'id_secretario_creador' => Auth::user()->id_persona,
                'estado' => 'generada',
                'fecha_generacion' => now(),
            ]
        );

        foreach ($data['notas'] ?? [] as $idPersonaAlumno => $nota) {
            DetalleActa::updateOrCreate(
                ['id_acta' => $acta->id_acta, 'id_persona_alumno' => $idPersonaAlumno],
                [
                    'nota_escrito' => $nota['nota_escrito'] ?? null,
                    'nota_oral' => $nota['nota_oral'] ?? null,
                    'nota_final' => $nota['nota_final'] ?? 0,
                    'resultado' => $nota['resultado'] ?? '',
                ]
            );

            $this->sincronizarHistorial($mesa, (int) $idPersonaAlumno, $nota['resultado'] ?? null, $nota['nota_final'] ?? null);
        }
    }

    /**
     * El resultado del acta pasa a ser la condición oficial de la materia en
     * el historial del alumno (lo que ve en su perfil) — antes quedaba solo
     * en el acta y nunca se reflejaba ahí. "Ausente" no cambia nada: no
     * rindió, sigue en condiciones de anotarse a otra mesa.
     */
    private function sincronizarHistorial(MesaExamen $mesa, int $idPersonaAlumno, ?string $resultado, mixed $notaFinal): void
    {
        if (! in_array($resultado, ['aprobado', 'desaprobado'], true)) {
            return;
        }

        $historial = HistorialAlumno::where('id_persona_alumno', $idPersonaAlumno)
            ->where('id_materia', $mesa->id_materia)
            ->latest('id_anio_lectivo')
            ->first();

        if (! $historial) {
            return;
        }

        $idCondicion = CondicionAlumno::where('nombre_condicion', $resultado === 'aprobado' ? 'Examen Final' : 'Final Desaprobado')
            ->value('id_condicion');

        $historial->update([
            'id_condicion' => $idCondicion,
            'nota_cursada' => $notaFinal !== null && $notaFinal !== '' ? max(1, min(10, (int) round((float) $notaFinal))) : $historial->nota_cursada,
            'fecha_ultima_modificacion' => $mesa->fecha_examen,
        ]);
    }
}
