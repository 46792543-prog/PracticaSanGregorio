@extends('layouts.admin')

@section('titulo', 'Ficha del alumno')

@section('contenido')
    @php
        $inscripcion = $alumno->inscripcionesCarrera->first();
        $estadoInscNombre = $inscripcion?->estadoInscripcion?->nombre_estado;
        $cuotasPagadas = $alumno->cuotas->where('pagado', true)->count();
        $cuotasPendientes = $alumno->cuotas->where('pagado', false)->count();
        $cuotasVencidas = $alumno->cuotas->filter(fn ($c) => $c->vencida)->count();
        $condicionesFinCursada = \App\Models\CondicionAlumno::whereIn('nombre_condicion', ['Regular', 'Promocionada', 'Libre'])->get()->keyBy('nombre_condicion');
        $materiasCursando = $alumno->historialAlumno
            ->filter(fn ($h) => in_array($h->condicion?->nombre_condicion, \App\Models\CondicionAlumno::EN_CURSO, true))
            ->sortBy([['materia.anioCursada.id_anio_cursada', 'asc'], ['materia.nombre', 'asc']]);
    @endphp

    <a href="{{ route('admin.alumnos.index') }}" class="sg-link mb-4 sg-revelar"><x-sg.icono nombre="arrow-left" class="!h-4 !w-4" /> Volver al listado</a>

    {{-- Cabecera de la ficha --}}
    <section class="sg-card sg-revelar overflow-hidden mb-6">
        <div class="h-24 bg-gradient-to-r from-[#082A66] via-[#0B3D91] to-[#4FC3F7] relative overflow-hidden">
            <span class="absolute -right-10 -top-16 h-48 w-48 rounded-full border-[14px] border-[#FFC107]/25"></span>
            <span class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 18px 18px;"></span>
        </div>
        <div class="relative px-6 pb-6 flex items-end justify-between flex-wrap gap-5">
            <div class="flex items-end gap-4 min-w-0">
                <span class="sg-avatar sg-avatar--grande sg-avatar--marca h-20 w-20 shrink-0 -mt-10 text-2xl ring-4 ring-white shadow-lg">
                    {{ mb_substr($alumno->nombre, 0, 1) }}{{ mb_substr($alumno->apellido, 0, 1) }}
                </span>
                <div class="pt-3 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="font-display font-extrabold text-xl text-slate-900">{{ $alumno->apellido }}, {{ $alumno->nombre }}</h1>
                        @if ($estadoInscNombre === 'Activo')
                            <span class="sg-badge sg-badge--verde">Activo</span>
                        @elseif ($estadoInscNombre === 'Baja')
                            <span class="sg-badge sg-badge--rojo">Baja</span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-500 mt-0.5">DNI {{ $alumno->dni }} · {{ $inscripcion?->carrera?->nombre_carrera ?? 'Sin carrera asignada' }}</p>
                </div>
            </div>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('admin.documentacion.show', $alumno) }}" class="sg-btn sg-btn--borde sg-btn--chico"><x-sg.icono nombre="folder" /> Documentación</a>
                <button type="button" onclick="document.getElementById('panel-otra-carrera').classList.toggle('hidden')" class="sg-btn sg-btn--borde sg-btn--chico"><x-sg.icono nombre="plus" /> Inscribir en otra carrera</button>
                @if ($inscripcion && $inscripcion->estadoInscripcion->nombre_estado === 'Activo')
                    <a href="{{ route('admin.alumnos.materias', [$alumno, 'carrera' => $idCarrera]) }}" class="sg-btn sg-btn--azul sg-btn--chico"><x-sg.icono nombre="cap" /> Inscribir a materias</a>
                    <button type="button" onclick="document.getElementById('panel-cerrar-cursada').classList.toggle('hidden')" class="sg-btn sg-btn--borde sg-btn--chico">
                        <x-sg.icono nombre="document-check" /> Cerrar cursada
                        @if ($materiasCursando->isNotEmpty())
                            <span class="text-xs opacity-80">({{ $materiasCursando->count() }})</span>
                        @endif
                    </button>
                @endif
                @if ($progresoPromocion)
                    <form method="POST" action="{{ route('admin.alumnos.promover', $alumno) }}"
                          data-confirm="¿Promover a {{ $alumno->nombre }} {{ $alumno->apellido }} a {{ $progresoPromocion['proximoAnio']->nombre_anio }}?">
                        @csrf @method('PUT') <input type="hidden" name="id_carrera" value="{{ $idCarrera }}">
                        <button type="submit" @disabled(! $progresoPromocion['puede'])
                                title="{{ $progresoPromocion['aprobadas'] }} de {{ $progresoPromocion['total'] }} materias de su año aprobadas{{ $progresoPromocion['puede'] ? '' : ' — hace falta la mayoría para promover' }}"
                                @class(['sg-btn sg-btn--chico', 'sg-btn--verde' => $progresoPromocion['puede'], 'sg-btn--borde opacity-50 cursor-not-allowed' => ! $progresoPromocion['puede']])>
                            <x-sg.icono nombre="trending-up" />
                            Promover a {{ $progresoPromocion['proximoAnio']->nombre_anio }}
                            <span class="text-xs opacity-80">({{ $progresoPromocion['aprobadas'] }}/{{ $progresoPromocion['total'] }})</span>
                        </button>
                    </form>
                @endif
                @if ($inscripcion && $inscripcion->estadoInscripcion->nombre_estado === 'Activo')
                    <form method="POST" action="{{ route('admin.alumnos.baja', $alumno) }}" data-confirm="¿Confirmás dar de baja a este alumno?">
                        @csrf @method('PUT') <input type="hidden" name="id_carrera" value="{{ $idCarrera }}">
                        <button type="submit" class="sg-btn sg-btn--peligro sg-btn--chico"><x-sg.icono nombre="user-minus" /> Dar de baja</button>
                    </form>
                @elseif ($inscripcion && $inscripcion->estadoInscripcion->nombre_estado === 'Baja')
                    <form method="POST" action="{{ route('admin.alumnos.alta', $alumno) }}" data-confirm="¿Confirmás dar de alta nuevamente a este alumno?">
                        @csrf @method('PUT') <input type="hidden" name="id_carrera" value="{{ $idCarrera }}">
                        <button type="submit" class="sg-btn sg-btn--verde sg-btn--chico"><x-sg.icono nombre="user-plus" /> Dar de alta</button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    {{-- Si cursa dos carreras: una pestaña por carrera (todo lo de abajo es de la carrera elegida) --}}
    @if ($carrerasAlumno->count() > 1)
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach ($carrerasAlumno as $carrera)
                <a href="{{ route('admin.alumnos.show', [$alumno, 'carrera' => $carrera->id_carrera]) }}"
                   class="sg-btn sg-btn--chico {{ $carrera->id_carrera === $idCarrera ? 'sg-btn--azul' : 'sg-btn--borde' }}">{{ $carrera->nombre_carrera }}</a>
            @endforeach
        </div>
    @endif

    {{-- Inscribir en otra carrera (oculto hasta tocar el botón) --}}
    <div id="panel-otra-carrera" class="{{ $errors->has('carrera_id') ? '' : 'hidden' }} sg-card p-5 mb-6">
        <h3 class="font-display font-bold text-slate-800 text-sm mb-4">Inscribir en otra carrera</h3>
        @if ($carrerasDisponibles->isEmpty())
            {{-- No hay otra carrera activa a la que inscribirlo --}}
            <p class="sg-aviso sg-aviso--info text-sm">No hay otra carrera activa para inscribirlo. Primero cargá la carrera en <a href="{{ route('admin.carreras.index') }}" class="sg-link">Carreras</a>.</p>
        @else
        <form method="POST" action="{{ route('admin.alumnos.carreras.store', $alumno) }}" class="grid sm:grid-cols-5 gap-3 items-end">
            @csrf
            <div class="sm:col-span-2">
                <label class="sg-label">Carrera *</label>
                <select name="carrera_id" required class="sg-input w-full">
                    @foreach ($carrerasDisponibles as $carrera)
                        <option value="{{ $carrera->id_carrera }}" data-turnos="{{ $carrera->turnos->pluck('id_turno_cursada')->join(',') }}">{{ $carrera->nombre_carrera }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="sg-label">Año de cursada *</label>
                <select name="anio_cursada_id" required class="sg-input w-full">
                    @foreach ($aniosCursada as $anio)
                        <option value="{{ $anio->id_anio_cursada }}">{{ $anio->nombre_anio }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="sg-label">Año lectivo *</label>
                <select name="anio_lectivo_id" required class="sg-input w-full">
                    @foreach ($aniosLectivos as $anio)
                        <option value="{{ $anio->id_anio_lectivo }}" @selected($anio->id_anio_lectivo === $corteActivoId)>{{ $anio->anio }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="sg-label">Turno *</label>
                <select name="turno_cursada_id" required class="sg-input w-full">
                    @foreach ($turnos as $turno)
                        <option value="{{ $turno->id_turno_cursada }}">{{ $turno->nombre_turno }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-5 flex justify-end">
                <button type="submit" class="sg-btn sg-btn--azul sg-btn--chico"><x-sg.icono nombre="check" /> Inscribir</button>
            </div>
        </form>
        @endif
    </div>

    {{-- Plan de estudios del alumno en esta carrera + pasarlo a otro plan --}}
    @if ($planActual)
        @php $caducado = $planActual->estado() === 'Caducado'; @endphp
        <div class="sg-card p-5 mb-6 {{ $caducado ? 'ring-2 ring-red-200' : '' }}">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-slate-600">
                    <span class="font-semibold text-slate-800">Plan de estudios:</span> {{ $planActual->nombre }}
                    <span class="sg-badge {{ $caducado ? 'sg-badge--rojo' : 'sg-badge--verde' }} sg-badge--sin-punto ml-1">{{ $planActual->estado() }}</span>
                </p>
                @if ($planesDestino->isNotEmpty())
                    <button type="button" onclick="document.getElementById('panel-cambio-plan').classList.toggle('hidden')" class="sg-btn sg-btn--borde sg-btn--chico"><x-sg.icono nombre="refresh" /> Pasar a otro plan</button>
                @endif
            </div>
            @if ($caducado)
                <p class="sg-aviso sg-aviso--error text-xs mt-3"><span>
                    Su plan caducó{{ $planActual->anio_caducidad ? ' en ' . $planActual->anio_caducidad : '' }}: <strong>no puede inscribirse a cursar ni a rendir</strong> hasta que lo pases al plan vigente.
                </span></p>
            @endif

            @if ($planDestino)
                <div id="panel-cambio-plan" class="{{ $caducado || request('plan_destino') ? '' : 'hidden' }} mt-4 pt-4 border-t border-slate-100">
                    {{-- Elegir el plan destino recarga la vista previa --}}
                    <form method="GET" class="flex flex-wrap items-end gap-3 mb-4">
                        <input type="hidden" name="carrera" value="{{ $idCarrera }}">
                        <div>
                            <label class="sg-label">Pasar al plan</label>
                            <select name="plan_destino" onchange="this.form.submit()" class="sg-input">
                                @foreach ($planesDestino as $p)
                                    <option value="{{ $p->id_plan_estudio }}" @selected($p->id_plan_estudio === $planDestino->id_plan_estudio)>{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    <p class="text-xs font-semibold text-slate-500 mb-2">Así queda su historial en el {{ $planDestino->nombre }}:</p>
                    <div class="overflow-x-auto mb-4">
                        <table class="sg-tabla">
                            <thead><tr><th>Materia (plan actual)</th><th>Condición</th><th>En el {{ $planDestino->nombre }}</th></tr></thead>
                            <tbody class="sg-filas">
                                @foreach ($vistaPreviaPlan['filas'] as $fila)
                                    <tr>
                                        <td>{{ $fila['materia']->nombre }}</td>
                                        <td class="text-slate-500">{{ $fila['condicion'] }}{{ $fila['nota'] ? ' (' . (int) $fila['nota'] . ')' : '' }}</td>
                                        <td>
                                            @if (in_array($fila['condicion'], \App\Models\CondicionAlumno::EN_CURSO, true))
                                                <span class="sg-badge sg-badge--amarillo sg-badge--sin-punto">Está cursando: primero cerrá la cursada</span>
                                            @elseif ($fila['destino'])
                                                <span class="sg-badge sg-badge--verde sg-badge--sin-punto">Se reconoce igual</span>
                                            @else
                                                <span class="sg-badge sg-badge--gris sg-badge--sin-punto">No existe en el plan nuevo: queda en el historial viejo</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                @foreach ($vistaPreviaPlan['nuevas'] as $materia)
                                    <tr>
                                        <td class="text-slate-400">—</td>
                                        <td class="text-slate-400">—</td>
                                        <td><span class="sg-badge sg-badge--azul sg-badge--sin-punto">{{ $materia->nombre }}: la tiene que cursar</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($impedimentoPlan)
                        <p class="sg-aviso sg-aviso--alerta text-xs"><span>{{ $impedimentoPlan }}</span></p>
                    @else
                        <form method="POST" action="{{ route('admin.alumnos.cambiar-plan', $alumno) }}" class="flex justify-end"
                              data-confirm="¿Pasar a {{ $alumno->nombre }} {{ $alumno->apellido }} del {{ $planActual->nombre }} al {{ $planDestino->nombre }}?">
                            @csrf
                            <input type="hidden" name="id_carrera" value="{{ $idCarrera }}">
                            <input type="hidden" name="id_plan_destino" value="{{ $planDestino->id_plan_estudio }}">
                            <button type="submit" class="sg-btn sg-btn--azul sg-btn--chico"><x-sg.icono nombre="check" /> Confirmar cambio de plan</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <div id="panel-cerrar-cursada" class="{{ session('panel_abierto') === 'cerrar-cursada' ? '' : 'hidden' }}">
    <x-sg.tarjeta titulo="Cerrar cursada" subtitulo="Cuando termina el cuatrimestre o el año, marcá cómo quedó cada materia — recién ahí el alumno se puede anotar a la mesa de examen." icono="document-check" tono="amarillo" class="mb-6" :cuerpo="false">
        @if ($materiasCursando->isEmpty())
            <div class="p-5"><x-sg.vacio icono="document-check">Este alumno no tiene materias cursando en este momento.</x-sg.vacio></div>
        @else
            <div class="overflow-x-auto">
                <table class="sg-tabla">
                    <thead>
                        <tr>
                            <th>Materia</th>
                            <th>Año</th>
                            <th>Quedó</th>
                            <th class="sg-num">Nota</th>
                            <th>Fecha</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($materiasCursando as $historial)
                            @php
                                $formId = 'form-cerrar-cursada-' . $historial->id_historial;
                                $nombreRegimen = $historial->materia->regimen?->nombre_regimen;
                                $promocionable = $nombreRegimen !== 'Solo Examen Final';
                            @endphp
                            <tr>
                                <td class="font-semibold text-slate-700">
                                    {{ $historial->materia->nombre }}
                                    <div>
                                        @if ($nombreRegimen)
                                            <span @class(['sg-badge sg-badge--sin-punto text-[11px]', 'sg-badge--azul' => $promocionable, 'sg-badge--gris' => ! $promocionable])>
                                                {{ $promocionable ? 'Promocionable' : 'Con Final Obligatorio' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-slate-500">{{ $historial->materia->anioCursada->nombre_anio ?? '—' }}</td>
                                <td>
                                    <select form="{{ $formId }}" name="id_condicion" data-cerrar-cursada-condicion class="sg-input text-sm" required>
                                        <option value="">Elegí...</option>
                                        @foreach (['Regular', 'Promocionada', 'Libre'] as $opcion)
                                            @if ($condicion = $condicionesFinCursada->get($opcion))
                                                <option value="{{ $condicion->id_condicion }}" data-nombre="{{ $opcion }}">{{ $opcion }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input form="{{ $formId }}" type="number" step="1" min="1" max="10" name="nota_cursada" data-cerrar-cursada-nota disabled
                                           class="sg-input sg-input--chico w-20 text-center">
                                </td>
                                <td>
                                    <input form="{{ $formId }}" type="date" name="fecha_ultima_modificacion" value="{{ now()->format('Y-m-d') }}" required class="sg-input text-sm">
                                </td>
                                <td class="text-right">
                                    <button form="{{ $formId }}" type="submit" class="sg-btn sg-btn--primario sg-btn--chico">Cerrar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @foreach ($materiasCursando as $historial)
                <form id="form-cerrar-cursada-{{ $historial->id_historial }}" method="POST" action="{{ route('admin.alumnos.historial.cerrar-cursada', [$alumno, $historial]) }}" class="hidden">
                    @csrf @method('PUT') <input type="hidden" name="id_carrera" value="{{ $idCarrera }}">
                </form>
            @endforeach

            <script>
                // Nota entera del 1 al 10: saca cualquier caracter que no sea dígito
                // apenas se escribe, así nunca se puede llegar a guardar un decimal.
                document.querySelectorAll('[data-cerrar-cursada-nota]').forEach((input) => {
                    input.addEventListener('input', () => {
                        let valor = input.value.replace(/[^0-9]/g, '');
                        if (valor !== '') {
                            valor = String(Math.min(10, Math.max(1, parseInt(valor, 10))));
                        }
                        input.value = valor;
                    });
                });

                document.querySelectorAll('[data-cerrar-cursada-condicion]').forEach((select) => {
                    const actualizar = () => {
                        const nombre = select.selectedOptions[0]?.dataset.nombre;
                        const fila = select.closest('tr');
                        const inputNota = fila.querySelector('[data-cerrar-cursada-nota]');
                        if (nombre === 'Promocionada') {
                            inputNota.style.visibility = '';
                            inputNota.disabled = false;
                            inputNota.required = true;
                            inputNota.min = 7;
                            inputNota.placeholder = '7 a 10';
                        } else {
                            // Regular o Libre: sin nota — la cursada se cierra
                            // sin calificación, va a examen final (Regular) o
                            // a rendir libre (Libre).
                            inputNota.value = '';
                            inputNota.disabled = true;
                            inputNota.required = false;
                            inputNota.placeholder = '';
                            inputNota.style.visibility = 'hidden';
                        }
                    };
                    select.addEventListener('change', actualizar);
                    actualizar();
                });
            </script>
        @endif
    </x-sg.tarjeta>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 mb-6" data-sg-escalonar>
        <div class="sg-card p-5">
            <div class="flex items-center gap-3 mb-4">
                <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="user" /></span>
                <h3 class="font-display font-bold text-slate-800 text-sm">Datos personales</h3>
            </div>
            <dl class="text-sm space-y-3">
                <div class="flex justify-between gap-3"><dt class="text-slate-400 flex items-center gap-2"><x-sg.icono nombre="mail" class="h-4 w-4" /> Email</dt><dd class="text-slate-700 font-medium truncate">{{ $alumno->usuario->email ?? '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-400 flex items-center gap-2"><x-sg.icono nombre="phone" class="h-4 w-4" /> Teléfono</dt><dd class="text-slate-700 font-medium">{{ $alumno->telefono ?? '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-400 flex items-center gap-2"><x-sg.icono nombre="calendar" class="h-4 w-4" /> Nacimiento</dt><dd class="text-slate-700 font-medium">{{ \App\Support\FechaEsp::corta($alumno->fecha_nacimiento) }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-400 flex items-center gap-2"><x-sg.icono nombre="map" class="h-4 w-4" /> Localidad</dt><dd class="text-slate-700 font-medium">{{ $alumno->localidad ?? '—' }}</dd></div>
            </dl>
        </div>
        <div class="sg-card p-5">
            <div class="flex items-center gap-3 mb-4">
                <span class="sg-chip sg-chip--chico sg-chip--amarillo"><x-sg.icono nombre="cap" /></span>
                <h3 class="font-display font-bold text-slate-800 text-sm">Situación académica</h3>
            </div>
            <dl class="text-sm space-y-2.5">
                <div class="flex justify-between"><dt class="text-slate-400">Año de ingreso</dt><dd class="text-slate-700 font-medium">{{ $anioIngreso ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Año lectivo</dt><dd class="text-slate-700 font-medium">{{ $inscripcion?->anioLectivo?->anio ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Año que cursa</dt><dd class="text-slate-700 font-medium">{{ $inscripcion?->anioCursada?->nombre_anio ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Turno</dt><dd class="text-slate-700 font-medium">{{ $inscripcion?->turnoCursada?->nombre_turno ?? '—' }}</dd></div>
                <div class="flex justify-between items-center"><dt class="text-slate-400">Estado</dt><dd>
                    <span @class(['sg-badge', 'sg-badge--verde' => $estadoInscNombre === 'Activo', 'sg-badge--rojo' => $estadoInscNombre === 'Baja'])>
                        {{ $estadoInscNombre ?? '—' }}
                    </span>
                </dd></div>
            </dl>
        </div>
        <div class="sg-card p-5">
            <div class="flex items-center gap-3 mb-4">
                <span class="sg-chip sg-chip--chico sg-chip--gris"><x-sg.icono nombre="refresh" /></span>
                <h3 class="font-display font-bold text-slate-800 text-sm">Modalidad de cursada</h3>
            </div>
            {{-- Ya no se cambia a mano: es Recursante si recursa alguna materia de la carrera (ver historial) --}}
            <p class="text-slate-700 font-medium text-sm">{{ $inscripcion?->condicion?->nombre_condicion ?? '—' }}</p>
            <p class="text-xs text-slate-400 mt-1">Se actualiza sola: pasa a Recursante cuando se le carga una materia como "Recursando" en el historial.</p>
        </div>
        <div class="sg-card p-5">
            <div class="flex items-center gap-3 mb-4">
                <span class="sg-chip sg-chip--chico sg-chip--verde"><x-sg.icono nombre="banknotes" /></span>
                <h3 class="font-display font-bold text-slate-800 text-sm">Cuotas</h3>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="rounded-xl bg-emerald-50 py-3">
                    <p class="font-display text-2xl font-extrabold text-emerald-600" data-sg-contar>{{ $cuotasPagadas }}</p>
                    <p class="text-[11px] font-semibold text-emerald-700/80">Pagadas</p>
                </div>
                <div class="rounded-xl bg-amber-50 py-3">
                    <p class="font-display text-2xl font-extrabold text-amber-600" data-sg-contar>{{ $cuotasPendientes }}</p>
                    <p class="text-[11px] font-semibold text-amber-700/80">Pendientes</p>
                </div>
                <div class="rounded-xl bg-rose-50 py-3">
                    <p class="font-display text-2xl font-extrabold text-rose-600" data-sg-contar>{{ $cuotasVencidas }}</p>
                    <p class="text-[11px] font-semibold text-rose-700/80">Vencidas</p>
                </div>
            </div>
            @if ($alumno->cuotas->count() > 0)
                <div class="sg-progreso mt-4"><span style="width: {{ round($cuotasPagadas / max($alumno->cuotas->count(), 1) * 100) }}%"></span></div>
                <p class="text-xs text-slate-400 mt-2">{{ $cuotasPagadas }} de {{ $alumno->cuotas->count() }} cuotas abonadas</p>
            @endif
        </div>
    </div>

    @php
        $cuotasOrdenadas = $alumno->cuotas->sortBy([['id_anio_lectivo', 'asc'], ['id_mes', 'asc']]);
        $cuotasAdeudadasCount = $cuotasOrdenadas->where('pagado', false)->count();
        $totalEstimadoAdeudado = $montoActualCuota ? $cuotasAdeudadasCount * (float) $montoActualCuota : null;
    @endphp

    <x-sg.tarjeta titulo="Historial de Cuotas" subtitulo="Todas las cuotas generadas para este alumno, pagadas y pendientes" icono="banknotes" tono="verde" class="mb-6" :cuerpo="false">
        @if ($cuotasAdeudadasCount > 0 && $montoActualCuota)
            <div class="mx-5 mt-4 flex items-center justify-between gap-3 rounded-2xl bg-gradient-to-r from-[#0B3D91] to-[#1565C0] text-white px-5 py-3">
                <span class="text-sm font-semibold text-blue-100">Adeuda {{ $cuotasAdeudadasCount }} cuota{{ $cuotasAdeudadasCount === 1 ? '' : 's' }} × $ {{ number_format($montoActualCuota, 0, ',', '.') }} (monto actual)</span>
                <span class="font-display font-extrabold text-xl text-[#FFD54F]">$ {{ number_format($totalEstimadoAdeudado, 0, ',', '.') }}</span>
            </div>
        @endif
        <div class="overflow-x-auto mt-4">
            <table class="sg-tabla">
                <thead>
                    <tr>
                        <th>Período</th>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Recargo</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha de pago</th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @forelse ($cuotasOrdenadas as $cuota)
                        <tr>
                            <td class="text-slate-500">{{ $cuota->mes->nombre_mes }} {{ $cuota->anioLectivo->anio }}</td>
                            <td class="text-slate-500">{{ $cuota->concepto ?: '—' }}</td>
                            <td class="font-medium text-slate-700">$ {{ number_format($cuota->monto, 0, ',', '.') }}</td>
                            <td>
                                @if ($cuota->recargo > 0)
                                    <span class="sg-badge sg-badge--amarillo sg-badge--sin-punto">+$ {{ number_format($cuota->recargo, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="font-display font-bold text-[#0B3D91]">$ {{ number_format($cuota->montoTotal(), 0, ',', '.') }}</td>
                            <td>
                                @if ($cuota->pagado)
                                    <span class="sg-badge sg-badge--verde">Pagada</span>
                                @elseif ($cuota->vencida)
                                    <span class="sg-badge sg-badge--rojo">Vencida</span>
                                @else
                                    <span class="sg-badge sg-badge--amarillo">Pendiente</span>
                                @endif
                            </td>
                            <td class="text-slate-500">{{ $cuota->fecha_pago ? \App\Support\FechaEsp::corta($cuota->fecha_pago) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-sg.vacio icono="banknotes">Todavía no se generó ninguna cuota para este alumno.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-sg.tarjeta>

    <x-sg.tarjeta titulo="Historial académico" subtitulo="Solo consulta — para cargar o corregir una materia, usá 'Cargar historial previo' desde Alumnos" icono="book" class="mb-6" :cuerpo="false">
        <div class="px-5 pt-4 pb-3 grid sm:grid-cols-2 gap-x-6 gap-y-1.5 text-sm border-b border-slate-100">
            <div class="flex justify-between gap-3"><dt class="text-slate-400">Carrera</dt><dd class="text-slate-700 font-medium text-right">{{ $inscripcion?->carrera?->nombre_carrera ?? '—' }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-slate-400">Resolución del plan de estudios</dt><dd class="text-slate-700 font-medium text-right">{{ $inscripcion?->carrera?->resolucion_ministerial ?? '—' }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-slate-400">DNI</dt><dd class="text-slate-700 font-medium text-right">{{ $alumno->dni }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-slate-400">Año de ingreso a la carrera</dt><dd class="text-slate-700 font-medium text-right">{{ $anioIngreso ?? '—' }}</dd></div>
        </div>

        @if ($alumno->historialAlumno->isEmpty())
            <div class="p-5"><x-sg.vacio icono="book">Sin historial académico cargado.</x-sg.vacio></div>
        @else
            <div class="overflow-x-auto">
                <table class="sg-tabla">
                    <thead>
                        <tr>
                            <th>Materia</th>
                            <th>Año</th>
                            <th>Condición</th>
                            <th class="sg-num">Nota</th>
                            <th>Fecha</th>
                            <th>Libro / Folio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($alumno->historialAlumno->sortBy([['materia.anioCursada.id_anio_cursada', 'asc'], ['materia.nombre', 'asc']]) as $historial)
                            @php
                                $condicionNombre = $historial->condicion->nombre_condicion ?? '—';
                                $aprobada = \App\Models\CondicionAlumno::esAprobada($condicionNombre);
                                $acta = $actasPorMateria->get($historial->id_materia);
                            @endphp
                            <tr>
                                <td>
                                    <span class="flex items-center gap-2.5 min-w-0">
                                        <span @class([
                                                'h-2 w-2 rounded-full shrink-0',
                                                'bg-emerald-500' => $aprobada,
                                                'bg-amber-400' => \App\Models\CondicionAlumno::esRegular($condicionNombre) && ! $historial->regularidad_vencida,
                                                'bg-rose-500' => $historial->regularidad_vencida || $condicionNombre === 'Libre',
                                                'bg-slate-300' => $condicionNombre === 'Pendiente',
                                                'bg-sky-500' => in_array($condicionNombre, ['Cursando', 'Recursando'], true),
                                            ])></span>
                                        <span class="font-semibold text-slate-700">{{ $historial->materia->nombre }}</span>
                                    </span>
                                </td>
                                <td class="text-slate-500">{{ $historial->materia->anioCursada->nombre_anio ?? '—' }}</td>
                                <td>
                                    <span @class([
                                            'sg-badge',
                                            'sg-badge--verde' => $aprobada,
                                            'sg-badge--amarillo' => \App\Models\CondicionAlumno::esRegular($condicionNombre) && ! $historial->regularidad_vencida,
                                            'sg-badge--rojo' => $historial->regularidad_vencida || $condicionNombre === 'Libre',
                                            'sg-badge--gris' => $condicionNombre === 'Pendiente',
                                            'sg-badge--azul' => in_array($condicionNombre, ['Cursando', 'Recursando'], true),
                                        ])>
                                        {{ $historial->regularidad_vencida ? 'Venció' : \App\Models\CondicionAlumno::etiqueta($condicionNombre) }}
                                    </span>
                                    @if (\App\Models\CondicionAlumno::esRegular($condicionNombre) && $historial->fecha_limite_calculada)
                                        <p class="text-[11px] text-slate-400 mt-1">{{ $historial->regularidad_vencida ? 'venció el' : 'vence' }} {{ \App\Support\FechaEsp::corta($historial->fecha_limite_calculada) }}</p>
                                    @endif
                                    @if ($condicionNombre === 'Equivalencia' && ($historial->institucion_equivalencia || $historial->resolucion_equivalencia))
                                        <p class="text-[11px] text-slate-400 mt-1">
                                            {{ $historial->institucion_equivalencia ?: '—' }}
                                            @if ($historial->resolucion_equivalencia)
                                                · Res. {{ $historial->resolucion_equivalencia }}
                                            @endif
                                        </p>
                                    @endif
                                </td>
                                <td class="sg-num font-semibold text-slate-700">{{ $historial->nota_cursada ?? '—' }}</td>
                                <td class="text-slate-500">{{ $aprobada && $historial->fecha_ultima_modificacion ? \App\Support\FechaEsp::corta($historial->fecha_ultima_modificacion) : '—' }}</td>
                                <td class="text-slate-500">{{ $acta ? "{$acta->acta->libro}/{$acta->acta->folio}" : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($alumno->historialAlumno->isNotEmpty())
            <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between text-sm bg-slate-50/60">
                <span class="text-slate-500">{{ $materiasAprobadasTotal }} de {{ $totalMateriasPlan }} materias del plan aprobadas</span>
                <span class="font-display font-bold text-[#0B3D91]">{{ $porcentajeAvance }}% de avance</span>
            </div>
        @endif
    </x-sg.tarjeta>

    <x-sg.tarjeta titulo="Ficha de inscripción" subtitulo="Datos que completó el alumno desde su portal — solo consulta, la edición la hace el propio alumno" icono="document" tono="azul">
        @php $algunaRespuesta = $fichaRespuestas->isNotEmpty(); @endphp
        @if (! $algunaRespuesta)
            <x-sg.vacio icono="document">El alumno todavía no completó su ficha de inscripción.</x-sg.vacio>
        @else
            <div class="grid md:grid-cols-2 gap-x-8 gap-y-5">
                @foreach ($fichaSecciones as $seccion)
                    @continue($seccion->campos->isEmpty())
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">{{ $seccion->nombre }}</p>
                        <dl class="text-sm space-y-2 text-slate-500">
                            @foreach ($seccion->campos as $campo)
                                <div class="flex justify-between gap-4">
                                    <dt class="shrink-0">{{ $campo->etiqueta }}</dt>
                                    <dd class="text-slate-700 text-right">{{ $fichaRespuestas->get($campo->id_campo)?->valor ?? '—' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endforeach
            </div>
        @endif
    </x-sg.tarjeta>
    <script>
        // El turno solo ofrece los turnos en que se dicta la carrera elegida.
        (function () {
            const carrera = document.querySelector('[name="carrera_id"]'), turno = document.querySelector('[name="turno_cursada_id"]');
            if (!carrera || !turno) return;
            function filtrar() {
                const turnos = (carrera.selectedOptions[0]?.dataset.turnos || '').split(',').filter(Boolean);
                [...turno.options].forEach(o => { if (o.value) { o.hidden = turnos.length && !turnos.includes(o.value); o.disabled = o.hidden; } });
                if (turno.selectedOptions[0]?.hidden) turno.value = '';
            }
            carrera.addEventListener('change', filtrar);
            filtrar();
        })();
    </script>
@endsection
