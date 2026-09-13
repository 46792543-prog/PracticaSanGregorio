@extends('layouts.admin')

@section('titulo', 'Inscripción a cursada')

@section('contenido')
    <p class="text-sm text-slate-400 mb-2">Gestión académica / Inscripción a cursada</p>

    @if (! $anioLectivo)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <p class="text-sm text-slate-500">No hay ningún año lectivo marcado como "Activo". Activá uno para poder habilitar la inscripción a cursada.</p>
        </div>
    @else
        <p class="text-sm text-slate-500 mb-6">Habilitá los períodos en los que los alumnos van a poder inscribirse a cursar materias del año lectivo <strong class="text-slate-700">{{ $anioLectivo->anio }}</strong>. Se aplica a todas las carreras a la vez.</p>

        <div class="grid sm:grid-cols-3 gap-4">
            @foreach ($periodos as $periodo)
                @php $abierto = $periodo->habilitacion->abierto ?? false; @endphp
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">{{ $periodo->nombre_periodo }}</p>
                    <span @class([
                            'text-xs font-semibold rounded-full px-3 py-1 inline-block mb-4',
                            'bg-green-100 text-green-700' => $abierto,
                            'bg-slate-100 text-slate-500' => ! $abierto,
                        ])>
                        {{ $abierto ? 'Abierto' : 'Cerrado' }}
                    </span>
                    <form method="POST" action="{{ route('admin.cursada.toggle', $periodo) }}"
                          onsubmit="return confirm('{{ $abierto ? '¿Cerrar la inscripción a cursada de este período?' : '¿Abrir la inscripción a cursada de este período para todas las carreras?' }}');">
                        @csrf @method('PUT')
                        <input type="hidden" name="id_anio_lectivo" value="{{ $anioLectivo->id_anio_lectivo }}">
                        <input type="hidden" name="abierto" value="{{ $abierto ? '0' : '1' }}">
                        <button type="submit" @class([
                                'w-full rounded-xl text-sm font-semibold px-4 py-2.5 transition',
                                'bg-red-50 text-red-600 hover:bg-red-100' => $abierto,
                                'bg-[#1E4D8C] text-white shadow-sm hover:shadow-md' => ! $abierto,
                            ])>
                            {{ $abierto ? 'Cerrar inscripción' : 'Abrir inscripción' }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
@endsection
