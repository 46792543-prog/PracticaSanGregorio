<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\AsignacionProfesorMateria;
use App\Models\Carrera;
use App\Models\Profesor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfesorController extends Controller
{
    public function index(Request $request): View
    {
        $anioLectivo = AnioLectivo::orderByDesc('anio')->first();

        $asignaciones = AsignacionProfesorMateria::with('profesor', 'materia.carrera')
            ->when($request->query('profesor'), fn ($q, $p) => $q->where('profesor_id', $p))
            ->when($request->query('dia'), fn ($q, $dia) => $q->whereJsonContains('dias_cursada', $dia))
            ->when($request->query('materia'), fn ($q, $m) => $q->whereHas('materia', fn ($w) => $w->where('nombre', 'like', "%{$m}%")))
            ->orderByDesc('id')
            ->get();

        return view('admin.profesores.index', [
            'profesores' => Profesor::orderBy('apellido')->get(),
            'carreras' => Carrera::orderBy('nombre')->get(),
            'aniosLectivos' => AnioLectivo::orderByDesc('anio')->get(),
            'anioLectivo' => $anioLectivo,
            'asignaciones' => $asignaciones,
            'filtros' => $request->only('profesor', 'dia', 'materia'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'dni' => ['required', 'string', 'max:20', 'unique:profesores,dni'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'especialidad' => ['nullable', 'string', 'max:100'],
        ]);

        Profesor::create($data);

        return back()->with('status', 'Profesor agregado correctamente.');
    }

    public function storeAsignacion(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'profesor_id' => ['required', 'exists:profesores,id'],
            'materia_id' => ['required', 'exists:materias,id'],
            'anio_lectivo_id' => ['required', 'exists:anios_lectivos,id'],
            'hora_inicio' => ['required'],
            'hora_fin' => ['required', 'after:hora_inicio'],
            'aula' => ['nullable', 'string', 'max:20'],
            'turno' => ['nullable', 'string', 'max:20'],
            'dias' => ['required', 'array', 'min:1'],
        ]);

        AsignacionProfesorMateria::updateOrCreate(
            [
                'profesor_id' => $data['profesor_id'],
                'materia_id' => $data['materia_id'],
                'anio_lectivo_id' => $data['anio_lectivo_id'],
            ],
            [
                'hora_inicio' => $data['hora_inicio'],
                'hora_fin' => $data['hora_fin'],
                'aula' => $data['aula'] ?? null,
                'turno' => $data['turno'] ?? null,
                'dias_cursada' => $data['dias'],
            ]
        );

        return back()->with('status', 'Asignación guardada correctamente.');
    }

    public function destroyAsignacion(AsignacionProfesorMateria $asignacion): RedirectResponse
    {
        $asignacion->delete();

        return back()->with('status', 'Asignación eliminada.');
    }
}
