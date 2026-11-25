<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Se saca el monto fijo para recursantes de la configuración de cuotas:
 * lo que paga un recursante depende de cuántas materias cursa, así que el
 * director le genera las cuotas desde "Registrar Cuotas" con el monto que
 * corresponda a cada alumno.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('configuracion_cuota', 'monto_recursante')) {
            Schema::table('configuracion_cuota', function (Blueprint $table) {
                $table->dropColumn('monto_recursante');
            });
        }
    }

    public function down(): void
    {
        Schema::table('configuracion_cuota', function (Blueprint $table) {
            $table->decimal('monto_recursante', 10, 2)->nullable()->after('monto_mensual');
        });
    }
};
