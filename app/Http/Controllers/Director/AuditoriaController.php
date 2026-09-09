<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\Acta;
use App\Models\ConfiguracionInstitucion;
use App\Models\ControlDocumentacion;
use App\Models\InscripcionCarrera;
use App\Models\InscripcionMesa;
use App\Models\MovimientoCaja;
use App\Models\Persona;
use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * No existe una tabla central de auditoría: este panel arma una línea de
 * tiempo de "quién hizo qué" federando las columnas id_secretario y
 * id_director que ya guardan cada módulo (inscripciones, caja,
 * documentación, mesas, actas, configuración).
 */
class AuditoriaController extends Controller
{
    private const TIPOS = [
        'inscripcion' => 'Alta de alumno',
        'baja_alumno' => 'Baja de alumno',
        'caja' => 'Movimiento de caja',
        'documentacion' => 'Documentación recibida',
        'baja_mesa' => 'Baja de inscripción a mesa',
        'acta' => 'Acta de examen',
        'configuracion' => 'Configuración institucional',
        'baja_profesor' => 'Baja de profesor',
        'reactivacion_profesor' => 'Reactivación de profesor',
    ];

    public function index(Request $request): View
    {
        $tipo = $request->query('tipo');
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $eventos = collect()
            ->when(! $tipo || $tipo === 'inscripcion', fn (Collection $c) => $c->merge($this->altasAlumno()))
            ->when(! $tipo || $tipo === 'baja_alumno', fn (Collection $c) => $c->merge($this->bajasAlumno()))
            ->when(! $tipo || $tipo === 'caja', fn (Collection $c) => $c->merge($this->movimientosCaja()))
            ->when(! $tipo || $tipo === 'documentacion', fn (Collection $c) => $c->merge($this->documentacionRecibida()))
            ->when(! $tipo || $tipo === 'baja_mesa', fn (Collection $c) => $c->merge($this->bajasMesa()))
            ->when(! $tipo || $tipo === 'acta', fn (Collection $c) => $c->merge($this->actas()))
            ->when(! $tipo || $tipo === 'configuracion', fn (Collection $c) => $c->merge($this->configuracion()))
            ->when(! $tipo || $tipo === 'baja_profesor', fn (Collection $c) => $c->merge($this->bajasProfesor()))
            ->when(! $tipo || $tipo === 'reactivacion_profesor', fn (Collection $c) => $c->merge($this->reactivacionesProfesor()));

        if ($desde) {
            $inicio = Carbon::parse($desde)->startOfDay();
            $eventos = $eventos->filter(fn (array $e) => $e['fecha'] && $e['fecha']->gte($inicio));
        }
        if ($hasta) {
            $fin = Carbon::parse($hasta)->endOfDay();
            $eventos = $eventos->filter(fn (array $e) => $e['fecha'] && $e['fecha']->lte($fin));
        }

        $eventos = $eventos->sortByDesc('fecha')->values();

        $porPagina = 20;
        $pagina = (int) $request->query('page', 1);
        $paginado = new LengthAwarePaginator(
            $eventos->forPage($pagina, $porPagina),
            $eventos->count(),
            $porPagina,
            $pagina,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('director.auditoria.index', [
            'eventos' => $paginado,
            'tipos' => self::TIPOS,
            'filtros' => ['tipo' => $tipo, 'desde' => $desde, 'hasta' => $hasta],
        ]);
    }

    private function evento(string $tipo, ?Carbon $fecha, ?Persona $actor, string $descripcion): array
    {
        return [
            'tipo' => $tipo,
            'tipoLabel' => self::TIPOS[$tipo],
            'fecha' => $fecha,
            'actor' => $actor,
            'descripcion' => $descripcion,
        ];
    }

    private function altasAlumno(): Collection
    {
        return InscripcionCarrera::with(['personaAlumno', 'carrera', 'secretarioRegistra.usuario'])
            ->whereNotNull('fecha_inscripcion')
            ->get()
            ->map(fn (InscripcionCarrera $i) => $this->evento(
                'inscripcion',
                $i->fecha_inscripcion,
                $i->secretarioRegistra,
                "Inscribió a {$i->personaAlumno?->apellido}, {$i->personaAlumno?->nombre} en {$i->carrera?->nombre_carrera}"
            ));
    }

    private function bajasAlumno(): Collection
    {
        return InscripcionCarrera::with(['personaAlumno', 'carrera', 'secretarioBaja.usuario'])
            ->whereNotNull('fecha_baja')
            ->get()
            ->map(fn (InscripcionCarrera $i) => $this->evento(
                'baja_alumno',
                $i->fecha_baja,
                $i->secretarioBaja,
                "Dio de baja a {$i->personaAlumno?->apellido}, {$i->personaAlumno?->nombre} de {$i->carrera?->nombre_carrera}"
            ));
    }

    private function movimientosCaja(): Collection
    {
        return MovimientoCaja::with(['concepto.tipoMovimiento', 'secretarioRegistra.usuario'])
            ->get()
            ->map(fn (MovimientoCaja $m) => $this->evento(
                'caja',
                $m->fecha_movimiento,
                $m->secretarioRegistra,
                "Registró un {$m->tipo} de \$" . number_format((float) $m->monto, 0, ',', '.') . " — {$m->concepto?->nombre_concepto}"
            ));
    }

    private function documentacionRecibida(): Collection
    {
        return ControlDocumentacion::with(['personaAlumno', 'documentoRequisito', 'secretarioRecibe.usuario'])
            ->whereNotNull('id_secretario_recibe')
            ->whereNotNull('fecha_entrega')
            ->get()
            ->map(fn (ControlDocumentacion $c) => $this->evento(
                'documentacion',
                $c->fecha_entrega ? Carbon::instance($c->fecha_entrega) : null,
                $c->secretarioRecibe,
                "Recibió \"{$c->documentoRequisito?->nombre_documento}\" de {$c->personaAlumno?->apellido}, {$c->personaAlumno?->nombre}"
            ));
    }

    private function bajasMesa(): Collection
    {
        return InscripcionMesa::with(['personaAlumno', 'mesaExamen.materia.nombreMateria', 'secretarioBaja.usuario'])
            ->whereNotNull('fecha_baja')
            ->get()
            ->map(fn (InscripcionMesa $i) => $this->evento(
                'baja_mesa',
                $i->fecha_baja,
                $i->secretarioBaja,
                "Dio de baja la inscripción a mesa de {$i->personaAlumno?->apellido}, {$i->personaAlumno?->nombre} — {$i->mesaExamen?->materia?->nombre}"
            ));
    }

    private function actas(): Collection
    {
        return Acta::with(['mesaExamen.materia.nombreMateria', 'secretarioCreador.usuario', 'directorFirmante.usuario'])
            ->get()
            ->map(function (Acta $a) {
                $materia = $a->mesaExamen?->materia?->nombre;
                $firmante = $a->directorFirmante ? " — firmada por {$a->directorFirmante->apellido}, {$a->directorFirmante->nombre}" : '';

                return $this->evento(
                    'acta',
                    $a->fecha_generacion,
                    $a->secretarioCreador,
                    "Generó el acta {$a->libro}/{$a->folio} de {$materia}{$firmante}"
                );
            });
    }

    private function configuracion(): Collection
    {
        return ConfiguracionInstitucion::with('secretarioModifica.usuario')
            ->whereNotNull('id_secretario_modifica')
            ->whereNotNull('fecha_ultima_modificacion')
            ->get()
            ->map(fn (ConfiguracionInstitucion $c) => $this->evento(
                'configuracion',
                $c->fecha_ultima_modificacion,
                $c->secretarioModifica,
                'Modificó la configuración institucional'
            ));
    }

    private function bajasProfesor(): Collection
    {
        return Profesor::with(['persona', 'secretarioBaja.usuario'])
            ->whereNotNull('fecha_baja')
            ->get()
            ->map(fn (Profesor $p) => $this->evento(
                'baja_profesor',
                $p->fecha_baja,
                $p->secretarioBaja,
                "Dio de baja al profesor {$p->persona?->apellido}, {$p->persona?->nombre}"
            ));
    }

    private function reactivacionesProfesor(): Collection
    {
        return Profesor::with(['persona', 'secretarioReactiva.usuario'])
            ->whereNotNull('fecha_reactivacion')
            ->get()
            ->map(fn (Profesor $p) => $this->evento(
                'reactivacion_profesor',
                $p->fecha_reactivacion,
                $p->secretarioReactiva,
                "Reactivó al profesor {$p->persona?->apellido}, {$p->persona?->nombre}"
            ));
    }
}
