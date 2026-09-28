<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionInstitucion;
use App\Models\Persona;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class CertificadoController extends Controller
{
    public function regular(Persona $persona): Response
    {
        $persona->load(['inscripcionesCarrera' => fn ($q) => $q->with('carrera', 'anioLectivo', 'anioCursada', 'turnoCursada', 'estadoInscripcion')
            ->latest('id_inscripcion_carrera')]);

        $inscripcion = $persona->inscripcionesCarrera->first(fn ($i) => $i->estadoInscripcion->nombre_estado === 'Activo')
            ?? abort(422, 'El alumno no tiene una inscripción activa para certificar.');

        $pdf = Pdf::loadView('admin.alumnos.certificado-regular', [
            'alumno' => $persona,
            'inscripcion' => $inscripcion,
            'config' => ConfiguracionInstitucion::first(),
        ]);

        return $pdf->stream("certificado-regular-{$persona->dni}.pdf");
    }

    public function boletin(Persona $persona): Response
    {
        $persona->load([
            'inscripcionesCarrera' => fn ($q) => $q->with('carrera')->latest('id_inscripcion_carrera'),
            'historialAlumno' => fn ($q) => $q->with('materia.nombreMateria', 'condicion', 'anioLectivo')
                ->join('anio_lectivo', 'anio_lectivo.id_anio_lectivo', '=', 'historial_alumno.id_anio_lectivo')
                ->orderBy('anio_lectivo.anio')
                ->select('historial_alumno.*'),
        ]);

        $pdf = Pdf::loadView('admin.alumnos.boletin', [
            'alumno' => $persona,
            'config' => ConfiguracionInstitucion::first(),
        ]);

        return $pdf->stream("boletin-{$persona->dni}.pdf");
    }
}
