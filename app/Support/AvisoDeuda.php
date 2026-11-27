<?php

namespace App\Support;

use App\Models\CuotaAlumno;
use App\Models\Persona;

/**
 * Arma la plantilla del correo de aviso de cuotas vencidas de un alumno:
 * carrera(s), cantidad de cuotas, períodos adeudados e importe total.
 * Secretaría la puede editar antes de enviarla (nunca se envía sola).
 */
class AvisoDeuda
{
    public static function plantilla(Persona $alumno): array
    {
        $cuotas = CuotaAlumno::with('mes', 'carrera', 'anioLectivo')
            ->where('id_persona_alumno', $alumno->id_persona)
            ->where('pagado', false)
            ->whereNotNull('fecha_vencimiento')->where('fecha_vencimiento', '<', now())
            ->orderBy('id_anio_lectivo')->orderBy('id_mes')
            ->get();

        $email = trim((string) $alumno->usuario?->email);
        $total = $cuotas->sum(fn (CuotaAlumno $c) => $c->montoTotal());
        $pesos = fn ($n) => '$ ' . number_format($n, 0, ',', '.');

        // Un bloque por carrera: cuántas cuotas, qué meses y cuánto.
        $porCarrera = $cuotas->groupBy(fn ($c) => $c->carrera?->nombre_carrera ?? 'Sin carrera')
            ->map(fn ($cs, $carrera) => "Carrera: {$carrera}\n"
                . 'Cuotas pendientes: ' . $cs->count() . "\n"
                . 'Períodos adeudados: ' . $cs->map(fn ($c) => "{$c->mes?->nombre_mes} {$c->anioLectivo?->anio}")->join(', ') . "\n"
                . 'Importe: ' . $pesos($cs->sum(fn ($c) => $c->montoTotal())))
            ->join("\n\n");

        $mensaje = "Hola {$alumno->nombre} {$alumno->apellido}:\n\n"
            . "Te escribimos desde la Secretaría del Instituto Superior San Gregorio para informarte que registrás cuotas vencidas sin pagar.\n\n"
            . $porCarrera . "\n\n"
            . 'Importe total pendiente: ' . $pesos($total) . " (incluye el recargo por mora).\n\n"
            . "Te pedimos que regularices la situación a la brevedad, acercándote a Secretaría o comunicándote con nosotros. Si ya realizaste el pago, por favor desestimá este mensaje.\n\n"
            . "Saludos cordiales,\nSecretaría — Instituto Superior San Gregorio";

        return [
            'alumno' => "{$alumno->apellido}, {$alumno->nombre}",
            'email' => $email,
            'email_valido' => self::emailValido($email),
            'cantidad' => $cuotas->count(),
            'total' => $total,
            'asunto' => 'Cuotas pendientes de pago — Instituto Superior San Gregorio',
            'mensaje' => $mensaje,
        ];
    }

    public static function emailValido(?string $email): bool
    {
        return (bool) filter_var(trim((string) $email), FILTER_VALIDATE_EMAIL);
    }
}
