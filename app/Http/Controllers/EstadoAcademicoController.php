<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EstadoAcademicoController extends Controller
{
    public function index(Request $request): View
    {
        $alumno = Auth::user();
        $inscripcionCarrera = $alumno->inscripcionesCarrera()->with('carrera')->latest()->first();

        $busqueda = trim((string) $request->query('q'));
        $condicionFiltro = $request->query('condicion');

        $materias = $inscripcionCarrera
            ? $inscripcionCarrera->carrera->materias()
                ->with(['requisitos', 'historial' => fn ($q) => $q->where('user_id', $alumno->id)])
                ->when($busqueda, fn ($q) => $q->where('nombre', 'like', "%{$busqueda}%"))
                ->orderBy('anio_cursada')
                ->orderBy('nombre')
                ->get()
            : collect();

        $materias = $materias->map(function ($materia) use ($alumno) {
            $historial = $materia->historial->first();
            $condicion = $historial->condicion ?? 'pendiente';
            $estadoVisual = $condicion;

            if ($condicion === 'pendiente' && $materia->correlativaFaltante($alumno)) {
                $estadoVisual = 'no_disponible';
            }

            $materia->condicion_alumno = $condicion;
            $materia->estado_visual = $estadoVisual;
            $materia->nota_alumno = $historial->nota_cursada ?? null;

            return $materia;
        });

        if ($condicionFiltro) {
            $materias = $materias->where('estado_visual', $condicionFiltro);
        }

        $materiasPorAnio = $materias->groupBy('anio_cursada')->sortKeys();
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
