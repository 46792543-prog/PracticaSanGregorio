<?php

namespace App\Support;

use App\Models\CondicionAlumno;
use App\Models\HistorialAlumno;
use App\Models\InscripcionCarrera;
use App\Models\PlanEstudio;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Pasa a un alumno de su plan de estudios a otro plan de la misma carrera
 * (ej. cuando su plan caduca). Lo usan el botón de la ficha (uno por uno) y
 * "Pasar alumnos" en Planes (todos juntos).
 *
 * Cada materia del plan viejo se busca en el nuevo por su nombre: si existe,
 * su historial se copia al plan nuevo con la misma condición y nota. El
 * historial viejo no se borra. Si está cursando alguna materia, primero hay
 * que cerrar esa cursada.
 */
class CambioDePlan
{
    // Motivo por el que NO se lo puede pasar, o null si se puede.
    public static function impedimento(InscripcionCarrera $inscripcion, PlanEstudio $destino): ?string
    {
        if ($destino->id_carrera !== $inscripcion->id_carrera || $destino->id_plan_estudio === $inscripcion->id_plan_estudio) {
            return 'El plan destino tiene que ser otro plan de la misma carrera.';
        }

        if ($destino->estado() !== 'Vigente') {
            return "El {$destino->nombre} no está vigente.";
        }

        $cursando = self::historialDelPlan($inscripcion)
            ->filter(fn ($h) => in_array($h->condicion?->nombre_condicion, CondicionAlumno::EN_CURSO, true));

        return $cursando->isNotEmpty()
            ? 'Está cursando ' . $cursando->pluck('materia.nombre')->join(', ') . ': primero hay que cerrar esa cursada.'
            : null;
    }

    // Vista previa: qué pasa con cada materia. [materia vieja, condición, materia nueva (o null)] + las nuevas sin equivalente.
    public static function vistaPrevia(InscripcionCarrera $inscripcion, PlanEstudio $destino): array
    {
        $materiasNuevas = $destino->materias()->with('nombreMateria')->get()->keyBy('id_nombre_materia');

        $filas = self::historialDelPlan($inscripcion)
            ->groupBy('id_materia')
            ->map(function (Collection $registros) use ($materiasNuevas) {
                $ultimo = $registros->sortBy(fn ($h) => $h->anioLectivo?->anio)->last();

                return [
                    'materia' => $ultimo->materia,
                    'condicion' => $ultimo->condicion?->nombre_condicion,
                    'nota' => $ultimo->nota_cursada,
                    'destino' => $materiasNuevas->get($ultimo->materia->id_nombre_materia),
                ];
            })
            ->values();

        $conHistorial = $filas->pluck('materia.id_nombre_materia');

        return [
            'filas' => $filas,
            'nuevas' => $materiasNuevas->reject(fn ($m) => $conHistorial->contains($m->id_nombre_materia))->values(),
        ];
    }

    // Hace el cambio. Devuelve cuántas materias se le reconocieron en el plan nuevo.
    public static function aplicar(InscripcionCarrera $inscripcion, PlanEstudio $destino): int
    {
        $materiasNuevas = $destino->materias()->get()->keyBy('id_nombre_materia');
        $planAnterior = $inscripcion->plan?->nombre;

        return DB::transaction(function () use ($inscripcion, $destino, $materiasNuevas, $planAnterior) {
            $copiadas = 0;

            foreach (self::historialDelPlan($inscripcion) as $h) {
                $nueva = $materiasNuevas->get($h->materia->id_nombre_materia);

                if (! $nueva) {
                    continue; // la materia ya no existe en el plan nuevo: queda solo en el historial viejo
                }

                $registro = HistorialAlumno::firstOrNew([
                    'id_persona_alumno' => $h->id_persona_alumno,
                    'id_materia' => $nueva->id_materia,
                    'id_anio_lectivo' => $h->id_anio_lectivo,
                ]);

                if (! $registro->exists) {
                    $registro->fill($h->only([
                        'id_condicion', 'nota_cursada', 'veces_rendida', 'institucion_equivalencia',
                        'resolucion_equivalencia', 'fecha_ultima_modificacion', 'anios_plazo_regularidad',
                    ]))->save();
                    $copiadas++;
                }
            }

            // Queda en Auditoría con el motivo.
            Auditor::conMotivo("Cambio de plan: {$planAnterior} → {$destino->nombre}");
            $inscripcion->update(['id_plan_estudio' => $destino->id_plan_estudio]);

            return $copiadas;
        });
    }

    // Historial del alumno en las materias de su plan actual.
    private static function historialDelPlan(InscripcionCarrera $inscripcion): Collection
    {
        return HistorialAlumno::with('materia.nombreMateria', 'condicion', 'anioLectivo')
            ->where('id_persona_alumno', $inscripcion->id_persona_alumno)
            ->whereIn('id_materia', $inscripcion->materiasDelPlan()->pluck('id_materia'))
            ->get();
    }
}
