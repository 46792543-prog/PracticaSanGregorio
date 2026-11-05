<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\HorarioCelda;
use App\Models\HorarioModulo;
use App\Models\TurnoCursada;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HorarioCarreraTest extends TestCase
{
    use DatabaseTransactions;

    private Carrera $carrera;
    private TurnoCursada $turno;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(Usuario::whereHas('rol', fn ($q) => $q->whereIn('nombre_rol', ['Secretario', 'Director']))->firstOrFail());

        $this->carrera = Carrera::firstOrFail();
        $this->turno = TurnoCursada::firstOrFail();

        HorarioModulo::where('id_carrera', $this->carrera->id_carrera)
            ->where('id_turno_cursada', $this->turno->id_turno_cursada)
            ->delete();
    }

    private function crearModulo(int $numero, string $inicio, string $fin): HorarioModulo
    {
        return HorarioModulo::create([
            'id_carrera' => $this->carrera->id_carrera,
            'id_turno_cursada' => $this->turno->id_turno_cursada,
            'modulo' => $numero,
            'hora_inicio' => $inicio,
            'hora_fin' => $fin,
        ]);
    }

    private function datosModulo(array $cambios = []): array
    {
        return $cambios + [
            'id_carrera' => $this->carrera->id_carrera,
            'id_turno_cursada' => $this->turno->id_turno_cursada,
            'modulo' => 5,
            'hora_inicio' => '10:00',
            'hora_fin' => '11:00',
        ];
    }

    public function test_descarga_el_horario_en_pdf()
    {
        $modulo = $this->crearModulo(1, '08:00', '09:00');
        HorarioCelda::create(['id_horario_modulo' => $modulo->id_horario_modulo, 'dia_semana' => 'Miércoles', 'contenido' => 'ANATOMÍA']);

        $respuesta = $this->get(route('admin.horarios.pdf', [
            'id_carrera' => $this->carrera->id_carrera,
            'id_turno_cursada' => $this->turno->id_turno_cursada,
        ]));

        $respuesta->assertOk();
        $respuesta->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment', $respuesta->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $respuesta->getContent());
    }

    public function test_pdf_de_carrera_inexistente_devuelve_404()
    {
        $this->get(route('admin.horarios.pdf', ['id_carrera' => 999999, 'id_turno_cursada' => $this->turno->id_turno_cursada]))
            ->assertNotFound();
    }

    public function test_el_boton_de_pdf_solo_aparece_si_hay_modulos()
    {
        $filtros = ['id_carrera' => $this->carrera->id_carrera, 'id_turno_cursada' => $this->turno->id_turno_cursada];

        $this->get(route('admin.horarios.index', $filtros))->assertDontSee('Descargar PDF');

        $this->crearModulo(1, '08:00', '09:00');

        $this->get(route('admin.horarios.index', $filtros))->assertSee('Descargar PDF');
    }

    public function test_rechaza_un_modulo_superpuesto()
    {
        $this->crearModulo(1, '08:00', '09:00');

        $this->post(route('admin.horarios.modulos.store'), $this->datosModulo(['hora_inicio' => '08:30', 'hora_fin' => '09:30']))
            ->assertSessionHasErrors(['hora_inicio' => 'El horario se superpone con el módulo 1° (08:00 a 09:00).']);

        $this->assertSame(1, HorarioModulo::where('id_carrera', $this->carrera->id_carrera)->where('id_turno_cursada', $this->turno->id_turno_cursada)->count());
    }

    public function test_acepta_modulos_contiguos()
    {
        $this->crearModulo(1, '08:00', '09:00');

        $this->post(route('admin.horarios.modulos.store'), $this->datosModulo(['hora_inicio' => '09:00', 'hora_fin' => '10:00']))
            ->assertSessionHasNoErrors();
    }

    public function test_editar_un_modulo_no_choca_consigo_mismo_pero_si_con_otros()
    {
        $primero = $this->crearModulo(1, '08:00', '09:00');
        $this->crearModulo(2, '09:00', '10:00');

        $this->put(route('admin.horarios.modulos.update', $primero), ['modulo' => 1, 'hora_inicio' => '08:15', 'hora_fin' => '09:00'])
            ->assertSessionHasNoErrors();

        $this->put(route('admin.horarios.modulos.update', $primero), ['modulo' => 1, 'hora_inicio' => '08:15', 'hora_fin' => '09:30'])
            ->assertSessionHasErrors('hora_inicio');
    }

    public function test_los_mensajes_de_validacion_estan_en_castellano()
    {
        $this->post(route('admin.horarios.modulos.store'), $this->datosModulo(['modulo' => null, 'hora_inicio' => '11:00', 'hora_fin' => '10:00']))
            ->assertSessionHasErrors([
                'modulo' => 'Ingresá el número de módulo.',
                'hora_fin' => 'La hora de fin debe ser posterior a la hora de inicio.',
            ]);
    }

    public function test_al_fallar_se_reabre_el_modal_con_lo_cargado()
    {
        $this->crearModulo(1, '08:00', '09:00');
        $url = route('admin.horarios.index', ['id_carrera' => $this->carrera->id_carrera, 'id_turno_cursada' => $this->turno->id_turno_cursada]);

        $this->from($url)
            ->followingRedirects()
            ->post(route('admin.horarios.modulos.store'), $this->datosModulo(['modulo' => 7, 'hora_inicio' => '08:30', 'hora_fin' => '09:30']))
            ->assertSee('const moduloRechazado', false)
            ->assertSee('08:30', false)
            ->assertSee('El horario se superpone');
    }
}
