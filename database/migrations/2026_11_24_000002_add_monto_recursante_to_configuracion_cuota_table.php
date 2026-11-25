<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Monto de la cuota para alumnos recursantes (cursan solo algunas
 * materias). Si queda vacío, el recursante paga el monto mensual completo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_cuota', function (Blueprint $table) {
            $table->decimal('monto_recursante', 10, 2)->nullable()->after('monto_mensual');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_cuota', function (Blueprint $table) {
            $table->dropColumn('monto_recursante');
        });
    }
};
