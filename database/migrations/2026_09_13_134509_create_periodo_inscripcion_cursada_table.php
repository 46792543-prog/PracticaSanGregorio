<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('periodo_inscripcion_cursada', function (Blueprint $table) {
            $table->id('id_periodo_inscripcion');
            $table->foreignId('id_anio_lectivo')->constrained('anio_lectivo', 'id_anio_lectivo');
            $table->foreignId('id_periodo')->constrained('periodo_dictado', 'id_periodo');
            $table->boolean('abierto')->default(false);
            $table->unique(['id_anio_lectivo', 'id_periodo']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('periodo_inscripcion_cursada');
    }
};
