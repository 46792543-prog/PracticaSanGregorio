<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_institucion', function (Blueprint $table) {
            $table->id('id_configuracion');
            $table->string('nombre_institucion', 150);
            $table->string('direccion', 255)->nullable();
            $table->string('nombre_director', 150)->nullable();
            $table->string('telefono_contacto', 20)->nullable();
            $table->string('email_contacto', 100)->nullable();
            $table->dateTime('fecha_ultima_modificacion')->useCurrent();
            $table->foreignId('id_secretario_modifica')->nullable()->constrained('persona', 'id_persona');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_institucion');
    }
};
