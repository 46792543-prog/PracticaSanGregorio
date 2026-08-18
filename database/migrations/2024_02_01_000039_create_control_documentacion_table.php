<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('control_documentacion', function (Blueprint $table) {
            $table->id('id_control');
            $table->foreignId('id_persona_alumno')->constrained('persona', 'id_persona');
            $table->foreignId('id_requisito')->constrained('documento_requisito', 'id_requisito');
            $table->date('fecha_entrega')->nullable();
            $table->foreignId('id_secretario_recibe')->nullable()->constrained('persona', 'id_persona');
            $table->foreignId('id_estado_documento')->constrained('estado_documento', 'id_estado_documento');
            $table->date('fecha_aprobacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_documentacion');
    }
};
