<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Support\GuiaAsistente;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class GuiaAsistenteTest extends TestCase
{
    private function usuarioConRol(string $rol): Usuario
    {
        return (new Usuario())->setRelation('rol', (object) ['nombre_rol' => $rol]);
    }

    private function panelesVisibles(string $rol): array
    {
        return collect(GuiaAsistente::paraUsuario($this->usuarioConRol($rol)))->pluck('panel')->unique()->values()->all();
    }

    public function test_cada_guia_esta_completa_y_apunta_a_una_ruta_real()
    {
        $catalogo = collect(GuiaAsistente::catalogo());

        $this->assertSame($catalogo->count(), $catalogo->pluck('id')->unique()->count(), 'Hay guías con id repetido.');

        foreach ($catalogo as $guia) {
            $this->assertTrue(Route::has($guia['ruta']), "La ruta {$guia['ruta']} no existe.");
            $this->assertNotEmpty($guia['pasos'], "La guía {$guia['id']} no tiene pasos.");
            $this->assertNotEmpty($guia['claves'], "La guía {$guia['id']} no tiene palabras clave.");
        }
    }

    public function test_cada_rol_ve_solo_las_guias_de_sus_paneles()
    {
        $this->assertSame(['Secretaría'], $this->panelesVisibles('Secretario'));
        $this->assertSame(['Secretaría', 'Dirección'], $this->panelesVisibles('Director'));
        $this->assertSame(['Secretaría', 'Dirección'], $this->panelesVisibles('Secretario Administrativo'));
        $this->assertSame([], $this->panelesVisibles('Alumno'));
        $this->assertSame([], GuiaAsistente::paraUsuario(null));
    }

    public function test_todas_las_pantallas_de_las_guias_abren_y_muestran_a_goyo()
    {
        $director = Usuario::whereHas('rol', fn ($q) => $q->where('nombre_rol', 'Director'))->firstOrFail();
        $this->actingAs($director);

        $urls = collect(GuiaAsistente::paraUsuario($director))->pluck('url')->unique();

        foreach ($urls as $url) {
            $this->get($url)->assertOk()->assertSee('sg-guia-boton', false);
        }
    }

    public function test_el_portal_del_alumno_no_muestra_a_goyo()
    {
        $alumno = Usuario::alumnos()
            ->whereHas('persona.inscripcionesCarrera.estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->firstOrFail();

        $this->actingAs($alumno)->get('/panel')->assertOk()->assertDontSee('sg-guia-boton', false);
    }
}
