<?php

namespace Database\Seeders;

use App\Models\FichaCampo;
use App\Models\FichaSeccion;
use Illuminate\Database\Seeder;

/**
 * Carga el punto de partida del formulario de inscripción configurable,
 * tomando como referencia la ficha en papel de la institución. El
 * secretario puede agregar, editar, ocultar o eliminar cualquiera de estos
 * campos después desde "Ficha de Inscripción" en el panel de Secretaría.
 */
class FichaInscripcionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seccion('Datos personales', 1, [
            ['etiqueta' => 'Fecha de nacimiento', 'tipo' => 'fecha'],
            ['etiqueta' => 'Lugar de nacimiento', 'tipo' => 'texto'],
            ['etiqueta' => 'Edad', 'tipo' => 'numero'],
            ['etiqueta' => 'Nacionalidad', 'tipo' => 'texto'],
            ['etiqueta' => 'Sexo', 'tipo' => 'seleccion', 'opciones' => ['Femenino', 'Masculino', 'Otro']],
            ['etiqueta' => 'Estado civil', 'tipo' => 'seleccion', 'opciones' => ['Soltero/a', 'Casado/a', 'Divorciado/a', 'Viudo/a', 'Unión convivencial']],
            ['etiqueta' => 'Teléfono fijo', 'tipo' => 'texto'],
            ['etiqueta' => 'Celular', 'tipo' => 'texto'],
            ['etiqueta' => 'Domicilio actual', 'tipo' => 'texto'],
            ['etiqueta' => 'Barrio', 'tipo' => 'texto'],
            ['etiqueta' => 'Ciudad', 'tipo' => 'texto'],
            ['etiqueta' => 'Departamento', 'tipo' => 'texto'],
        ]);

        $this->seccion('Datos de contacto', 2, [
            ['etiqueta' => 'Nombre y apellido del contacto', 'tipo' => 'texto'],
            ['etiqueta' => 'Teléfono', 'tipo' => 'texto'],
            ['etiqueta' => 'Correo', 'tipo' => 'texto'],
            ['etiqueta' => 'Parentesco', 'tipo' => 'texto'],
        ]);

        $this->seccion('Datos de salud', 3, [
            ['etiqueta' => 'Grupo sanguíneo', 'tipo' => 'texto'],
            ['etiqueta' => '¿Es alérgico?', 'tipo' => 'si_no'],
            ['etiqueta' => '¿A qué?', 'tipo' => 'texto_largo'],
            ['etiqueta' => '¿Dispone de obra social o seguro médico?', 'tipo' => 'si_no'],
            ['etiqueta' => '¿Padece alguna discapacidad o enfermedad?', 'tipo' => 'si_no'],
            ['etiqueta' => 'Especificar discapacidad o enfermedad', 'tipo' => 'texto_largo'],
            ['etiqueta' => '¿Necesita controles médicos periódicos?', 'tipo' => 'si_no'],
        ]);

        $this->seccion('Antecedentes académicos', 4, [
            ['etiqueta' => 'Colegio del que egresó', 'tipo' => 'texto'],
            ['etiqueta' => 'Ciudad/distrito', 'tipo' => 'texto'],
            ['etiqueta' => 'Departamento', 'tipo' => 'texto'],
            ['etiqueta' => 'Título obtenido', 'tipo' => 'texto'],
            ['etiqueta' => 'Año de egreso', 'tipo' => 'numero'],
            ['etiqueta' => 'Promedio de egreso', 'tipo' => 'numero'],
        ]);
    }

    private function seccion(string $nombre, int $orden, array $campos): void
    {
        $seccion = FichaSeccion::firstOrCreate(['nombre' => $nombre], ['orden' => $orden]);

        foreach ($campos as $i => $campo) {
            FichaCampo::firstOrCreate(
                ['id_seccion' => $seccion->id_seccion, 'etiqueta' => $campo['etiqueta']],
                [
                    'tipo' => $campo['tipo'],
                    'opciones' => $campo['opciones'] ?? null,
                    'obligatorio' => false,
                    'orden' => $i + 1,
                ]
            );
        }
    }
}
