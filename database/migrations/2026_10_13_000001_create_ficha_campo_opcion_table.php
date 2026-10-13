<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Normaliza ficha_campo: el array de opciones (JSON en una sola columna)
 * violaba 1FN al guardar varios valores en un mismo campo. Esta migración
 * lo separa en una tabla hija con una fila por opción.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ficha_campo_opcion', function (Blueprint $table) {
            $table->id('id_opcion');
            $table->foreignId('id_campo')->constrained('ficha_campo', 'id_campo')->cascadeOnDelete();
            $table->string('valor', 150);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });

        foreach (DB::table('ficha_campo')->whereNotNull('opciones')->get() as $campo) {
            foreach ((array) json_decode($campo->opciones, true) as $i => $valor) {
                DB::table('ficha_campo_opcion')->insert([
                    'id_campo' => $campo->id_campo,
                    'valor' => $valor,
                    'orden' => $i + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('ficha_campo', function (Blueprint $table) {
            $table->dropColumn('opciones');
        });
    }

    public function down(): void
    {
        Schema::table('ficha_campo', function (Blueprint $table) {
            $table->text('opciones')->nullable();
        });

        $agrupadas = DB::table('ficha_campo_opcion')->orderBy('orden')->get()->groupBy('id_campo');

        foreach ($agrupadas as $idCampo => $opciones) {
            DB::table('ficha_campo')->where('id_campo', $idCampo)->update([
                'opciones' => json_encode($opciones->pluck('valor')->all()),
            ]);
        }

        Schema::dropIfExists('ficha_campo_opcion');
    }
};
