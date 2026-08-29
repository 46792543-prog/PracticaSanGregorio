<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Acta;
use App\Models\Carrera;
use App\Models\DetalleActa;
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

    public function guardar(Request $request, MesaExamen $mesa): RedirectResponse
    {
        $this->guardarActa($request, $mesa, 'borrador');

        return redirect()->route('admin.actas.index', ['mesa' => $mesa->id_mesa])->with('status', 'Borrador guardado.');
    }

    public function generar(Request $request, MesaExamen $mesa): RedirectResponse
    {
        $this->guardarActa($request, $mesa, 'generada');

        $mesa->update(['id_estado_mesa' => \App\Models\EstadoMesa::where('nombre_estado', 'Finalizada')->value('id_estado_mesa')]);

        return redirect()->route('admin.mesas.acta.pdf', $mesa);
    }

    private function guardarActa(Request $request, MesaExamen $mesa, string $estado): void
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
                'libro' => $data['libro'] ?? '',
                'folio' => $data['folio'] ?? '',
                'id_tipo_acta' => TipoActa::where('nombre_tipo', 'Acta de Examen Final')->value('id_tipo_acta'),
                'observaciones' => $data['observaciones'] ?? null,
                'id_secretario_creador' => Auth::user()->id_persona,
                'estado' => $estado,
                'fecha_generacion' => $estado === 'generada' ? now() : null,
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
        }
    }
}
