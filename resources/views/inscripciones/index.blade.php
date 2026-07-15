@extends('layouts.portal')

@section('titulo', 'Mis inscripciones')
@section('subtitulo', \App\Support\FechaEsp::larga(now()))

@section('contenido')
    @php
        $badges = [
            'en_proceso' => 'bg-amber-100 text-amber-700',
            'aceptada' => 'bg-green-100 text-green-700',
            'rechazada' => 'bg-red-100 text-red-600',
        ];
        $etiquetas = ['en_proceso' => 'En proceso', 'aceptada' => 'Aceptada', 'rechazada' => 'Rechazada'];
    @endphp

    <div class="bg-white rounded-xl shadow-sm mb-6">
        <div class="border-b border-slate-100 px-6 pt-4">
            <span class="inline-block border-b-2 border-amber-500 text-sm font-semibold text-slate-800 pb-3">Todas</span>
        </div>

        <p class="text-xs text-slate-400 px-6 pt-4">Seguimiento de tus inscripciones a mesas de examen</p>

        <div class="overflow-x-auto">
            <table class="w-full text-sm mt-2">
                <thead>
                    <tr class="text-left text-xs text-slate-400 uppercase">
                        <th class="px-6 py-3 font-semibold">Materia</th>
                        <th class="px-6 py-3 font-semibold">Fecha de examen</th>
                        <th class="px-6 py-3 font-semibold">Fecha de inscripción · Turno/Llamado</th>
                        <th class="px-6 py-3 font-semibold">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($inscripciones as $inscripcion)
                        <tr class="cursor-pointer hover:bg-slate-50 {{ $seleccionada?->id === $inscripcion->id ? 'bg-slate-50' : '' }}"
                            onclick="window.location.href='{{ route('inscripciones.index', ['ver' => $inscripcion->id]) }}'">
                            <td class="px-6 py-4 font-semibold text-slate-700">{{ $inscripcion->mesaExamen->materia->nombre }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ \App\Support\FechaEsp::corta($inscripcion->mesaExamen->fecha_examen) }}</td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ \App\Support\FechaEsp::corta($inscripcion->fecha_inscripcion) }} ·
                                {{ ucwords(str_replace('_', '/', $inscripcion->mesaExamen->turno), ' /') }}
                                - {{ $inscripcion->mesaExamen->llamado === 'primer_llamado' ? '1er' : '2do' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold rounded-full px-3 py-1 {{ $badges[$inscripcion->estado] }}">
                                    {{ $etiquetas[$inscripcion->estado] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-400">Todavía no te inscribiste a ninguna mesa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($seleccionada)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-bold text-slate-800 mb-6">🔍 Detalle — {{ $seleccionada->mesaExamen->materia->nombre }}</h3>

            @php
                $paso = match ($seleccionada->estado) {
                    'en_proceso' => 2,
                    'aceptada' => 3,
                    'rechazada' => 2,
                    default => 1,
                };
                $rechazada = $seleccionada->estado === 'rechazada';
            @endphp

            <div class="flex items-center mb-6">
                @foreach ([1 => 'Inscripción enviada', 2 => $rechazada ? 'Rechazada por secretaría' : 'En revisión por secretaría', 3 => 'Aprobada / habilitada'] as $numero => $texto)
                    <div class="flex-1 flex flex-col items-center text-center">
                        <div @class([
                                'h-9 w-9 rounded-full flex items-center justify-center text-sm font-bold text-white',
                                'bg-green-500' => $numero < $paso || ($numero === $paso && $seleccionada->estado === 'aceptada'),
                                'bg-red-500' => $rechazada && $numero === 2,
                                'bg-amber-500' => $numero === $paso && ! $rechazada && $seleccionada->estado !== 'aceptada',
                                'bg-slate-200 text-slate-400' => $numero > $paso,
                            ])>
                            {{ $numero < $paso || ($numero === $paso && $seleccionada->estado === 'aceptada') ? '✓' : ($rechazada && $numero === 2 ? '✕' : $numero) }}
                        </div>
                        <p class="text-xs mt-2 {{ $numero <= $paso ? 'text-slate-700 font-medium' : 'text-slate-400' }}">{{ $texto }}</p>
                    </div>
                    @if (! $loop->last)
                        <div class="flex-1 h-0.5 -mt-6 {{ $numero < $paso ? 'bg-green-500' : 'bg-slate-200' }}"></div>
                    @endif
                @endforeach
            </div>

            <div @class([
                    'text-sm rounded-lg px-4 py-3',
                    'bg-amber-50 text-amber-700' => $seleccionada->estado === 'en_proceso',
                    'bg-green-50 text-green-700' => $seleccionada->estado === 'aceptada',
                    'bg-red-50 text-red-700' => $seleccionada->estado === 'rechazada',
                ])>
                @if ($seleccionada->estado === 'en_proceso')
                    ⏱ Tu inscripción está siendo revisada por el secretario académico. Te avisaremos por este medio cuando haya una novedad.
                @elseif ($seleccionada->estado === 'aceptada')
                    ✓ Tu inscripción fue aprobada. ¡Éxitos en tu examen!
                @else
                    ✕ Tu inscripción fue rechazada. Comunicate con secretaría para más información.
                @endif
            </div>
        </div>
    @endif
@endsection
