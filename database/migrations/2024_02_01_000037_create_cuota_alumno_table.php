<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuota_alumno', function (Blueprint $table) {
            $table->id('id_cuota');
            $table->foreignId('id_persona_alumno')->constrained('persona', 'id_persona');
            $table->foreignId('id_anio_lectivo')->constrained('anio_lectivo', 'id_anio_lectivo');
            $table->foreignId('id_mes')->constrained('mes', 'id_mes');
            $table->decimal('monto', 10, 2);
            $table->boolean('pagado')->default(false);
            $table->foreignId('id_movimiento_caja')->nullable()->constrained('movimiento_caja', 'id_movimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuota_alumno');
    }
};
