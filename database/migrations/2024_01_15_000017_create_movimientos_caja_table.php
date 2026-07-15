<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['ingreso', 'gasto']);
            $table->string('concepto', 150);
            $table->decimal('monto', 10, 2);
            $table->dateTime('fecha_movimiento');
            $table->string('turno', 20)->nullable();
            $table->foreignId('registrado_por_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cuota_id')->nullable()->constrained('cuotas')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
    }
};
