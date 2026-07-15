<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('anio_lectivo_id')->constrained('anios_lectivos')->cascadeOnDelete();
            $table->string('concepto', 100); // "Cuota Enero 2026", "Matrícula 2026", etc.
            $table->decimal('monto', 10, 2);
            $table->decimal('recargo', 10, 2)->default(0); // recargo por mora aplicado al momento del cobro
            $table->date('fecha_vencimiento');
            $table->enum('estado', ['pendiente', 'pagado', 'vencido'])->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->enum('medio_pago', ['efectivo', 'transferencia'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuotas');
    }
};
