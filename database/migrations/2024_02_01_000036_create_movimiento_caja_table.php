<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimiento_caja', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->foreignId('id_concepto')->constrained('concepto_caja', 'id_concepto');
            $table->decimal('monto', 10, 2);
            $table->dateTime('fecha_movimiento')->nullable()->useCurrent();
            $table->string('descripcion_detalle', 250)->nullable();
            $table->foreignId('id_secretario_registra')->constrained('persona', 'id_persona');
            $table->foreignId('id_medio_pago')->default(1)->constrained('medio_pago', 'id_medio_pago');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento_caja');
    }
};
