<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configuración económica de cuotas por período lectivo: la carga el
 * Secretario Administrativo (único rol habilitado) y a partir de ella el
 * sistema genera automáticamente la cuota de cada alumno con inscripción
 * activa en ese año, mes por mes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_cuota', function (Blueprint $table) {
            $table->id('id_configuracion_cuota');
            $table->foreignId('id_anio_lectivo')->unique()->constrained('anio_lectivo', 'id_anio_lectivo');
            $table->decimal('monto_mensual', 10, 2);
            $table->unsignedTinyInteger('dia_vencimiento');
            $table->decimal('porcentaje_interes_mora', 5, 2)->default(0);
            $table->string('concepto', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_cuota');
    }
};
