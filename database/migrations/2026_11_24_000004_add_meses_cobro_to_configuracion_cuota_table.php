<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Período de cobro de las cuotas de cada año lectivo: el director elige
 * desde qué mes y hasta qué mes se cobran (por defecto, marzo a diciembre).
 * Todos los alumnos deben desde mes_desde, sin importar el día en que se
 * los cargó en el sistema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_cuota', function (Blueprint $table) {
            $table->unsignedTinyInteger('mes_desde')->default(3)->after('monto_mensual');
            $table->unsignedTinyInteger('mes_hasta')->default(12)->after('mes_desde');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_cuota', function (Blueprint $table) {
            $table->dropColumn(['mes_desde', 'mes_hasta']);
        });
    }
};
