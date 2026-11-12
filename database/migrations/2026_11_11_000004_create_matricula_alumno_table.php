<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matricula_alumno', function (Blueprint $table) {
            $table->id('id_matricula');
            $table->foreignId('id_persona_alumno')->constrained('persona', 'id_persona');
            $table->foreignId('id_anio_lectivo')->constrained('anio_lectivo', 'id_anio_lectivo');
            $table->decimal('monto', 10, 2);
            $table->boolean('pagado')->default(false);
            $table->date('fecha_pago')->nullable();
            $table->foreignId('id_movimiento_caja')->nullable()->constrained('movimiento_caja', 'id_movimiento');
            $table->unique(['id_persona_alumno', 'id_anio_lectivo']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matricula_alumno');
    }
};
