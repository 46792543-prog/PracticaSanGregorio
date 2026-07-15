<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Acta;
use App\Models\Carrera;
use App\Models\DetalleActa;
use App\Models\MesaExamen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ActaController extends Controller
{
    public function index(Request $request): View
    {
        $mesas = MesaExamen::with('materia.carrera')
            ->whereIn('estado', ['finalizada', 'programada'])
            ->when($request->query('carrera'), fn ($q, $c) => $q->whereHas('materia', fn ($w) => $w->where('carrera_id', $c)))
            ->orderByDesc('fecha_examen')
            ->get();

        $mesaSeleccionada = null;
        $inscripcionesAceptadas = collect();
        $acta = null;

        if ($request->query('mesa')) {
            $mesaSeleccionada = MesaExamen::with('materia.carrera')->find($request->query('mesa'));
        }
        $mesaSeleccionada ??= $mesas->first();

        if ($mesaSeleccionada) {
            $inscripcionesAceptadas = $mesaSeleccionada->inscripciones()
                ->whereIn('estado', ['aceptada', 'en_proceso'])
                ->with('user')
                ->get();

            $acta = Acta::with('detalles')->where('mesa_examen_id', $mesaSeleccionada->id)->first();
        }

        return view('admin.actas.index', [
            'mesas' => $mesas,
            'carreras' => Carrera::orderBy('nombre')->get(),
            'mesaSeleccionada' => $mesaSeleccionada,
            'inscripcionesAceptadas' => $inscripcionesAceptadas,
            'acta' => $acta,
            'filtroCarrera' => $request->query('carrera'),
        ]);
    }

    public function guardar(Request $request, MesaExamen $mesa): RedirectResponse
    {
        $this->guardarActa($request, $mesa, 'borrador');

        return redirect()->route('admin.actas.index', ['mesa' => $mesa->id])->with('status', 'Borrador guardado.');
    }

    public function generar(Request $request, MesaExamen $mesa): RedirectResponse
    {
        $this->guardarActa($request, $mesa, 'generada');

        $mesa->update(['estado' => 'finalizada']);

        return redirect()->route('admin.actas.index', ['mesa' => $mesa->id])->with('status', 'Acta generada correctamente.');
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
            'notas.*.nota_promedio' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'notas.*.resultado' => ['nullable', 'in:aprobado,desaprobado,ausente'],
        ]);

        $acta = Acta::updateOrCreate(
            ['mesa_examen_id' => $mesa->id],
            [
                'libro' => $data['libro'] ?? null,
                'folio' => $data['folio'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'secretario_id' => Auth::id(),
                'estado' => $estado,
                'fecha_generacion' => $estado === 'generada' ? now() : null,
            ]
        );

        foreach ($data['notas'] ?? [] as $userId => $nota) {
            DetalleActa::updateOrCreate(
                ['acta_id' => $acta->id, 'user_id' => $userId],
                [
                    'nota_escrito' => $nota['nota_escrito'] ?? null,
                    'nota_oral' => $nota['nota_oral'] ?? null,
                    'nota_promedio' => $nota['nota_promedio'] ?? null,
                    'resultado' => $nota['resultado'] ?? null,
                ]
            );
        }
    }
}
