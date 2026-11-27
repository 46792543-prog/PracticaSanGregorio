<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ficha del docente en pasos (como SIGEDOC):
 *  - Títulos pasan a ser un catálogo (nombre + nivel) que se elige de una
 *    lista; cada docente puede tener varios (tabla profesor_titulo).
 *  - Provincias y localidades: catálogos para el domicilio de la persona.
 */
return new class extends Migration
{
    private const PROVINCIAS = [
        'Buenos Aires', 'Catamarca', 'Chaco', 'Chubut', 'Ciudad Autónoma de Buenos Aires', 'Córdoba', 'Corrientes',
        'Entre Ríos', 'Formosa', 'Jujuy', 'La Pampa', 'La Rioja', 'Mendoza', 'Misiones', 'Neuquén', 'Río Negro',
        'Salta', 'San Juan', 'San Luis', 'Santa Cruz', 'Santa Fe', 'Santiago del Estero', 'Tierra del Fuego', 'Tucumán',
    ];

    public function up(): void
    {
        // Títulos: catálogo + relación con el docente (la tabla anterior estaba vacía).
        Schema::dropIfExists('titulo_profesor');

        Schema::create('titulo', function (Blueprint $table) {
            $table->id('id_titulo');
            $table->string('nombre', 150)->unique();
            $table->string('nivel', 20); // Secundario, Terciario, Universitario, Posgrado
            $table->timestamps();
        });

        Schema::create('profesor_titulo', function (Blueprint $table) {
            $table->foreignId('id_profesor')->constrained('profesor', 'id_profesor')->cascadeOnDelete();
            $table->foreignId('id_titulo')->constrained('titulo', 'id_titulo')->cascadeOnDelete();
            $table->primary(['id_profesor', 'id_titulo']);
        });

        // Provincias y localidades.
        Schema::create('provincia', function (Blueprint $table) {
            $table->id('id_provincia');
            $table->string('nombre', 60)->unique();
            $table->timestamps();
        });

        Schema::create('localidad', function (Blueprint $table) {
            $table->id('id_localidad');
            $table->foreignId('id_provincia')->constrained('provincia', 'id_provincia');
            $table->string('nombre', 80);
            $table->timestamps();
            $table->unique(['id_provincia', 'nombre']);
        });

        foreach (self::PROVINCIAS as $nombre) {
            DB::table('provincia')->insert(['nombre' => $nombre, 'created_at' => now(), 'updated_at' => now()]);
        }

        Schema::table('persona', function (Blueprint $table) {
            $table->foreignId('id_localidad')->nullable()->after('localidad')->constrained('localidad', 'id_localidad');
        });
    }

    public function down(): void
    {
        Schema::table('persona', fn (Blueprint $table) => $table->dropConstrainedForeignId('id_localidad'));
        Schema::dropIfExists('localidad');
        Schema::dropIfExists('provincia');
        Schema::dropIfExists('profesor_titulo');
        Schema::dropIfExists('titulo');

        Schema::create('titulo_profesor', function (Blueprint $table) {
            $table->id('id_titulo');
            $table->foreignId('id_profesor')->constrained('profesor', 'id_profesor')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->string('nivel', 20);
            $table->timestamps();
        });
    }
};
