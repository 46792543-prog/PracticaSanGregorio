@extends('layouts.portal')

@section('titulo', 'Mis inscripciones')
@section('subtitulo', \App\Support\FechaEsp::larga(now()))

@section('contenido')
    @php
        $badges = [
            'En proceso' => 'bg-amber-100 text-amber-700',
            'Aceptado' => 'bg-green-100 text-green-700',
            'Rechazado' => 'bg-red-100 text-red-600',
        ];
        $etiquetas = ['En proceso' => 'En proceso', 'Aceptado' => 'Aceptada', 'Rechazado' => 'Rechazada'];
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
                        <tr class="cursor-pointer hover:bg-slate-50 {{ $seleccionada?->id_inscripcion === $inscripcion->id_inscripcion ? 'bg-slate-50' : '' }}"
                            onclick="window.location.href='{{ route('inscripciones.index', ['ver' => $inscripcion->id_inscripcion]) }}'">
                            <td class="px-6 py-4 font-semibold text-slate-700">{{ $inscripcion->mesaExamen->materia->nombre }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ \App\Support\FechaEsp::corta($inscripcion->mesaExamen->fecha_examen) }}</td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ \App\Support\FechaEsp::corta($inscripcion->fecha_inscripcion) }} ·
                                {{ $inscripcion->mesaExamen->turnoExamen->nombre_turno }}
                                - {{ $inscripcion->mesaExamen->llamadoExamen->nombre_llamado }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold rounded-full px-3 py-1 {{ $badges[$inscripcion->estadoInscripcion->nombre_estado] ?? 'bg-slate-100 text-slate-500' }}">
                                    {{ $etiquetas[$inscripcion->estadoInscripcion->nombre_estado] ?? $inscripcion->estadoInscripcion->nombre_estado }}
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
        @php $estadoNombre = $seleccionada->estadoInscripcion->nombre_estado; @endphp
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-bold text-slate-800 mb-6">🔍 Detalle — {{ $seleccionada->mesaExamen->materia->nombre }}</h3>

            @php
                $paso = match ($estadoNombre) {
                    'En proceso' => 2,
                    'Aceptado' => 3,
                    'Rechazado' => 2,
                    default => 1,
                };
                $rechazada = $estadoNombre === 'Rechazado';
            @endphp

            <div class="flex items-center mb-6">
                @foreach ([1 => 'Inscripción enviada', 2 => $rechazada ? 'Rechazada por secretaría' : 'En revisión por secretaría', 3 => 'Aprobada / habilitada'] as $numero => $texto)
                    <div class="flex-1 flex flex-col items-center text-center">
                        <div @class([
                                'h-9 w-9 rounded-full flex items-center justify-center text-sm font-bold text-white',
                                'bg-green-500' => $numero < $paso || ($numero === $paso && $estadoNombre === 'Aceptado'),
                                'bg-red-500' => $rechazada && $numero === 2,
                                'bg-amber-500' => $numero === $paso && ! $rechazada && $estadoNombre !== 'Aceptado',
                                'bg-slate-200 text-slate-400' => $numero > $paso,
                            ])>
                            {{ $numero < $paso || ($numero === $paso && $estadoNombre === 'Aceptado') ? '✓' : ($rechazada && $numero === 2 ? '✕' : $numero) }}
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
                    'bg-amber-50 text-amber-700' => $estadoNombre === 'En proceso',
                    'bg-green-50 text-green-700' => $estadoNombre === 'Aceptado',
                    'bg-red-50 text-red-700' => $estadoNombre === 'Rechazado',
                ])>
                @if ($estadoNombre === 'En proceso')
                    ⏱ Tu inscripción está siendo revisada por el secretario académico. Te avisaremos por este medio cuando haya una novedad.
                @elseif ($estadoNombre === 'Aceptado')
                    ✓ Tu inscripción fue aprobada. ¡Éxitos en tu examen!
                @else
                    ✕ Tu inscripción fue rechazada. Comunicate con secretaría para más información.
                @endif
            </div>

            @if ($seleccionada->resultado)
                <div class="mt-4 flex items-center gap-3 text-sm">
                    <span class="font-semibold text-slate-600">Resultado del examen:</span>
                    <span @class([
                            'text-xs font-semibold rounded-full px-3 py-1',
                            'bg-green-100 text-green-700' => $seleccionada->resultado === 'aprobado',
                            'bg-red-100 text-red-600' => $seleccionada->resultado === 'desaprobado',
                            'bg-slate-100 text-slate-500' => $seleccionada->resultado === 'ausente',
                        ])>
                        {{ ucfirst($seleccionada->resultado) }}
                    </span>
                    @if ($seleccionada->nota_examen)
                        <span class="text-slate-500">Nota: {{ $seleccionada->nota_examen }}</span>
                    @endif
                </div>
            @endif
        </div>
    @endif
@endsection
