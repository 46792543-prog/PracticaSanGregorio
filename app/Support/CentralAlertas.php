<?php

namespace App\Support;

use App\Models\CuotaAlumno;
use App\Models\InscripcionCarrera;
use App\Models\Profesor;
use Illuminate\Support\Collection;

/**
 * Federa reglas de negocio ya existentes (cuotas vencidas, bajas recientes)
 * en una única lista de alertas activas para mostrar en el panel y el menú.
 */
class CentralAlertas
{
    private const DIAS_BAJA_RECIENTE = 7;

    /**
     * @param  bool  $paraDirector  Si es true, los enlaces de cada alerta apuntan a las
     *                              pantallas del Panel de Dirección; si es false, a sus
     *                              equivalentes accesibles desde el Panel de Secretaría.
     */
    public static function activas(bool $paraDirector = true): Collection
    {
        return collect()
            ->merge(self::alumnosConCuotasVencidas($paraDirector))
            ->merge(self::bajasAlumnoRecientes($paraDirector))
            ->merge(self::bajasProfesorRecientes($paraDirector))
            ->sortByDesc('fecha')
            ->values();
    }

    public static function cantidad(bool $paraDirector = true): int
    {
        return self::activas($paraDirector)->count();
    }

    private static function alumnosConCuotasVencidas(bool $paraDirector): Collection
    {
        return CuotaAlumno::where('pagado', false)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', now())
            ->with('personaAlumno')
            ->get()
            ->groupBy('id_persona_alumno')
            ->map(function (Collection $cuotas) use ($paraDirector) {
                $alumno = $cuotas->first()->personaAlumno;
                $monto = $cuotas->sum(fn (CuotaAlumno $c) => $c->montoTotal());
                $masAntigua = $cuotas->min('fecha_vencimiento');

                return [
                    'tipo' => 'cuota_vencida',
                    'severidad' => 'alta',
                    'titulo' => "Cuotas vencidas — {$alumno->apellido}, {$alumno->nombre}",
                    'descripcion' => $cuotas->count() . ' cuota(s) vencida(s), $' . number_format($monto, 0, ',', '.') . ' adeudado — vencida desde el ' . FechaEsp::corta($masAntigua),
                    'fecha' => $masAntigua,
                    'ruta' => $paraDirector
                        ? route('director.pagos.index', ['q' => $alumno->apellido])
                        : route('admin.alumnos.show', $alumno),
                ];
            })
            ->values();
    }

    private static function bajasAlumnoRecientes(bool $paraDirector): Collection
    {
        return InscripcionCarrera::whereNotNull('fecha_baja')
            ->where('fecha_baja', '>=', now()->subDays(self::DIAS_BAJA_RECIENTE))
            ->with('personaAlumno', 'carrera')
            ->get()
            ->map(fn (InscripcionCarrera $i) => [
                'tipo' => 'baja_alumno',
                'severidad' => 'media',
                'titulo' => "Baja de alumno — {$i->personaAlumno->apellido}, {$i->personaAlumno->nombre}",
                'descripcion' => "Dado de baja de {$i->carrera->nombre_carrera} el " . FechaEsp::corta($i->fecha_baja),
                'fecha' => $i->fecha_baja,
                'ruta' => $paraDirector
                    ? route('director.auditoria.index', ['tipo' => 'baja_alumno'])
                    : route('admin.alumnos.show', $i->personaAlumno),
            ]);
    }

    private static function bajasProfesorRecientes(bool $paraDirector): Collection
    {
        return Profesor::whereNotNull('fecha_baja')
            ->where('fecha_baja', '>=', now()->subDays(self::DIAS_BAJA_RECIENTE))
            ->with('persona')
            ->get()
            ->map(fn (Profesor $p) => [
                'tipo' => 'baja_profesor',
                'severidad' => 'media',
                'titulo' => "Baja de profesor — {$p->persona->apellido}, {$p->persona->nombre}",
                'descripcion' => 'Dado de baja el ' . FechaEsp::corta($p->fecha_baja),
                'fecha' => $p->fecha_baja,
                'ruta' => $paraDirector
                    ? route('director.auditoria.index', ['tipo' => 'baja_profesor'])
                    : route('admin.profesores.index'),
            ]);
    }
}
