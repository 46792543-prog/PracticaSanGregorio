<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Valor de la hora cátedra según el tipo de designación (Titular, Interino,
 * Suplente, Provisional), con vigencia: "desde julio 2026 la hora cátedra
 * del titular vale $1.100". Con esto se liquida el pago de cada docente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valor_hora_catedra', function (Blueprint $table) {
            $table->id('id_valor_hora_catedra');
            $table->enum('tipo', ['Titular', 'Interino', 'Suplente', 'Provisional']);
            $table->decimal('monto', 10, 2);
            $table->date('vigente_desde'); // primer día del mes desde el que rige
            $table->timestamps();

            $table->unique(['tipo', 'vigente_desde']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valor_hora_catedra');
    }
};
