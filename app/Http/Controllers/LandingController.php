<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\ConfiguracionInstitucion;
use App\Models\ImagenLanding;
use App\Models\InscripcionCarrera;
use App\Models\Materia;
use App\Models\Profesor;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $config = ConfiguracionInstitucion::first();

        // Todas las carreras activas, con su plan de materias agrupado por
        // año. Esto sale 100% de lo que ya se carga en Admin > Carreras: si
        // se agrega una carrera nueva (o una materia nueva), aparece acá
        // automáticamente, sin tocar nada de la landing ni de Configuración.
        $carreras = Carrera::whereHas('estadoCarrera', fn ($q) => $q->where('nombre_estado', 'Activa'))
            ->orderBy('id_carrera')
            ->get();

        $planesPorCarrera = $carreras->mapWithKeys(fn (Carrera $c) => [
            $c->id_carrera => Materia::where('id_carrera', $c->id_carrera)
                ->where('activa', true)
                ->with('nombreMateria', 'anioCursada')
                ->orderBy('id_anio_cursada')
                ->orderBy('numero_orden')
                ->get()
                ->groupBy(fn (Materia $m) => $m->anioCursada?->nombre_anio ?? 'Sin año asignado'),
        ]);

        $carreraPrincipal = $carreras->first();

        $alumnosActivos = InscripcionCarrera::whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->distinct('id_persona_alumno')->count('id_persona_alumno');
        $docentesActivos = Profesor::whereNull('fecha_baja')->count();

        return view('landing.index', [
            'config' => $config,
            'carreraPrincipal' => $carreraPrincipal,
            'carreras' => $carreras,
            'planesPorCarrera' => $planesPorCarrera,
            'imagenesHero' => ImagenLanding::orderBy('orden')->orderBy('id_imagen')->get(),
            // Los 4 contadores se pueden pisar desde Configuración (panel de
            // Dirección); si no se cargó un valor ahí, se usa el dato real
            // del sistema (o un valor de ejemplo para "egresados", que el
            // sistema no registra como entidad propia).
            'contadores' => [
                ['valor' => $config?->landing_anios_formacion ?? $carreraPrincipal?->duracion_anos ?? 3, 'sufijo' => '', 'etiqueta' => 'Años de formación'],
                ['valor' => $config?->landing_docentes_cantidad ?? $docentesActivos, 'sufijo' => '+', 'etiqueta' => 'Docentes activos'],
                ['valor' => $config?->landing_alumnos_cantidad ?? $alumnosActivos, 'sufijo' => '+', 'etiqueta' => 'Alumnos cursando'],
                ['valor' => $config?->landing_egresados_cantidad ?? 120, 'sufijo' => '+', 'etiqueta' => 'Egresados'],
            ],
            'novedades' => [
                ['icono' => 'practica', 'etiqueta' => 'Formación práctica', 'titulo' => 'Prácticas en el hospital', 'texto' => 'Nuestros estudiantes de 2° y 3° año realizan prácticas supervisadas en centros de salud de la región.'],
                ['icono' => 'comunidad', 'etiqueta' => 'Compromiso social', 'titulo' => 'Jornada de salud comunitaria', 'texto' => 'Alumnos y docentes participan de jornadas de prevención y control de salud en barrios de San Pedro de Jujuy.'],
                ['icono' => 'institucional', 'etiqueta' => 'Vida institucional', 'titulo' => 'Acto de colación', 'texto' => 'Celebramos a la nueva camada de Técnicos Superiores en Enfermería egresados del instituto.'],
            ],
            'servicios' => [
                ['icono' => 'campus', 'titulo' => 'Campus virtual', 'texto' => 'Material de estudio, calificaciones y mesas de examen, disponible las 24 horas.'],
                ['icono' => 'inscripcion', 'titulo' => 'Inscripciones online', 'texto' => 'Ingreso y reinscripción sin moverte de tu casa, con seguimiento del trámite.'],
                ['icono' => 'practicas', 'titulo' => 'Prácticas profesionales', 'texto' => 'Convenios con hospitales y centros de salud para tu formación práctica desde 1° año.'],
                ['icono' => 'biblioteca', 'titulo' => 'Biblioteca', 'texto' => 'Material bibliográfico actualizado para consulta y préstamo a domicilio.'],
                ['icono' => 'tutorias', 'titulo' => 'Tutorías', 'texto' => 'Acompañamiento académico personalizado durante toda la carrera.'],
                ['icono' => 'examenes', 'titulo' => 'Mesas de examen', 'texto' => 'Fechas, requisitos y resultados siempre al día desde el Campus Virtual.'],
                ['icono' => 'simulacion', 'titulo' => 'Laboratorio de simulación', 'texto' => 'Practicá procedimientos de enfermería en un entorno controlado antes de llegar al hospital.'],
                ['icono' => 'becas', 'titulo' => 'Becas y ayuda económica', 'texto' => 'Acompañamos a quienes necesitan apoyo para sostener sus estudios.'],
            ],
        ]);
    }
}
