@extends('layouts.admin')

@section('titulo', 'Inscripción a cursada')

@section('contenido')
    <x-sg.encabezado titulo="Inscripción a cursada" subtitulo="Habilitá, por carrera, los períodos en los que los alumnos pueden inscribirse a cursar" eyebrow="Gestión académica" icono="cap" />

    @if (! $anioLectivo)
        <x-sg.tarjeta>
            <x-sg.vacio icono="calendar" titulo="Sin año lectivo activo">No hay ningún año lectivo marcado como "Activo". Activá uno para poder habilitar la inscripción a cursada.</x-sg.vacio>
        </x-sg.tarjeta>
    @elseif (! $carrera)
        <x-sg.tarjeta>
            <x-sg.vacio icono="cap" titulo="Sin carreras">Todavía no hay carreras cargadas.</x-sg.vacio>
        </x-sg.tarjeta>
    @else
        <form method="GET" class="sg-card sg-revelar flex flex-wrap items-end gap-4 px-6 py-5 mb-6">
            <input type="hidden" name="id_anio_cursada" value="{{ $anioCursadaSeleccionada->id_anio_cursada }}">
            <div class="flex-1 min-w-[260px]">
                <label class="sg-label" for="id_carrera">Carrera</label>
                <select name="id_carrera" id="id_carrera" onchange="this.form.submit()" class="sg-input w-full">
                    @foreach ($carreras as $opcion)
                        <option value="{{ $opcion->id_carrera }}" @selected($opcion->id_carrera === $carrera->id_carrera)>{{ $opcion->nombre_carrera }}</option>
                    @endforeach
                </select>
            </div>
            <noscript><button class="sg-btn sg-btn--azul">Ver</button></noscript>
        </form>

        <div class="sg-aviso sg-aviso--info mb-6 sg-revelar">
            <x-sg.icono nombre="info" class="h-5 w-5 shrink-0" />
            <span>Habilitá los períodos en los que los alumnos de <strong>{{ $carrera->nombre_carrera }}</strong> van a poder inscribirse a cursar materias del año lectivo <strong>{{ $anioLectivo->anio }}</strong> — elegí el año del plan dentro de cada tarjeta. Las demás carreras no se modifican.</span>
        </div>

        <div class="grid sm:grid-cols-3 gap-5" data-sg-escalonar>
            @foreach ($periodos as $periodo)
                <div class="sg-card sg-card--hover p-6 overflow-hidden" data-cursada-tarjeta>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="sg-eyebrow mb-1">Período</p>
                            <p class="font-display font-bold text-lg text-slate-800">{{ $periodo->nombre_periodo }}</p>
                        </div>
                        <select class="sg-input sg-input--chico w-auto" data-cursada-selector-anio>
                            @foreach ($periodo->habilitacionesPorAnio as $item)
                                <option value="{{ $item['anioCursada']->id_anio_cursada }}" @selected($item['anioCursada']->id_anio_cursada === $anioCursadaSeleccionada->id_anio_cursada)>{{ $item['anioCursada']->nombre_anio }}</option>
                            @endforeach
                        </select>
                    </div>

                    @foreach ($periodo->habilitacionesPorAnio as $item)
                        @php $abierto = $item['habilitacion']->abierto ?? false; @endphp
                        <div data-cursada-panel-anio="{{ $item['anioCursada']->id_anio_cursada }}" @class(['hidden' => $item['anioCursada']->id_anio_cursada !== $anioCursadaSeleccionada->id_anio_cursada])>
                            <span @class(['absolute inset-x-0 top-0 h-1', 'bg-gradient-to-r from-emerald-400 to-emerald-600' => $abierto, 'bg-slate-200' => ! $abierto])></span>
                            <div class="flex items-center justify-between mb-5">
                                <span @class(['sg-chip sg-chip--grande', 'sg-chip--verde' => $abierto, 'sg-chip--gris' => ! $abierto])>
                                    <x-sg.icono :nombre="$abierto ? 'check-circle' : 'lock'" />
                                </span>
                                <span @class(['sg-badge', 'sg-badge--verde' => $abierto, 'sg-badge--gris' => ! $abierto])>
                                    {{ $abierto ? 'Abierto' : 'Cerrado' }}
                                </span>
                            </div>
                            <form method="POST" action="{{ route('admin.cursada.toggle', $periodo) }}"
                                  onsubmit="return confirm({{ \Illuminate\Support\Js::from($abierto
                                        ? '¿Cerrar la inscripción a cursada de ' . $periodo->nombre_periodo . ' (' . $item['anioCursada']->nombre_anio . ') para ' . $carrera->nombre_carrera . '?'
                                        : '¿Abrir la inscripción a cursada de ' . $periodo->nombre_periodo . ' (' . $item['anioCursada']->nombre_anio . ') para ' . $carrera->nombre_carrera . '?') }});">
                                @csrf @method('PUT')
                                <input type="hidden" name="id_anio_lectivo" value="{{ $anioLectivo->id_anio_lectivo }}">
                                <input type="hidden" name="id_carrera" value="{{ $carrera->id_carrera }}">
                                <input type="hidden" name="id_anio_cursada" value="{{ $item['anioCursada']->id_anio_cursada }}">
                                <input type="hidden" name="abierto" value="{{ $abierto ? '0' : '1' }}">
                                <button type="submit" @class(['sg-btn w-full', 'sg-btn--peligro' => $abierto, 'sg-btn--azul' => ! $abierto])>
                                    <x-sg.icono :nombre="$abierto ? 'lock' : 'bolt'" />
                                    {{ $abierto ? 'Cerrar inscripción' : 'Abrir inscripción' }}
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <script>
            const inputAnioCarreraForm = document.querySelector('input[name="id_anio_cursada"]');
            document.querySelectorAll('[data-cursada-tarjeta]').forEach((tarjeta) => {
                const selector = tarjeta.querySelector('[data-cursada-selector-anio]');
                selector?.addEventListener('change', () => {
                    tarjeta.querySelectorAll('[data-cursada-panel-anio]').forEach((panel) => {
                        panel.classList.toggle('hidden', panel.dataset.cursadaPanelAnio !== selector.value);
                    });
                    // Para que, si después cambiás de carrera, se mantenga este mismo año seleccionado.
                    if (inputAnioCarreraForm) inputAnioCarreraForm.value = selector.value;
                });
            });
        </script>
    @endif
@endsection
