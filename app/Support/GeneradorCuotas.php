<?php

namespace App\Support;

use App\Models\CondicionAlumno;
use App\Models\ConfiguracionCuota;
use App\Models\CuotaAlumno;
use App\Models\InscripcionCarrera;
use App\Models\TarifaRecursante;
use Illuminate\Support\Facades\DB;

/**
 * Genera la cuota de cada mes del período (mes_desde a mes_hasta) para los
 * alumnos activos de la carrera de la configuración. No duplica las que ya
 * existen. La usan Configuración de Cuotas y Cuotas de Recursantes.
 *
 *  - Regular: precio de Configuración de Cuotas (con sus aumentos).
 *  - Recursante: precio de su tabla, según cuántas materias cursa. Si no hay
 *    precio cargado para esa cantidad, no se le genera.
 *  - Cursada completa: no se le genera (paga solo el mes en que rinde, a mano).
 * También se usa para un alumno solo (paraAlumno), así el que se inscribe
 * después de guardada la configuración igual tiene sus cuotas.
 */
class GeneradorCuotas
{
    // Las cuotas que le falten a un alumno en sus carreras activas (con configuración cargada). Nunca duplica.
    public static function paraAlumno(?int $idPersona): int
    {
        if (! $idPersona) {
            return 0;
        }

        return InscripcionCarrera::where('id_persona_alumno', $idPersona)
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->get()
            ->sum(fn ($i) => ($config = ConfiguracionCuota::de($i->id_anio_lectivo, $i->id_carrera)) ? self::generar($config, $idPersona) : 0);
    }

    public static function generar(ConfiguracionCuota $configuracion, ?int $idPersona = null): int
    {
        $configuracion->loadMissing('anioLectivo', 'aumentos');
        $anio = $configuracion->anioLectivo;
        $creadas = 0;

        $inscripciones = InscripcionCarrera::with('condicion', 'personaAlumno')
            ->where('id_anio_lectivo', $anio->id_anio_lectivo)
            ->where('id_carrera', $configuracion->id_carrera)
            ->whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->when($idPersona, fn ($q) => $q->where('id_persona_alumno', $idPersona))
            ->get();

        DB::transaction(function () use ($inscripciones, $anio, $configuracion, &$creadas) {
            foreach ($inscripciones as $inscripcion) {
                $condicion = $inscripcion->condicion?->nombre_condicion;

                if ($condicion === CondicionAlumno::CURSADA_COMPLETA) {
                    continue;
                }

                $montoRecursante = $condicion === CondicionAlumno::RECURSANTE
                    ? TarifaRecursante::montoPara($inscripcion->personaAlumno, $anio->id_anio_lectivo, $configuracion->id_carrera)
                    : null;

                if ($condicion === CondicionAlumno::RECURSANTE && $montoRecursante === null) {
                    continue;
                }

                for ($mes = $configuracion->mes_desde; $mes <= $configuracion->mes_hasta; $mes++) {
                    $cuota = CuotaAlumno::firstOrNew([
                        'id_persona_alumno' => $inscripcion->id_persona_alumno,
                        'id_anio_lectivo' => $anio->id_anio_lectivo,
                        'id_carrera' => $configuracion->id_carrera,
                        'id_mes' => $mes,
                    ]);

                    if ($cuota->exists) {
                        continue;
                    }

                    $cuota->fill([
                        'concepto' => $configuracion->concepto,
                        'fecha_vencimiento' => ConfiguracionCuota::fechaVencimiento($anio->anio, $mes, $configuracion->dia_vencimiento),
                        'monto' => $montoRecursante ?? $configuracion->montoACobrar($mes),
                        'recargo' => 0,
                        'mora_aplicada' => false,
                        'pagado' => false,
                    ])->save();

                    $creadas++;
                }
            }
        });

        return $creadas;
    }
}
