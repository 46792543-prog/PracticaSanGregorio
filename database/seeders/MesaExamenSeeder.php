<?php

namespace Database\Seeders;

use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\InscripcionMesa;
use App\Models\Materia;
use App\Models\MesaExamen;
use App\Models\User;
use Illuminate\Database\Seeder;

class MesaExamenSeeder extends Seeder
{
    public function run(): void
    {
        $lucia = User::where('dni', '41205678')->firstOrFail();
        $carrera = Carrera::where('nombre', 'Tecnicatura Superior en Enfermería')->firstOrFail();
        $anioLectivo = AnioLectivo::where('anio', 2026)->firstOrFail();

        $materiaPorOrden = fn (int $orden) => Materia::where('carrera_id', $carrera->id)
            ->where('numero_orden', $orden)
            ->firstOrFail();

        // Mesa vigente: Lucía se inscribió a Enfermería Médica y Especialidades (regular) y está en revisión.
        $mesaEnfMedica = $this->crearMesa($materiaPorOrden(9), $anioLectivo, [
            'turno' => 'julio',
            'llamado' => 'primer_llamado',
            'fecha_examen' => '2026-07-12',
            'fecha_inicio_inscripcion' => '2026-06-01',
            'fecha_fin_inscripcion' => '2026-07-05',
            'estado' => 'programada',
        ]);
        $this->inscribir($mesaEnfMedica, $lucia, [
            'fecha_inscripcion' => '2026-06-18 10:00:00',
            'estado' => 'en_proceso',
        ]);

        // Mesa disponible para el resto de los alumnos: Lucía ya aprobó Microbiología por promoción.
        $this->crearMesa($materiaPorOrden(3), $anioLectivo, [
            'turno' => 'julio',
            'llamado' => 'primer_llamado',
            'fecha_examen' => '2026-07-15',
            'fecha_inicio_inscripcion' => '2026-06-01',
            'fecha_fin_inscripcion' => '2026-07-05',
            'estado' => 'programada',
        ]);

        // Mesa finalizada de Febrero/Marzo: inscripción de Lucía rechazada (documentación incompleta en su momento).
        $mesaMicrobiologiaFM = $this->crearMesa($materiaPorOrden(3), $anioLectivo, [
            'turno' => 'febrero_marzo',
            'llamado' => 'primer_llamado',
            'fecha_examen' => '2026-03-10',
            'fecha_inicio_inscripcion' => '2026-02-20',
            'fecha_fin_inscripcion' => '2026-03-05',
            'estado' => 'finalizada',
        ]);
        $this->inscribir($mesaMicrobiologiaFM, $lucia, [
            'fecha_inscripcion' => '2026-02-20 09:30:00',
            'estado' => 'rechazada',
        ]);

        // Mesa finalizada donde Lucía rindió y aprobó Informática Básica.
        $mesaInformatica = $this->crearMesa($materiaPorOrden(7), $anioLectivo, [
            'turno' => 'febrero_marzo',
            'llamado' => 'primer_llamado',
            'fecha_examen' => '2026-03-15',
            'fecha_inicio_inscripcion' => '2026-02-20',
            'fecha_fin_inscripcion' => '2026-03-10',
            'estado' => 'finalizada',
        ]);
        $this->inscribir($mesaInformatica, $lucia, [
            'fecha_inscripcion' => '2026-02-25 11:00:00',
            'estado' => 'aceptada',
            'nota_examen' => 8.00,
            'resultado' => 'aprobado',
        ]);

        // Mesa programada a la que Lucía todavía no puede anotarse: le falta aprobar
        // Enfermería Médica y Especialidades (sigue "regular", no "aprobada").
        $this->crearMesa($materiaPorOrden(18), $anioLectivo, [
            'turno' => 'noviembre_diciembre',
            'llamado' => 'primer_llamado',
            'fecha_examen' => '2026-11-14',
            'fecha_inicio_inscripcion' => '2026-10-20',
            'fecha_fin_inscripcion' => '2026-11-05',
            'estado' => 'programada',
        ]);
    }

    private function crearMesa(Materia $materia, AnioLectivo $anioLectivo, array $datos): MesaExamen
    {
        return MesaExamen::updateOrCreate(
            [
                'materia_id' => $materia->id,
                'anio_lectivo_id' => $anioLectivo->id,
                'turno' => $datos['turno'],
                'llamado' => $datos['llamado'],
            ],
            $datos
        );
    }

    private function inscribir(MesaExamen $mesa, User $alumno, array $datos): InscripcionMesa
    {
        return InscripcionMesa::updateOrCreate(
            ['mesa_examen_id' => $mesa->id, 'user_id' => $alumno->id],
            $datos
        );
    }
}
