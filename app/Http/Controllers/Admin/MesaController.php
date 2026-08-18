<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\EstadoMesa;
use App\Models\LlamadoExamen;
use App\Models\MesaExamen;
use App\Models\Profesor;
use App\Models\RolTribunal;
use App\Models\TribunalMesa;
use App\Models\TurnoExamen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MesaController extends Controller
{
    public function index(Request $request): View
    {
        $turnos = TurnoExamen::orderBy('id_turno')->get();
        $turnoId = (int) $request->query('turno', $turnos->firstWhere('nombre_turno', 'Turno Julio')?->id_turno ?? $turnos->first()?->id_turno);

        $mesas = MesaExamen::with('materia.carrera', 'materia.nombreMateria', 'tribunal.profesor.persona', 'tribunal.rolTribunal', 'llamadoExamen', 'inscripciones', 'estadoMesa')
            ->where('id_turno', $turnoId)
            ->when($request->query('q'), fn ($q, $busqueda) => $q->whereHas('materia.nombreMateria', fn ($w) => $w->where('nombre', 'like', "%{$busqueda}%")))
            ->orderBy('fecha_examen')
            ->get();

        return view('admin.mesas.index', [
            'mesas' => $mesas,
            'turno' => $turnoId,
            'turnos' => $turnos,
            'busqueda' => $request->query('q'),
        ]);
    }

    public function create(): View
    {
        return view('admin.mesas.create', [
            'carreras' => Carrera::with(['materias.nombreMateria'])->orderBy('nombre_carrera')->get(),
            'profesores' => Profesor::with('persona')->get()->sortBy('apellido'),
            'turnos' => TurnoExamen::orderBy('id_turno')->get(),
            'llamados' => LlamadoExamen::orderBy('id_llamado')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_materia' => ['required', 'exists:materia,id_materia'],
            'fecha_examen' => ['required', 'date'],
            'fecha_inicio_inscripcion' => ['required', 'date', 'before_or_equal:fecha_examen'],
            'fecha_fin_inscripcion' => ['required', 'date', 'after_or_equal:fecha_inicio_inscripcion', 'before_or_equal:fecha_examen'],
            'id_turno' => ['required', 'exists:turno_examen,id_turno'],
            'id_llamado' => ['required', 'exists:llamado_examen,id_llamado'],
            'cupo_maximo' => ['nullable', 'integer', 'min:1'],
            'presidente_id' => ['nullable', 'exists:profesor,id_profesor'],
            'vocal1_id' => ['nullable', 'exists:profesor,id_profesor'],
            'vocal2_id' => ['nullable', 'exists:profesor,id_profesor'],
        ]);

        $anioLectivo = AnioLectivo::orderByDesc('anio')->firstOrFail();
        $estadoProgramada = EstadoMesa::where('nombre_estado', 'Programada')->firstOrFail();

        $mesa = MesaExamen::create([
            'id_materia' => $data['id_materia'],
            'id_anio_lectivo' => $anioLectivo->id_anio_lectivo,
            'id_turno' => $data['id_turno'],
            'id_llamado' => $data['id_llamado'],
            'fecha_examen' => $data['fecha_examen'],
            'fecha_inicio_inscripcion' => $data['fecha_inicio_inscripcion'],
            'fecha_fin_inscripcion' => $data['fecha_fin_inscripcion'],
            'id_estado_mesa' => $estadoProgramada->id_estado_mesa,
            'cupo_maximo' => $data['cupo_maximo'] ?? null,
        ]);

        $roles = RolTribunal::pluck('id_rol_tribunal', 'nombre_rol');

        foreach (['Presidente' => 'presidente_id', 'Vocal 1' => 'vocal1_id', 'Vocal 2' => 'vocal2_id'] as $rol => $campo) {
            if (! empty($data[$campo]) && $roles->has($rol)) {
                TribunalMesa::create([
                    'id_mesa' => $mesa->id_mesa,
                    'id_profesor' => $data[$campo],
                    'id_rol_tribunal' => $roles[$rol],
                ]);
            }
        }

        return redirect()->route('admin.mesas.index', ['turno' => $data['id_turno']])->with('status', 'Mesa de examen creada correctamente.');
    }

    public function destroy(MesaExamen $mesa): RedirectResponse
    {
        $estadoCancelada = EstadoMesa::where('nombre_estado', 'Cancelada')->firstOrFail();
        $mesa->update(['id_estado_mesa' => $estadoCancelada->id_estado_mesa]);

        return back()->with('status', 'Mesa cancelada.');
    }
}
