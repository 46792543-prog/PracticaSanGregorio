<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('correlativa', function (Blueprint $table) {
            $table->boolean('requiere_regularizada')->default(false)->after('id_materia_requisito');
            $table->boolean('requiere_aprobada')->default(false)->after('requiere_regularizada');
        });

        // doctrine/dbal no está instalado, así que no se puede usar ->change();
        // se modifica la columna con SQL directo para permitirle NULL (el FK
        // sigue intacto, InnoDB acepta NULL en columnas con foreign key).
        DB::statement('ALTER TABLE correlativa MODIFY id_tipo_correlativa BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        Schema::table('correlativa', function (Blueprint $table) {
            $table->dropColumn(['requiere_regularizada', 'requiere_aprobada']);
        });
    }
};
