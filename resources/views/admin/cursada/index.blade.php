@extends('layouts.admin')

@section('titulo', 'Inscripción a cursada')

@section('contenido')
    <x-sg.encabezado titulo="Inscripción a cursada" subtitulo="Habilitá los períodos en los que los alumnos pueden inscribirse a cursar" eyebrow="Gestión académica" icono="cap" />

    @if (! $anioLectivo)
        <x-sg.tarjeta>
            <x-sg.vacio icono="calendar" titulo="Sin año lectivo activo">No hay ningún año lectivo marcado como "Activo". Activá uno para poder habilitar la inscripción a cursada.</x-sg.vacio>
        </x-sg.tarjeta>
    @else
        <div class="sg-aviso sg-aviso--info mb-6 sg-revelar">
            <x-sg.icono nombre="info" class="h-5 w-5 shrink-0" />
            <span>Habilitá los períodos en los que los alumnos van a poder inscribirse a cursar materias del año lectivo <strong>{{ $anioLectivo->anio }}</strong>. Se aplica a todas las carreras a la vez.</span>
        </div>

        <div class="grid sm:grid-cols-3 gap-5" data-sg-escalonar>
            @foreach ($periodos as $periodo)
                @php $abierto = $periodo->habilitacion->abierto ?? false; @endphp
                <div @class(['sg-card sg-card--hover p-6 overflow-hidden', 'ring-2 ring-emerald-200' => $abierto])>
                    <span @class(['absolute inset-x-0 top-0 h-1', 'bg-gradient-to-r from-emerald-400 to-emerald-600' => $abierto, 'bg-slate-200' => ! $abierto])></span>
                    <div class="flex items-center justify-between mb-5">
                        <span @class(['sg-chip sg-chip--grande', 'sg-chip--verde' => $abierto, 'sg-chip--gris' => ! $abierto])>
                            <x-sg.icono :nombre="$abierto ? 'check-circle' : 'lock'" />
                        </span>
                        <span @class(['sg-badge', 'sg-badge--verde' => $abierto, 'sg-badge--gris' => ! $abierto])>
                            {{ $abierto ? 'Abierto' : 'Cerrado' }}
                        </span>
                    </div>
                    <p class="sg-eyebrow mb-1">Período</p>
                    <p class="font-display font-bold text-lg text-slate-800 mb-5">{{ $periodo->nombre_periodo }}</p>
                    <form method="POST" action="{{ route('admin.cursada.toggle', $periodo) }}"
                          onsubmit="return confirm('{{ $abierto ? '¿Cerrar la inscripción a cursada de este período?' : '¿Abrir la inscripción a cursada de este período para todas las carreras?' }}');">
                        @csrf @method('PUT')
                        <input type="hidden" name="id_anio_lectivo" value="{{ $anioLectivo->id_anio_lectivo }}">
                        <input type="hidden" name="abierto" value="{{ $abierto ? '0' : '1' }}">
                        <button type="submit" @class(['sg-btn w-full', 'sg-btn--peligro' => $abierto, 'sg-btn--azul' => ! $abierto])>
                            <x-sg.icono :nombre="$abierto ? 'lock' : 'bolt'" />
                            {{ $abierto ? 'Cerrar inscripción' : 'Abrir inscripción' }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
@endsection
