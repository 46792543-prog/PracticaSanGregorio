<?php

namespace Tests\Feature;

use App\Models\MesaExamen;
use App\Models\Usuario;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    public function test_paginas_del_alumno_cargan_sin_error()
    {
        $usuario = Usuario::alumnos()
            ->whereHas('persona.inscripcionesCarrera.estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->firstOrFail();

        $this->actingAs($usuario);

        foreach ([
            '/panel',
            '/mi-estado-academico',
            '/mis-materias',
            '/mesas-examen',
            '/mis-inscripciones',
            '/mis-cuotas',
        ] as $ruta) {
            $this->get($ruta)->assertStatus(200);
        }
    }

    public function test_paginas_de_secretaria_cargan_sin_error()
    {
        $staff = Usuario::whereHas('rol', fn ($q) => $q->whereIn('nombre_rol', ['Secretario', 'Director']))->firstOrFail();
        $director = Usuario::whereHas('rol', fn ($q) => $q->where('nombre_rol', 'Director'))->first();
        $alumno = Usuario::alumnos()
            ->whereHas('persona.inscripcionesCarrera.estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->firstOrFail();
        $mesaProgramada = MesaExamen::whereHas('estadoMesa', fn ($q) => $q->where('nombre_estado', 'Programada'))->first();

        $this->actingAs($staff);

        $this->get('/admin/alumnos/' . $alumno->persona->id_persona)->assertStatus(200);
        $this->get('/admin/alumnos/' . $alumno->persona->id_persona . '/materias')->assertStatus(200);
        $this->get('/admin/mesas/nueva')->assertStatus(200);

        if ($mesaProgramada) {
            $this->get('/admin/mesas/' . $mesaProgramada->id_mesa . '/editar')->assertStatus(200);
        }

        if ($director) {
            $this->actingAs($director);
            $this->get('/director/cuotas?alumno=' . $alumno->id_usuario)->assertStatus(200);
        }
    }
}
