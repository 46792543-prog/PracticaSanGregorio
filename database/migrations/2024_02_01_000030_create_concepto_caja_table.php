<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('concepto_caja', function (Blueprint $table) {
            $table->id('id_concepto');
            $table->foreignId('id_tipo_movimiento')->constrained('tipo_movimiento', 'id_tipo_movimiento');
            $table->string('nombre_concepto', 100)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concepto_caja');
    }
};
