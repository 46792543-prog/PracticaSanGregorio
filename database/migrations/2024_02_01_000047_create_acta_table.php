<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acta', function (Blueprint $table) {
            $table->id('id_acta');
            $table->string('libro', 20);
            $table->string('folio', 20);
            $table->foreignId('id_mesa')->unique()->constrained('mesa_examen', 'id_mesa');
            $table->foreignId('id_tipo_acta')->constrained('tipo_acta', 'id_tipo_acta');
            $table->timestamp('fecha_generacion')->nullable()->useCurrent();
            $table->foreignId('id_secretario_creador')->constrained('persona', 'id_persona');
            $table->foreignId('id_director_firmante')->nullable()->constrained('persona', 'id_persona');
            $table->unique(['libro', 'folio'], 'uq_acta_libro_folio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acta');
    }
};
