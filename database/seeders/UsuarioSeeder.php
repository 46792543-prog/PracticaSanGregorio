<?php

namespace Database\Seeders;

use App\Models\EstadoUsuario;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Única persona+usuario semilla del sistema: el mínimo indispensable para
 * poder loguearse el primer día y cargar todo lo demás (carreras, alumnos,
 * profesores, etc.) desde la propia app. No es data de negocio de prueba.
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $persona = Persona::firstOrCreate(
            ['dni' => '00000001'],
            ['nombre' => 'Admin', 'apellido' => 'Sistema']
        );

        Usuario::firstOrCreate(
            ['id_persona' => $persona->id_persona],
            [
                'email' => 'direccion@sangregorio.edu.ar',
                'password' => Hash::make('CambiarAhora123'),
                'id_rol' => Rol::where('nombre_rol', 'Director')->value('id_rol'),
                'id_estado' => EstadoUsuario::where('nombre_estado', 'Activo')->value('id_estado'),
            ]
        );
    }
}
