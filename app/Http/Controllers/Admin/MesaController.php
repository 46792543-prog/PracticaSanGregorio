<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\MesaExamen;
use App\Models\Profesor;
use App\Models\TribunalMesa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MesaController extends Controller
{
    private const TURNOS = [
        'febrero_marzo' => 'Turno Febrero/Marzo',
        'julio' => 'Turno Julio',
        'noviembre_diciembre' => 'Turno Noviembre/Diciembre',
    ];

    public function index(Request $request): View
    {
        $turno = $request->query('turno', 'julio');

        $mesas = MesaExamen::with('materia.carrera', 'tribunal.profesor', 'inscripciones')
            ->where('turno', $turno)
            ->when($request->query('q'), fn ($q, $busqueda) => $q->whereHas('materia', fn ($w) => $w->where('nombre', 'like', "%{$busqueda}%")))
            ->orderBy('fecha_examen')
            ->get();

        return view('admin.mesas.index', [
            'mesas' => $mesas,
            'turno' => $turno,
            'turnos' => self::TURNOS,
            'busqueda' => $request->query('q'),
        ]);
    }

    public function create(): View
    {
        return view('admin.mesas.create', [
            'carreras' => Carrera::with(['materias' => fn ($q) => $q->orderBy('numero_orden')])->orderBy('nombre')->get(),
            'profesores' => Profesor::orderBy('apellido')->get(),
            'turnos' => self::TURNOS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'materia_id' => ['required', 'exists:materias,id'],
            'fecha_examen' => ['required', 'date'],
            'fecha_inicio_inscripcion' => ['required', 'date', 'before_or_equal:fecha_examen'],
            'fecha_fin_inscripcion' => ['required', 'date', 'after_or_equal:fecha_inicio_inscripcion', 'before_or_equal:fecha_examen'],
            'turno' => ['required', Rule::in(array_keys(self::TURNOS))],
            'llamado' => ['required', Rule::in(['primer_llamado', 'segundo_llamado'])],
            'presidente_id' => ['nullable', 'exists:profesores,id'],
            'vocal1_id' => ['nullable', 'exists:profesores,id'],
            'vocal2_id' => ['nullable', 'exists:profesores,id'],
        ]);

        $anioLectivo = AnioLectivo::orderByDesc('anio')->firstOrFail();

        $mesa = MesaExamen::create([
            'materia_id' => $data['materia_id'],
            'anio_lectivo_id' => $anioLectivo->id,
            'turno' => $data['turno'],
            'llamado' => $data['llamado'],
            'fecha_examen' => $data['fecha_examen'],
            'fecha_inicio_inscripcion' => $data['fecha_inicio_inscripcion'],
            'fecha_fin_inscripcion' => $data['fecha_fin_inscripcion'],
            'estado' => 'programada',
        ]);

        foreach (['presidente' => 'presidente_id', 'vocal1' => 'vocal1_id', 'vocal2' => 'vocal2_id'] as $rol => $campo) {
            if (! empty($data[$campo])) {
                TribunalMesa::create(['mesa_examen_id' => $mesa->id, 'profesor_id' => $data[$campo], 'rol' => $rol]);
            }
        }

        return redirect()->route('admin.mesas.index', ['turno' => $data['turno']])->with('status', 'Mesa de examen creada correctamente.');
    }

    public function destroy(MesaExamen $mesa): RedirectResponse
    {
        $mesa->update(['estado' => 'cancelada']);

        return back()->with('status', 'Mesa cancelada.');
    }
}
