@extends('layouts.admin')

@section('titulo', 'Panel principal')

@section('contenido')
    @php
        $acciones = [
            ['ruta' => 'admin.mesas.create', 'titulo' => 'Nueva mesa de examen', 'texto' => 'Crear turno, asignar docente y materia', 'icono' => 'clipboard', 'tono' => 'azul'],
            ['ruta' => 'admin.carreras.index', 'titulo' => 'Gestionar planes', 'texto' => 'Editar materias, correlativas y carreras', 'icono' => 'cap', 'tono' => 'amarillo'],
            ['ruta' => 'admin.actas.index', 'titulo' => 'Emitir actas', 'texto' => 'Generar actas autocompletadas por mesa', 'icono' => 'document-check', 'tono' => 'verde'],
            ['ruta' => 'admin.alumnos.create', 'titulo' => 'Inscribir alumno', 'texto' => 'Verificar cuotas y correlativas', 'icono' => 'user-plus', 'tono' => 'violeta'],
        ];
        $iconoActividad = ['👤' => ['user', 'azul'], '💰' => ['cash', 'verde'], '📄' => ['document', 'amarillo'], '✅' => ['check-circle', 'celeste']];
    @endphp

    <x-sg.hero saludo="Buen día, {{ e(auth()->user()->nombre) }} <span class='sg-saludo-mano'>👋</span>">
        {{ \App\Support\FechaEsp::larga(now()) }} · Este es el resumen de la Secretaría para hoy.
        <x-slot:acciones>
            <a href="{{ route('admin.mesas.create') }}" class="sg-btn bg-white/10 text-white border-white/25 hover:bg-white/20">
                <x-sg.icono nombre="clipboard" /> Nueva mesa
            </a>
            <a href="{{ route('admin.alumnos.create') }}" class="sg-btn sg-btn--primario">
                <x-sg.icono nombre="user-plus" /> Inscribir alumno
            </a>
        </x-slot:acciones>
        <x-slot:extra>
            @foreach ([
                ['Alumnos activos', $alumnosActivos, 'users', null],
                ['Carreras', $carreras, 'cap', null],
                ['Mesas hoy', $mesasHoy, 'calendar', "{$mesasEnCurso} en curso"],
                ['Actas por generar', $actasPorGenerar, 'document', null],
            ] as [$etiqueta, $valor, $icono, $detalle])
                <div class="sg-hero__mini flex items-center gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/10 text-[#8FD3F8]"><x-sg.icono :nombre="$icono" /></span>
                    <div class="min-w-0">
                        <p class="font-display text-2xl font-extrabold leading-none" data-sg-contar>{{ $valor }}</p>
                        <p class="text-xs text-blue-100/80 mt-1 truncate">{{ $etiqueta }}@if ($detalle) <span class="text-[#FFD54F] font-semibold">· {{ $detalle }}</span>@endif</p>
                    </div>
                </div>
            @endforeach
        </x-slot:extra>
    </x-sg.hero>

    @if ($alertasCount > 0)
        <x-sg.banner-alertas :cantidad="$alertasCount" :destacadas="$alertasDestacadas" :href="route('admin.alertas.index')" />
    @endif

    <div class="sg-seccion-titulo sg-revelar">
        <span class="sg-chip sg-chip--chico sg-chip--amarillo"><x-sg.icono nombre="bolt" /></span>
        <h2>Acciones rápidas</h2>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5 mb-10" data-sg-escalonar>
        @foreach ($acciones as $accion)
            <a href="{{ route($accion['ruta']) }}" class="group sg-card sg-card--hover p-5 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <span class="sg-chip sg-chip--grande sg-chip--{{ $accion['tono'] }}"><x-sg.icono :nombre="$accion['icono']" /></span>
                    <x-sg.icono nombre="arrow-right" class="h-4 w-4 text-slate-300 transition-all group-hover:text-[#0B3D91] group-hover:translate-x-1" />
                </div>
                <div>
                    <p class="font-display font-bold text-[15px] text-slate-800 group-hover:text-[#0B3D91] transition-colors">{{ $accion['titulo'] }}</p>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $accion['texto'] }}</p>
                </div>
            </a>
        @endforeach
        <div class="sg-card p-5 flex flex-col gap-4 border-dashed bg-slate-50/60 shadow-none">
            <span class="sg-chip sg-chip--grande sg-chip--gris"><x-sg.icono nombre="user-minus" /></span>
            <div>
                <p class="font-display font-bold text-[15px] text-slate-600">Baja de alumno</p>
                <p class="text-xs text-slate-500 mt-1">Proceso exclusivo de Secretaría</p>
                <span class="sg-badge sg-badge--rojo mt-2.5">Solo Secretaría</span>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-5 items-start">
        <x-sg.tarjeta titulo="Mesas de hoy" subtitulo="Exámenes programados para la jornada" icono="calendar" class="lg:col-span-2" :cuerpo="false">
            <x-slot:acciones>
                <a href="{{ route('admin.mesas.index') }}" class="sg-link">Ver todas <x-sg.icono nombre="arrow-right" /></a>
            </x-slot:acciones>
            <div class="sg-lista sg-filas">
                @forelse ($mesasDeHoy as $mesa)
                    <div class="sg-fila sg-fila--hover">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-[#FFF8E1] text-[#A87400]">
                            <x-sg.icono nombre="clipboard" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-800 truncate">{{ $mesa->materia->nombre }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ \App\Support\FechaEsp::corta($mesa->fecha_examen) }}</p>
                        </div>
                        <span class="sg-badge sg-badge--amarillo">Hoy</span>
                    </div>
                @empty
                    <x-sg.vacio icono="calendar" titulo="Día tranquilo">No hay mesas programadas.</x-sg.vacio>
                @endforelse
            </div>
        </x-sg.tarjeta>

        <x-sg.tarjeta titulo="Actividad reciente" subtitulo="Últimos movimientos del sistema" icono="clock" tono="celeste" class="lg:col-span-3" :cuerpo="false">
            <div class="px-6 py-3">
                <ol class="relative sg-filas">
                    @forelse ($actividad as $evento)
                        @php [$ico, $tono] = $iconoActividad[$evento['icono']] ?? ['info', 'gris']; @endphp
                        <li class="relative flex gap-4 pb-5 last:pb-2">
                            @unless ($loop->last)
                                <span class="absolute left-[19px] top-10 bottom-0 w-px bg-gradient-to-b from-slate-200 to-transparent"></span>
                            @endunless
                            <span class="sg-chip sg-chip--{{ $tono }} relative z-10"><x-sg.icono :nombre="$ico" class="h-[18px] w-[18px]" /></span>
                            <div class="min-w-0 pt-1">
                                <p class="text-sm text-slate-700 leading-snug">{{ $evento['titulo'] }}</p>
                                @if (! empty($evento['fecha']))
                                    <p class="text-xs text-slate-400 mt-1">{{ \Illuminate\Support\Carbon::parse($evento['fecha'])->format('d/m/Y') }}</p>
                                @endif
                            </div>
                        </li>
                    @empty
                        <x-sg.vacio icono="clock">Todavía no hay actividad registrada.</x-sg.vacio>
                    @endforelse
                </ol>
            </div>
        </x-sg.tarjeta>
    </div>
@endsection
