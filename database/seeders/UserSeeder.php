<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['dni' => '41205678'],
            [
                'nombre' => 'Lucía',
                'apellido' => 'Fernández',
                'fecha_nacimiento' => '2001-03-15',
                'telefono' => '3884123456',
                'direccion' => 'Av. San Martín 245',
                'localidad' => 'San Salvador de Jujuy',
                'email' => 'lucia.fernandez@gmail.com',
                'password' => Hash::make('alumna123'),
                'rol' => 'alumno',
            ]
        );

        User::updateOrCreate(
            ['dni' => '28555444'],
            [
                'nombre' => 'María',
                'apellido' => 'Torres',
                'fecha_nacimiento' => '1985-07-02',
                'telefono' => '3884555222',
                'direccion' => 'Belgrano 456',
                'localidad' => 'San Salvador de Jujuy',
                'email' => 'secretaria@sangregorio.edu.ar',
                'password' => Hash::make('secretaria123'),
                'rol' => 'secretario',
            ]
        );

        User::updateOrCreate(
            ['dni' => '25444777'],
            [
                'nombre' => 'Roxana',
                'apellido' => 'Acosta',
                'fecha_nacimiento' => '1978-11-20',
                'telefono' => '3884777111',
                'direccion' => 'San Martín 1200',
                'localidad' => 'San Salvador de Jujuy',
                'email' => 'directora@sangregorio.edu.ar',
                'password' => Hash::make('directora123'),
                'rol' => 'director',
            ]
        );
    }
}
