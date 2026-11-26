<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\ConceptoCaja;
use App\Models\CondicionAlumno;
use App\Models\ConfiguracionCuota;
use App\Models\ConfiguracionMatricula;
use App\Models\CuotaAlumno;
use App\Models\MatriculaAlumno;
use App\Models\MedioPago;
use App\Models\Mes;
use App\Models\MovimientoCaja;
use App\Models\Persona;
use App\Models\TarifaRecursante;
use App\Models\TipoMovimiento;
use App\Models\Usuario;
use App\Support\RecalculadorCuotas;
use App\Support\CorteActivo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CuotaController extends Controller
{
    public function index(Request $request): View
    {
        // Pone las cuotas impagas al precio y la mora de hoy (no depende de
        // la tarea diaria, que en Laragon / el hosting no corre sin cron).
        RecalculadorCuotas::aplicarATodos();

        $busqueda = trim((string) $request->query('q'));
        $alumnoSeleccionado = null;
        $resultados = collect();

        if ($request->query('alumno')) {
            $alumnoSeleccionado = Usuario::alumnos()
                ->with([
                    'persona.inscripcionesCarrera.carrera',
                    'persona.cuotas' => fn ($q) => $q->with('mes')->orderBy('id_anio_lectivo')->orderBy('id_mes'),
                ])
                ->find($request->query('alumno'));
        } elseif ($busqueda) {
            $resultados = Usuario::alumnos()
                ->whereHas('persona', fn ($q) => $q->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('apellido', 'like', "%{$busqueda}%")
                    ->orWhere('dni', 'like', "%{$busqueda}%"))
                ->with('persona.inscripcionesCarrera.carrera')
                ->get()
                ->sortBy(fn ($u) => $u->persona->apellido)
                ->take(8)
                ->values()
                ->map(function (Usuario $u) {
                    // Meses adeudados sumando todas sus carreras.
                    $u->cuotas_pendientes_count = $u->persona->inscripcionesCarrera
                        ->sum(fn ($i) => $u->persona->mesesAdeudados(null, $i->id_carrera)->count());

                    return $u;
                });
        }

        // Carrera que se está viendo (pestañas, si cursa dos). Por defecto, la de su inscripción activa.
        $persona = $alumnoSeleccionado?->persona;
        $carrerasAlumno = $persona ? $persona->inscripcionesCarrera->pluck('carrera')->filter()->unique('id_carrera')->values() : collect();
        $idCarrera = $carrerasAlumno->contains('id_carrera', (int) $request->query('carrera'))
            ? (int) $request->query('carrera')
            : ($persona?->inscripcionCarreraActiva()?->id_carrera ?? $carrerasAlumno->first()?->id_carrera);

        // La pantalla trabaja solo con las cuotas de esa carrera.
        $persona?->setRelation('cuotas', $persona->cuotas->where('id_carrera', $idCarrera)->values());

        $corteActivo = CorteActivo::actual();
        $montoActual = ConfiguracionCuota::de($corteActivo?->id_anio_lectivo, $idCarrera)?->montoVigente();

        // Regular / Recursante / Cursada completa: al recursante y al de
        // cursada completa no se les generan cuotas solas — se les cargan
        // desde "Generar cuotas" de esta pantalla. Al recursante el monto no
        // se le precarga: depende de cuántas materias cursa.
        $condicionAlumno = $persona?->condicionInscripcion($idCarrera);

        // Para que "Generar cuota nueva" autocomplete monto/concepto/día de
        // vencimiento con lo cargado en Configuración de Cuotas, por cada
        // año lectivo (el formulario permite elegir cualquiera, no solo el activo).
        $configuracionesCuotaPorAnio = ConfiguracionCuota::with('aumentos', 'anioLectivo')->where('id_carrera', $idCarrera)->get()
            ->keyBy('id_anio_lectivo')
            ->map(fn (ConfiguracionCuota $c) => [
                // Recursante: precio de su tabla según cuántas materias cursa.
                'monto' => $condicionAlumno === CondicionAlumno::RECURSANTE
                    ? TarifaRecursante::montoPara($persona, $c->id_anio_lectivo, $idCarrera)
                    : $c->montoVigente(),
                'dia_vencimiento' => $c->dia_vencimiento,
                'concepto' => $c->concepto,
            ]);

        $anioMatricula = $corteActivo?->id_anio_lectivo ?? AnioLectivo::orderByDesc('anio')->value('id_anio_lectivo');
        $matricula = $persona && $anioMatricula ? $persona->matriculaDe($anioMatricula, $idCarrera) : null;

        // Con un alumno abierto se muestra todo SU historial de cobros, sin
        // mezclarlo con el de otros alumnos ni filtrarlo por año lectivo
        // activo (igual que en Estado de Pagos) — así no quedan pagos
        // "tapados" por los de otros alumnos en una lista global paginada.
        $historial = CuotaAlumno::where('pagado', true)
            ->with('personaAlumno', 'mes', 'movimientoCaja.medioPago')
            ->when(
                $alumnoSeleccionado,
                fn ($q) => $q->where('id_persona_alumno', $alumnoSeleccionado->persona->id_persona),
                fn ($q) => $q
                    ->when($corteActivo, fn ($q2) => $q2->where('id_anio_lectivo', $corteActivo->id_anio_lectivo))
                    ->when($request->query('h_alumno'), fn ($q2, $v) => $q2->whereHas('personaAlumno', fn ($w) => $w->where('nombre', 'like', "%{$v}%")->orWhere('apellido', 'like', "%{$v}%")))
            )
            ->orderByDesc('id_cuota')
            ->paginate(8, ['*'], 'historial')
            ->withQueryString();

        return view('director.cuotas.index', [
            'busqueda' => $busqueda,
            'resultados' => $resultados,
            'alumnoSeleccionado' => $alumnoSeleccionado,
            'estadoMensual' => $persona ? $persona->estadoMensualCuotas(null, $idCarrera) : collect(),
            'primerMesSinGenerar' => $persona ? $persona->mesesSinCuotaGenerada($idCarrera)->first() : null,
            'carrerasAlumno' => $carrerasAlumno,
            'idCarrera' => $idCarrera,
            'historial' => $historial,
            'filtroHistorialAlumno' => $request->query('h_alumno'),
            'anios' => AnioLectivo::orderByDesc('anio')->get(),
            'meses' => Mes::orderBy('id_mes')->get(),
            'corteActivo' => $corteActivo,
            'montoActual' => $montoActual,
            'configuracionesCuotaPorAnio' => $configuracionesCuotaPorAnio,
            'matricula' => $matricula,
            'anioMatricula' => AnioLectivo::find($anioMatricula),
            'hayConfiguracionMatricula' => $anioMatricula && ConfiguracionMatricula::where('id_anio_lectivo', $anioMatricula)->exists(),
            'condicionAlumno' => $condicionAlumno,
        ]);
    }

    public function generar(Request $request): RedirectResponse
    {
        // El período de cobro (por defecto marzo a diciembre) lo define
        // Dirección en Configuración de Cuotas. "Cursada completa" es la
        // única excepción: sus cuotas van atadas al mes de cada mesa que
        // rinde, no al período general, así que para esa condición no se
        // restringe el mes (ver Persona::estadoMensualCuotas()).
        $persona = Persona::find($request->input('persona_id'));
        $idCarrera = (int) $request->input('id_carrera');
        $respetaPeriodo = ! $persona?->tieneCursadaCompleta($idCarrera);

        $configuracion = ConfiguracionCuota::de($request->integer('id_anio_lectivo'), $idCarrera);
        $mesDesde = $configuracion?->mes_desde ?? 3;
        $mesHasta = $configuracion?->mes_hasta ?? 12;
        // Tope del importe: lo que vale hoy la cuota del mes más avanzado de
        // los elegidos (con los aumentos que ya rigen).
        $mesesElegidos = array_map('intval', (array) $request->input('meses', []));
        $montoMaximo = $configuracion && $mesesElegidos
            ? $configuracion->montoACobrar(max($mesesElegidos))
            : $configuracion?->montoVigente();

        // Recursante: el tope es el precio de su tabla (no el de la cuota regular).
        if ($persona?->esRecursante($idCarrera)) {
            $montoMaximo = TarifaRecursante::montoPara($persona, $request->integer('id_anio_lectivo'), $idCarrera);
        }

        $data = $request->validate([
            'alumno_usuario_id' => ['required', 'exists:usuario,id_usuario'],
            'persona_id' => ['required', 'exists:persona,id_persona'],
            'id_carrera' => ['required', 'exists:carrera,id_carrera'],
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'meses' => ['required', 'array', 'min:1'],
            'meses.*' => array_filter([
                'integer',
                'exists:mes,id_mes',
                $respetaPeriodo ? "between:{$mesDesde},{$mesHasta}" : null,
            ]),
            'concepto' => ['nullable', 'string', 'max:120', 'regex:/^[\pL\s\'-]+$/u'],
            'monto' => [
                'required', 'numeric', 'min:0.01', 'max:1000000',
                function ($attribute, $value, $fail) use ($montoMaximo) {
                    if ($montoMaximo !== null && (float) $value > (float) $montoMaximo) {
                        $fail('El importe no puede ser mayor al monto configurado para este año lectivo ($' . number_format($montoMaximo, 0, ',', '.') . ').');
                    }
                },
            ],
            'dia_vencimiento' => ['required', 'integer', 'min:1', 'max:31'],
        ], [
            'monto.required' => 'Ingresá el importe de la cuota.',
            'monto.numeric' => 'El importe tiene que ser un número.',
            'monto.min' => 'El importe tiene que ser mayor a $0.',
            'monto.max' => 'El importe no puede superar $1.000.000.',
            'concepto.regex' => 'El concepto solo puede tener letras.',
            'meses.*.between' => 'Ese mes está fuera del período de cobro configurado para este año lectivo (de '
                . (Mes::find($mesDesde)?->nombre_mes ?? $mesDesde) . ' a ' . (Mes::find($mesHasta)?->nombre_mes ?? $mesHasta) . ').',
        ]);

        $anio = AnioLectivo::findOrFail($data['id_anio_lectivo']);
        $persona = $persona ?? Persona::findOrFail($data['persona_id']);

        if (! $this->matriculaPagadaEstricta($persona, $anio->id_anio_lectivo, $idCarrera)) {
            return back()->withErrors(['meses' => "No se puede generar una cuota de {$anio->anio}: primero hay que generar y cobrar la matrícula de ese año."])->withInput();
        }

        $creadas = 0;
        $omitidas = 0;

        DB::transaction(function () use ($data, $anio, &$creadas, &$omitidas) {
            foreach ($data['meses'] as $idMes) {
                $existe = CuotaAlumno::where('id_persona_alumno', $data['persona_id'])
                    ->where('id_anio_lectivo', $anio->id_anio_lectivo)
                    ->where('id_carrera', $data['id_carrera'])
                    ->where('id_mes', $idMes)
                    ->exists();

                if ($existe) {
                    $omitidas++;

                    continue;
                }

                CuotaAlumno::create([
                    'id_persona_alumno' => $data['persona_id'],
                    'id_anio_lectivo' => $anio->id_anio_lectivo,
                    'id_carrera' => $data['id_carrera'],
                    'id_mes' => $idMes,
                    'concepto' => $data['concepto'] ?: null,
                    'fecha_vencimiento' => ConfiguracionCuota::fechaVencimiento($anio->anio, $idMes, $data['dia_vencimiento']),
                    'monto' => round((float) $data['monto'], 2),
                    'recargo' => 0,
                    'pagado' => false,
                ]);

                $creadas++;
            }
        });

        $mensaje = $creadas > 0
            ? "Se generaron {$creadas} cuota(s) nueva(s)."
            : 'No se generó ninguna cuota nueva.';

        if ($omitidas > 0) {
            $mensaje .= " Se omitieron {$omitidas} porque ya existían para ese alumno y año.";
        }

        return redirect()->route('director.cuotas.index', ['alumno' => $data['alumno_usuario_id'], 'carrera' => $request->input('id_carrera')])
            ->with('status', $mensaje);
    }

    public function cobrar(Request $request): RedirectResponse
    {
        // Por si algo cambió desde que se abrió la pantalla: que el cobro
        // use el precio y la mora de hoy.
        RecalculadorCuotas::aplicarATodos();

        $data = $request->validate([
            'persona_id' => ['required', 'exists:persona,id_persona'],
            'fecha_pago' => ['required', 'date'],
            'medio_pago' => ['required', 'in:Efectivo,Transferencia'],
            'cuotas' => ['required', 'array', 'min:1'],
            'cuotas.*.pagar' => ['nullable'],
        ]);

        $seleccionadas = collect($data['cuotas'])->filter(fn ($item) => ! empty($item['pagar']));

        if ($seleccionadas->isEmpty()) {
            return back()->withErrors(['cuotas' => 'Seleccioná al menos una cuota para registrar el cobro.']);
        }

        $alumno = Persona::findOrFail($data['persona_id']);
        $idsSeleccionados = $seleccionadas->keys()->map(fn ($id) => (int) $id);

        if ($error = $this->validarMatriculaPagada($alumno, $idsSeleccionados)) {
            return back()->withErrors(['cuotas' => $error]);
        }

        if ($error = $this->validarOrdenDeCobro($alumno, $idsSeleccionados)) {
            return back()->withErrors(['cuotas' => $error]);
        }

        $totalCobrado = 0;
        $cantidad = 0;

        DB::transaction(function () use ($seleccionadas, $data, &$totalCobrado, &$cantidad) {
            $medioPago = MedioPago::where('nombre_medio', $data['medio_pago'])->firstOrFail();
            $tipoIngreso = TipoMovimiento::where('nombre_tipo', 'Ingreso')->firstOrFail();
            $conceptoCuota = ConceptoCaja::firstOrCreate(
                ['nombre_concepto' => 'Cuota mensual'],
                ['id_tipo_movimiento' => $tipoIngreso->id_tipo_movimiento]
            );

            foreach ($seleccionadas as $cuotaId => $item) {
                $cuota = CuotaAlumno::where('id_persona_alumno', $data['persona_id'])
                    ->where('pagado', false)
                    ->find($cuotaId);

                if (! $cuota) {
                    continue;
                }

                $monto = round((float) $cuota->monto, 2); // monto bloqueado: el que calculó el sistema
                // El recargo nunca sale del formulario: siempre es el que ya
                // tiene calculado la cuota (desde Configuración de Cuotas /
                // "Calcular moras"), para que no se pueda alterar al cobrar.
                $recargo = round((float) $cuota->recargo, 2);
                $conceptoTexto = $cuota->etiqueta;

                $descripcion = "{$conceptoTexto} — {$cuota->personaAlumno->apellido}, {$cuota->personaAlumno->nombre}";
                if ($recargo > 0) {
                    $descripcion .= ' (incluye recargo $' . number_format($recargo, 0, ',', '.') . ')';
                }

                $movimiento = MovimientoCaja::create([
                    'id_concepto' => $conceptoCuota->id_concepto,
                    'monto' => $monto + $recargo,
                    // La fecha que eligió el director (puede ser una fecha
                    // pasada, para asentar un cobro que se hizo en efectivo
                    // otro día), pero con la hora real de este momento, para
                    // que el Libro de Caja no muestre siempre 00:00.
                    'fecha_movimiento' => now()->setDateFrom($data['fecha_pago']),
                    'descripcion_detalle' => $descripcion,
                    'id_secretario_registra' => Auth::user()->id_persona,
                    'id_medio_pago' => $medioPago->id_medio_pago,
                ]);

                $cuota->update([
                    'monto' => $monto,
                    'recargo' => $recargo,
                    'pagado' => true,
                    'fecha_pago' => $data['fecha_pago'],
                    'id_movimiento_caja' => $movimiento->id_movimiento,
                ]);

                $totalCobrado += $monto + $recargo;
                $cantidad++;
            }
        });

        return redirect()->route('director.cuotas.index', ['alumno' => $alumno->usuario?->id_usuario, 'carrera' => $request->input('id_carrera')])
            ->with('status', "Se registró el cobro de {$cantidad} cuota(s) por $" . number_format($totalCobrado, 0, ',', '.') . ' y se asentó en el Libro de Caja.');
    }

    /**
     * Genera a mano la matrícula de un año para un alumno que no la tiene
     * (típicamente el de "Cursada completa", que solo paga la matrícula del
     * año en que vuelve a rendir y no entra en la generación automática).
     */
    public function generarMatricula(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'persona_id' => ['required', 'exists:persona,id_persona'],
            'id_carrera' => ['required', 'exists:carrera,id_carrera'],
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
            'alumno_usuario_id' => ['required', 'exists:usuario,id_usuario'],
        ]);

        $configuracion = ConfiguracionMatricula::where('id_anio_lectivo', $data['id_anio_lectivo'])->first();

        if (! $configuracion) {
            return back()->withErrors(['matricula' => 'Primero cargá el monto de la matrícula de ese año en Configuración de Matrícula.']);
        }

        $matricula = MatriculaAlumno::firstOrCreate(
            ['id_persona_alumno' => $data['persona_id'], 'id_anio_lectivo' => $data['id_anio_lectivo'], 'id_carrera' => $data['id_carrera']],
            ['monto' => $configuracion->monto, 'pagado' => false]
        );

        $mensaje = $matricula->wasRecentlyCreated
            ? 'Se generó la matrícula del alumno. Ya la podés cobrar.'
            : 'El alumno ya tenía la matrícula de ese año generada.';

        return redirect()->route('director.cuotas.index', ['alumno' => $data['alumno_usuario_id'], 'carrera' => $request->input('id_carrera')])
            ->with('status', $mensaje);
    }

    public function cobrarMatricula(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'persona_id' => ['required', 'exists:persona,id_persona'],
            'id_matricula' => ['required', 'exists:matricula_alumno,id_matricula'],
            'alumno_usuario_id' => ['required', 'exists:usuario,id_usuario'],
            'fecha_pago' => ['required', 'date'],
            'medio_pago' => ['required', 'in:Efectivo,Transferencia'],
        ]);

        $matricula = MatriculaAlumno::where('id_persona_alumno', $data['persona_id'])
            ->where('pagado', false)
            ->findOrFail($data['id_matricula']);

        if (! $matricula->estaHabilitada()) {
            return back()->withErrors(['matricula' => 'La matrícula todavía no se puede cobrar: se habilita a partir del ' . $matricula->fechaHabilitacion()->format('d/m/Y') . '.']);
        }

        DB::transaction(function () use ($matricula, $data) {
            $medioPago = MedioPago::where('nombre_medio', $data['medio_pago'])->firstOrFail();
            $tipoIngreso = TipoMovimiento::where('nombre_tipo', 'Ingreso')->firstOrFail();
            $conceptoMatricula = ConceptoCaja::firstOrCreate(
                ['nombre_concepto' => 'Matrícula'],
                ['id_tipo_movimiento' => $tipoIngreso->id_tipo_movimiento]
            );

            $descripcion = "Matrícula {$matricula->anioLectivo->anio} ({$matricula->carrera?->nombre_carrera}) — {$matricula->personaAlumno->apellido}, {$matricula->personaAlumno->nombre}";

            $movimiento = MovimientoCaja::create([
                'id_concepto' => $conceptoMatricula->id_concepto,
                'monto' => $matricula->monto,
                'fecha_movimiento' => now()->setDateFrom($data['fecha_pago']),
                'descripcion_detalle' => $descripcion,
                'id_secretario_registra' => Auth::user()->id_persona,
                'id_medio_pago' => $medioPago->id_medio_pago,
            ]);

            $matricula->update([
                'pagado' => true,
                'fecha_pago' => $data['fecha_pago'],
                'id_movimiento_caja' => $movimiento->id_movimiento,
            ]);
        });

        return redirect()->route('director.cuotas.index', ['alumno' => $data['alumno_usuario_id'], 'carrera' => $request->input('id_carrera')])
            ->with('status', 'Se registró el pago de la matrícula y se asentó en el Libro de Caja.');
    }

    /**
     * No se puede cobrar (ni generar) ninguna cuota de un año lectivo sin la
     * matrícula de ese mismo año ya generada y pagada — a diferencia de
     * Persona::matriculaAlDia() (que usan mesas e inscripciones), acá no hay
     * excepción para "nunca se configuró matrícula para ese año": sin
     * matrícula paga, no hay cuota.
     */
    private function matriculaPagadaEstricta(Persona $alumno, int $idAnio, ?int $idCarrera = null): bool
    {
        $matricula = $alumno->matriculaDe($idAnio, $idCarrera);

        return $matricula && $matricula->pagado;
    }

    private function validarMatriculaPagada(Persona $alumno, Collection $idsSeleccionados): ?string
    {
        // Cada par año + carrera de las cuotas elegidas necesita su matrícula paga.
        $involucrados = CuotaAlumno::whereIn('id_cuota', $idsSeleccionados)->get(['id_anio_lectivo', 'id_carrera'])->unique(fn ($c) => "{$c->id_anio_lectivo}-{$c->id_carrera}");

        foreach ($involucrados as $c) {
            $idAnio = $c->id_anio_lectivo;
            if (! $this->matriculaPagadaEstricta($alumno, $idAnio, $c->id_carrera)) {
                $anio = AnioLectivo::find($idAnio);

                return "No se pueden cobrar cuotas de {$anio->anio}: primero hay que generar y cobrar la matrícula de ese año.";
            }
        }

        return null;
    }

    /**
     * Las cuotas se cobran en orden: no se puede cobrar un mes si queda una
     * cuota anterior sin pagar (que no se esté cobrando junto con esta), ni
     * si hay un mes anterior adeudado al que todavía no se le generó la cuota.
     */
    private function validarOrdenDeCobro(Persona $alumno, Collection $idsSeleccionados): ?string
    {
        // El orden se controla dentro de cada carrera (deber una no frena cobrar la otra).
        foreach (CuotaAlumno::whereIn('id_cuota', $idsSeleccionados)->pluck('id_carrera')->unique() as $idCarrera) {
            if ($error = $this->validarOrdenEnCarrera($alumno, $idsSeleccionados, $idCarrera)) {
                return $error;
            }
        }

        return null;
    }

    private function validarOrdenEnCarrera(Persona $alumno, Collection $idsSeleccionados, ?int $idCarrera): ?string
    {
        $pendientes = CuotaAlumno::where('cuota_alumno.id_persona_alumno', $alumno->id_persona)
            ->where('cuota_alumno.id_carrera', $idCarrera)
            ->where('cuota_alumno.pagado', false)
            ->join('anio_lectivo', 'anio_lectivo.id_anio_lectivo', '=', 'cuota_alumno.id_anio_lectivo')
            ->orderBy('anio_lectivo.anio')
            ->orderBy('cuota_alumno.id_mes')
            ->select('cuota_alumno.*', 'anio_lectivo.anio')
            ->with('mes')
            ->get();

        $ultimaSeleccionada = $pendientes->keys()->filter(fn ($i) => $idsSeleccionados->contains($pendientes[$i]->id_cuota))->max();

        if ($ultimaSeleccionada === null) {
            return null;
        }

        $salteada = $pendientes->take($ultimaSeleccionada)->first(fn ($c) => ! $idsSeleccionados->contains($c->id_cuota));

        if ($salteada) {
            return "No se pueden saltear cuotas: primero hay que cobrar la de {$salteada->mes->nombre_mes} {$salteada->anio}. Seleccionala junto con las siguientes.";
        }

        $sinGenerar = $alumno->mesesSinCuotaGenerada($idCarrera)->first();

        if ($sinGenerar) {
            $posterior = $pendientes->take($ultimaSeleccionada + 1)->first(fn ($c) => $c->id_anio_lectivo == $sinGenerar->id_anio_lectivo && $c->id_mes > $sinGenerar->id_mes);

            if ($posterior) {
                return "El alumno debe {$sinGenerar->nombre_mes}, pero esa cuota todavía no está generada. Generala y cobrala antes de cobrar {$posterior->mes->nombre_mes}.";
            }
        }

        return null;
    }
}
