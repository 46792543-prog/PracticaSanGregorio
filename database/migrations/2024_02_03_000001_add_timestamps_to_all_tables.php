<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tablas = [
        'persona', 'rol', 'estado_usuario', 'estado_carrera', 'anio_cursada',
        'periodo_dictado', 'regimen_aprobacion', 'nombre_materia', 'tipo_correlativa',
        'estado_documento', 'estado_anio_lectivo', 'estado_inscripcion', 'turno_cursada',
        'condicion_alumno', 'mes', 'estado_mesa', 'turno_examen', 'llamado_examen',
        'especialidad_profesor', 'rol_tribunal', 'tipo_acta', 'tipo_movimiento',
        'tipo_accion', 'institucion_origen', 'medio_pago', 'usuario', 'carrera',
        'profesor', 'anio_lectivo', 'concepto_caja', 'acciones', 'configuracion_institucion',
        'documento_requisito', 'materia', 'inscripcion_carrera', 'movimiento_caja',
        'cuota_alumno', 'historial_alumno', 'control_documentacion', 'correlativa',
        'asignacion_profesor_materia', 'mesa_examen', 'equivalencia', 'horario_asignacion',
        'inscripcion_mesa', 'tribunal_mesa', 'acta', 'detalle_acta',
    ];

    public function up(): void
    {
        foreach ($this->tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropTimestamps();
            });
        }
    }
};
