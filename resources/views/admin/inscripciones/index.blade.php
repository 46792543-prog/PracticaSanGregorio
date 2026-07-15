@extends('layouts.admin')

@section('titulo', 'Inscripciones a mesas de examen')

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">📋 Inscripciones a mesas de examen</h1>
        <p class="text-sm text-slate-400">Aprobá o rechazá las inscripciones enviadas por los alumnos</p>
    </div>

    <div class="flex gap-2 mb-6">
        @foreach (['en_proceso' => 'Pendientes', 'aceptada' => 'Aceptadas', 'rechazada' => 'Rechazadas', 'todas' => 'Todas'] as $valor => $texto)
            <a href="{{ route('admin.inscripciones.index', ['estado' => $valor]) }}"
               class="text-sm font-semibold px-4 py-2 rounded-lg {{ $filtro === $valor ? 'bg-[#1E4D8C] text-white' : 'bg-white text-slate-600 shadow-sm' }}">
                {{ $texto }}
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-400 uppercase bg-slate-50">
                    <th class="px-6 py-3 font-semibold">Alumno</th>
                    <th class="px-6 py-3 font-semibold">Materia</th>
                    <th class="px-6 py-3 font-semibold">Fecha inscripción</th>
                    <th class="px-6 py-3 font-semibold">Estado</th>
                    <th class="px-6 py-3 font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($inscripciones as $inscripcion)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-700">{{ $inscripcion->user->nombre }} {{ $inscripcion->user->apellido }}</p>
                            <p class="text-xs text-slate-400">DNI {{ $inscripcion->user->dni }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-slate-700">{{ $inscripcion->mesaExamen->materia->nombre }}</p>
                            <p class="text-xs text-slate-400">{{ $inscripcion->mesaExamen->materia->carrera->nombre }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ \App\Support\FechaEsp::corta($inscripcion->fecha_inscripcion) }}</td>
                        <td class="px-6 py-4">
                            <span @class([
                                    'text-xs font-semibold rounded-full px-3 py-1',
                                    'bg-amber-100 text-amber-700' => $inscripcion->estado === 'en_proceso',
                                    'bg-green-100 text-green-700' => $inscripcion->estado === 'aceptada',
                                    'bg-red-100 text-red-600' => $inscripcion->estado === 'rechazada',
                                ])>
                                {{ ['en_proceso' => 'Pendiente', 'aceptada' => 'Aceptada', 'rechazada' => 'Rechazada'][$inscripcion->estado] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if ($inscripcion->estado === 'en_proceso')
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('admin.inscripciones.actualizar', $inscripcion) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="estado" value="aceptada">
                                        <button class="rounded-lg bg-green-600 text-white text-xs font-semibold px-3 py-1.5">Aceptar</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.inscripciones.actualizar', $inscripcion) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="estado" value="rechazada">
                                        <button class="rounded-lg bg-red-500 text-white text-xs font-semibold px-3 py-1.5">Rechazar</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400">No hay inscripciones para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
