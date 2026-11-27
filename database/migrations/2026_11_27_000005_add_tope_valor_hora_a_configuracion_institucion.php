<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Valor máximo que se puede cargar por hora cátedra (lo cambia Dirección en Liquidación de horas).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_institucion', function (Blueprint $table) {
            $table->decimal('tope_valor_hora_catedra', 10, 2)->default(80000);
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_institucion', function (Blueprint $table) {
            $table->dropColumn('tope_valor_hora_catedra');
        });
    }
};
