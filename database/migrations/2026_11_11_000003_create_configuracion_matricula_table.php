<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_matricula', function (Blueprint $table) {
            $table->id('id_configuracion_matricula');
            $table->foreignId('id_anio_lectivo')->unique()->constrained('anio_lectivo', 'id_anio_lectivo');
            $table->decimal('monto', 10, 2);
            $table->unsignedTinyInteger('mes_habilitacion');
            $table->unsignedTinyInteger('dia_limite');
            $table->string('concepto', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_matricula');
    }
};
