<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\AsignacionProfesorMateria;
use App\Models\Carrera;
use App\Models\EspecialidadProfesor;
use App\Models\HorarioAsignacion;
use App\Models\Persona;
use App\Models\Profesor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfesorController extends Controller
{
    public function index(Request $request): View
    {
        $anioLectivo = AnioLectivo::orderByDesc('anio')->first();

        $asignaciones = AsignacionProfesorMateria::with('profesor.persona', 'materia.carrera', 'materia.nombreMateria', 'horarios')
            ->when($request->query('profesor'), fn ($q, $p) => $q->where('id_profesor', $p))
            ->when($request->query('dia'), fn ($q, $dia) => $q->whereHas('horarios', fn ($w) => $w->where('dia_semana', ucfirst($dia))))
            ->when($request->query('materia'), fn ($q, $m) => $q->whereHas('materia.nombreMateria', fn ($w) => $w->where('nombre', 'like', "%{$m}%")))
            ->orderByDesc('id_asignacion')
            ->get();

        return view('admin.profesores.index', [
            'profesores' => Profesor::with('persona', 'especialidad')->get()->sortBy('apellido'),
            'carreras' => Carrera::orderBy('nombre_carrera')->get(),
            'aniosLectivos' => AnioLectivo::orderByDesc('anio')->get(),
            'anioLectivo' => $anioLectivo,
            'asignaciones' => $asignaciones,
            'especialidades' => EspecialidadProfesor::orderBy('nombre_especialidad')->get(),
            'filtros' => $request->only('profesor', 'dia', 'materia'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'dni' => ['required', 'digits:8', 'unique:persona,dni'],
            'nombre' => ['required', 'string', 'max:50', 'regex:/^[\pL\s\'-]+$/u'],
            'apellido' => ['required', 'string', 'max:50', 'regex:/^[\pL\s\'-]+$/u'],
            'email' => ['nullable', 'email', 'max:100', 'unique:profesor,email'],
            'id_especialidad' => ['required', 'exists:especialidad_profesor,id_especialidad'],
        ]);

        $persona = Persona::create([
            'dni' => $data['dni'],
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
        ]);

        Profesor::create([
            'id_persona' => $persona->id_persona,
            'email' => $data['email'] ?? null,
            'id_especialidad' => $data['id_especialidad'],
        ]);

        return back()->with('status', 'Profesor agregado correctamente.');
    }

    public function storeAsignacion(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_profesor' => ['required', 'exists:profesor,id_profesor'],
            'id_materia' => ['required', 'exists:materia,id_materia'],
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'aula' => ['nullable', 'string', 'max:20'],
            'hora_inicio' => ['required'],
            'hora_fin' => ['required', 'after:hora_inicio'],
            'dias' => ['required', 'array', 'min:1'],
            'dias.*' => ['in:Lunes,Martes,Miercoles,Jueves,Viernes'],
        ]);

        $asignacion = AsignacionProfesorMateria::updateOrCreate(
            [
                'id_profesor' => $data['id_profesor'],
                'id_materia' => $data['id_materia'],
                'id_anio_lectivo' => $data['id_anio_lectivo'],
            ],
            [
                'fecha_asignacion' => now(),
                'aula' => $data['aula'] ?? null,
            ]
        );

        $asignacion->horarios()->delete();

        foreach ($data['dias'] as $dia) {
            HorarioAsignacion::create([
                'id_asignacion' => $asignacion->id_asignacion,
                'dia_semana' => $dia,
                'hora_desde' => $data['hora_inicio'],
                'hora_fin' => $data['hora_fin'],
            ]);
        }

        return back()->with('status', 'Asignación guardada correctamente.');
    }

    public function destroyAsignacion(AsignacionProfesorMateria $asignacion): RedirectResponse
    {
        $asignacion->delete();

        return back()->with('status', 'Asignación eliminada.');
    }
}
