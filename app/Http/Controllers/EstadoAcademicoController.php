<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EstadoAcademicoController extends Controller
{
    public function index(Request $request): View
    {
        $alumno = Auth::user()->persona;
        $inscripcionCarrera = $alumno->inscripcionesCarrera()->with('carrera', 'anioCursada')->latest('id_inscripcion_carrera')->first();

        $busqueda = trim((string) $request->query('q'));
        $condicionFiltro = $request->query('condicion');

        $materias = $inscripcionCarrera
            ? $inscripcionCarrera->carrera->materias()
                ->with(['requisitos', 'nombreMateria', 'anioCursada', 'periodo', 'regimen', 'historial' => fn ($q) => $q->where('id_persona_alumno', $alumno->id_persona)])
                ->when($busqueda, fn ($q) => $q->whereHas('nombreMateria', fn ($n) => $n->where('nombre', 'like', "%{$busqueda}%")))
                ->orderBy('id_anio_cursada')
                ->get()
                ->sortBy('nombre')
            : collect();

        $materias = $materias->map(function ($materia) use ($alumno) {
            $historial = $materia->historial->first();
            $condicion = strtolower($historial?->condicion?->nombre_condicion ?? 'pendiente');
            $estadoVisual = $condicion;

            if ($condicion === 'pendiente' && $materia->correlativaFaltante($alumno)) {
                $estadoVisual = 'no_disponible';
            }

            $materia->condicion_alumno = $condicion;
            $materia->estado_visual = $estadoVisual;
            $materia->nota_alumno = $historial->nota_cursada ?? null;
            $materia->anio_cursada_num = $materia->anioCursada->id_anio_cursada ?? null;

            return $materia;
        });

        if ($condicionFiltro) {
            $materias = $materias->where('estado_visual', $condicionFiltro);
        }

        $materiasPorAnio = $materias->groupBy('anio_cursada_num')->sortKeys();
        $nombresAnio = [1 => '1er Año', 2 => '2do Año', 3 => '3er Año'];

        return view('estado-academico.index', [
            'inscripcionCarrera' => $inscripcionCarrera,
            'materiasPorAnio' => $materiasPorAnio,
            'nombresAnio' => $nombresAnio,
            'busqueda' => $busqueda,
            'condicionFiltro' => $condicionFiltro,
        ]);
    }
}
