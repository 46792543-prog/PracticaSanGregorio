<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\EstadoAnioLectivo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnioLectivoController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'anio' => ['required', 'integer', 'digits:4', 'min:2000', 'max:2100', 'unique:anio_lectivo,anio'],
        ]);

        AnioLectivo::create([
            'anio' => $data['anio'],
            'id_estado_anio' => EstadoAnioLectivo::where('nombre_estado', 'Activo')->value('id_estado_anio'),
        ]);

        return redirect()->route('director.configuracion.index')
            ->with('status', "Se cargó el corte {$data['anio']} correctamente.");
    }

    public function actualizarEstado(AnioLectivo $anio): RedirectResponse
    {
        $nuevoEstado = $anio->estadoAnio->nombre_estado === 'Activo' ? 'Cerrado' : 'Activo';

        $anio->update([
            'id_estado_anio' => EstadoAnioLectivo::where('nombre_estado', $nuevoEstado)->value('id_estado_anio'),
        ]);

        return redirect()->route('director.configuracion.index')
            ->with('status', "El corte {$anio->anio} ahora está {$nuevoEstado}.");
    }

    public function destroy(AnioLectivo $anio): RedirectResponse
    {
        $enUso = $anio->cuotas()->exists()
            || $anio->inscripcionesCarrera()->exists()
            || $anio->historialAlumno()->exists()
            || $anio->mesasExamen()->exists()
            || $anio->asignaciones()->exists();

        abort_if($enUso, 403, 'No se puede eliminar un corte que ya tiene datos cargados.');

        $anio->delete();

        return redirect()->route('director.configuracion.index')
            ->with('status', "Se eliminó el corte {$anio->anio}.");
    }
}
