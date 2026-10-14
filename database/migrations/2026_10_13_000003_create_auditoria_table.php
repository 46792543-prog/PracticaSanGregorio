<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id('id_auditoria');
            $table->foreignId('id_usuario')->nullable()->constrained('usuario', 'id_usuario')->nullOnDelete();
            $table->string('accion', 20);
            $table->string('tabla', 60);
            $table->unsignedBigInteger('id_registro')->nullable();
            $table->string('descripcion', 255);
            $table->json('cambios')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tabla', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
