<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aumentos de la cuota mensual dentro de un año lectivo: "a partir de
 * julio la cuota pasa a $110.000". El monto de configuracion_cuota es el
 * valor base (desde mes_desde); cada aumento rige desde su mes en adelante.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aumento_cuota', function (Blueprint $table) {
            $table->id('id_aumento_cuota');
            $table->foreignId('id_configuracion_cuota')
                ->constrained('configuracion_cuota', 'id_configuracion_cuota')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('mes_desde');
            $table->decimal('monto', 10, 2);
            $table->timestamps();

            $table->unique(['id_configuracion_cuota', 'mes_desde']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aumento_cuota');
    }
};
