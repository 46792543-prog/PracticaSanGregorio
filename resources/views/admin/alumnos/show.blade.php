@extends('layouts.admin')

@section('titulo', 'Ficha del alumno')

@section('contenido')
    @php
        $inscripcion = $alumno->inscripcionesCarrera->first();
        $estadoInscNombre = $inscripcion?->estadoInscripcion?->nombre_estado;
        $cuotasPagadas = $alumno->cuotas->where('pagado', true)->count();
        $cuotasPendientes = $alumno->cuotas->where('pagado', false)->count();
        $cuotasVencidas = $alumno->cuotas->filter(fn ($c) => $c->vencida)->count();
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
                @if ($inscripcion && $inscripcion->estadoInscripcion->nombre_estado === 'Activo')
                    <a href="{{ route('admin.alumnos.materias', $alumno) }}" class="sg-btn sg-btn--azul sg-btn--chico"><x-sg.icono nombre="cap" /> Inscribir a materias</a>
                @endif
                @if ($progresoPromocion)
                    <form method="POST" action="{{ route('admin.alumnos.promover', $alumno) }}"
                          onsubmit="return confirm('¿Promover a {{ $alumno->nombre }} {{ $alumno->apellido }} a {{ $progresoPromocion['proximoAnio']->nombre_anio }}?');">
                        @csrf @method('PUT')
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
                    <form method="POST" action="{{ route('admin.alumnos.baja', $alumno) }}" onsubmit="return confirm('¿Confirmás dar de baja a este alumno?');">
                        @csrf @method('PUT')
                        <button type="submit" class="sg-btn sg-btn--peligro sg-btn--chico"><x-sg.icono nombre="user-minus" /> Dar de baja</button>
                    </form>
                @elseif ($inscripcion && $inscripcion->estadoInscripcion->nombre_estado === 'Baja')
                    <form method="POST" action="{{ route('admin.alumnos.alta', $alumno) }}" onsubmit="return confirm('¿Confirmás dar de alta nuevamente a este alumno?');">
                        @csrf @method('PUT')
                        <button type="submit" class="sg-btn sg-btn--verde sg-btn--chico"><x-sg.icono nombre="user-plus" /> Dar de alta</button>
                    </form>
                @endif
            </div>
        </div>
    </section>

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
                <div class="flex justify-between"><dt class="text-slate-400">Fecha de ingreso</dt><dd class="text-slate-700 font-medium">{{ $inscripcion?->fecha_inscripcion ? \App\Support\FechaEsp::corta($inscripcion->fecha_inscripcion) : '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Año lectivo</dt><dd class="text-slate-700 font-medium">{{ $inscripcion?->anioLectivo?->anio ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Año que cursa</dt><dd class="text-slate-700 font-medium">{{ $inscripcion?->anioCursada?->nombre_anio ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Turno</dt><dd class="text-slate-700 font-medium">{{ $inscripcion?->turnoCursada?->nombre_turno ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Condición</dt><dd class="text-slate-700 font-medium">{{ $inscripcion?->condicion?->nombre_condicion ?? '—' }}</dd></div>
                <div class="flex justify-between items-center"><dt class="text-slate-400">Estado</dt><dd>
                    <span @class(['sg-badge', 'sg-badge--verde' => $estadoInscNombre === 'Activo', 'sg-badge--rojo' => $estadoInscNombre === 'Baja'])>
                        {{ $estadoInscNombre ?? '—' }}
                    </span>
                </dd></div>
            </dl>
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

    <x-sg.tarjeta titulo="Historial académico" subtitulo="Solo consulta — para cargar o corregir una materia, usá 'Cargar historial previo' desde Alumnos" icono="book" class="mb-6" :cuerpo="false">
        <div class="px-5 pt-4 pb-3 grid sm:grid-cols-2 gap-x-6 gap-y-1.5 text-sm border-b border-slate-100">
            <div class="flex justify-between gap-3"><dt class="text-slate-400">Carrera</dt><dd class="text-slate-700 font-medium text-right">{{ $inscripcion?->carrera?->nombre_carrera ?? '—' }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-slate-400">Resolución del plan de estudios</dt><dd class="text-slate-700 font-medium text-right">{{ $inscripcion?->carrera?->resolucion_ministerial ?? '—' }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-slate-400">DNI</dt><dd class="text-slate-700 font-medium text-right">{{ $alumno->dni }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-slate-400">Año de ingreso a la carrera</dt><dd class="text-slate-700 font-medium text-right">{{ $anioIngreso ?? '—' }}</dd></div>
        </div>

        <div class="sg-lista sg-filas">
            @forelse ($alumno->historialAlumno->sortBy('materia.nombre') as $historial)
                @php
                    $condicionNombre = $historial->condicion->nombre_condicion ?? '—';
                    $aprobada = \App\Models\CondicionAlumno::esAprobada($condicionNombre);
                    $acta = $actasPorMateria->get($historial->id_materia);
                @endphp
                <div class="sg-fila flex-wrap justify-between text-sm">
                    <span class="flex items-center gap-3 min-w-0">
                        <span @class([
                                'h-2.5 w-2.5 rounded-full shrink-0',
                                'bg-emerald-500' => $aprobada,
                                'bg-amber-400' => $condicionNombre === 'Regular' && ! $historial->regularidad_vencida,
                                'bg-rose-500' => $historial->regularidad_vencida || $condicionNombre === 'Libre',
                                'bg-slate-300' => $condicionNombre === 'Pendiente',
                                'bg-sky-500' => $condicionNombre === 'Cursando',
                            ])></span>
                        <span class="font-semibold text-slate-700">{{ $historial->materia->nombre }}</span>
                    </span>
                    <div class="flex items-center gap-4 flex-wrap text-xs text-slate-500">
                        @if ($historial->nota_cursada)
                            <span>Nota: <span class="font-semibold text-slate-700">{{ $historial->nota_cursada }}</span></span>
                        @endif
                        @if ($aprobada && $historial->fecha_ultima_modificacion)
                            <span>Fecha: {{ \App\Support\FechaEsp::corta($historial->fecha_ultima_modificacion) }}</span>
                        @endif
                        <span>Libro/Folio: {{ $acta ? "{$acta->acta->libro}/{$acta->acta->folio}" : '—' }}</span>
                        @if ($condicionNombre === 'Regular' && $historial->fecha_limite_calculada)
                            <span>(vence {{ \App\Support\FechaEsp::corta($historial->fecha_limite_calculada) }})</span>
                        @endif
                        <span @class([
                                'sg-badge',
                                'sg-badge--verde' => $aprobada,
                                'sg-badge--amarillo' => $condicionNombre === 'Regular' && ! $historial->regularidad_vencida,
                                'sg-badge--rojo' => $historial->regularidad_vencida || $condicionNombre === 'Libre',
                                'sg-badge--gris' => $condicionNombre === 'Pendiente',
                                'sg-badge--azul' => $condicionNombre === 'Cursando',
                            ])>
                            {{ $historial->regularidad_vencida ? 'Venció el ' . \App\Support\FechaEsp::corta($historial->fecha_limite_calculada) : $condicionNombre }}
                        </span>
                    </div>
                </div>
            @empty
                <x-sg.vacio icono="book">Sin historial académico cargado.</x-sg.vacio>
            @endforelse
        </div>

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
@endsection
