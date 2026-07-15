<?php

namespace Database\Seeders;

use App\Models\Acta;
use App\Models\Carrera;
use App\Models\DetalleActa;
use App\Models\InscripcionMesa;
use App\Models\Materia;
use App\Models\MesaExamen;
use App\Models\Profesor;
use App\Models\TribunalMesa;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActaSeeder extends Seeder
{
    public function run(): void
    {
        $carrera = Carrera::where('nombre', 'Tecnicatura Superior en Enfermería')->firstOrFail();
        $secretaria = User::where('rol', 'secretario')->firstOrFail();
        $lopez = Profesor::where('dni', '23111333')->firstOrFail();
        $fernandez = Profesor::where('dni', '21444555')->firstOrFail();

        $mesaPorOrdenYTurno = fn (int $orden, string $turno) => MesaExamen::whereHas(
            'materia',
            fn ($q) => $q->where('carrera_id', $carrera->id)->where('numero_orden', $orden)
        )->where('turno', $turno)->firstOrFail();

        // Tribunal de las mesas ya cargadas.
        $tribunales = [
            [$mesaPorOrdenYTurno(9, 'julio'), $fernandez, 'presidente'],
            [$mesaPorOrdenYTurno(3, 'julio'), $lopez, 'presidente'],
            [$mesaPorOrdenYTurno(3, 'febrero_marzo'), $lopez, 'presidente'],
            [$mesaPorOrdenYTurno(7, 'febrero_marzo'), $lopez, 'presidente'],
            [$mesaPorOrdenYTurno(7, 'febrero_marzo'), $fernandez, 'vocal1'],
        ];

        foreach ($tribunales as [$mesa, $profesor, $rol]) {
            TribunalMesa::updateOrCreate(
                ['mesa_examen_id' => $mesa->id, 'rol' => $rol],
                ['profesor_id' => $profesor->id]
            );
        }

        // Acta generada para la mesa finalizada de Informática Básica (Lucía aprobó).
        $mesaInformatica = $mesaPorOrdenYTurno(7, 'febrero_marzo');

        $acta = Acta::updateOrCreate(
            ['mesa_examen_id' => $mesaInformatica->id],
            [
                'libro' => '3',
                'folio' => '145',
                'fecha_generacion' => '2026-03-16 10:00:00',
                'secretario_id' => $secretaria->id,
                'estado' => 'generada',
            ]
        );

        $inscripcion = InscripcionMesa::where('mesa_examen_id', $mesaInformatica->id)
            ->where('estado', 'aceptada')
            ->first();

        if ($inscripcion) {
            DetalleActa::updateOrCreate(
                ['acta_id' => $acta->id, 'user_id' => $inscripcion->user_id],
                [
                    'nota_promedio' => $inscripcion->nota_examen,
                    'resultado' => $inscripcion->resultado,
                ]
            );
        }
    }
}
