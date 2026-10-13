<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_docente', function (Blueprint $table) {
            $table->id('id_pago_docente');
            $table->foreignId('id_profesor')->constrained('profesor', 'id_profesor');
            $table->date('periodo');
            $table->date('fecha_pago');
            $table->decimal('monto', 10, 2);
            $table->foreignId('id_metodo_pago_docente')->constrained('metodo_pago_docente', 'id_metodo_pago_docente');
            $table->foreignId('id_estado_pago_docente')->constrained('estado_pago_docente', 'id_estado_pago_docente');
            $table->string('observaciones', 255)->nullable();
            $table->foreignId('id_director_registra')->nullable()->constrained('persona', 'id_persona');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_docente');
    }
};
