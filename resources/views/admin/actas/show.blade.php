@extends('layouts.admin')

@section('titulo', 'Ver acta')
@section('subtitulo', 'Gestión académica / Mesas de examen')

@section('contenido')
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <a href="{{ route('admin.mesas.index', ['turno' => $mesa->id_turno]) }}" class="text-sm text-[#1E4D8C] font-semibold hover:underline">← Volver a mesas de examen</a>
        <div class="flex items-center gap-3">
            <span @class([
                    'text-xs font-semibold rounded-full px-3 py-1',
                    'bg-emerald-100 text-emerald-700' => ($acta?->estado ?? null) === 'generada',
                    'bg-amber-100 text-amber-700' => ($acta?->estado ?? null) === 'borrador',
                    'bg-slate-100 text-slate-500' => ! $acta,
                ])>
                {{ match ($acta?->estado) { 'generada' => 'Acta generada', 'borrador' => 'Borrador guardado', default => 'Todavía no se cargó el acta' } }}
            </span>
            <a href="{{ route('admin.mesas.acta.pdf', $mesa) }}" target="_blank"
               class="rounded-lg bg-[#D4A017] hover:brightness-95 transition text-white font-semibold text-xs px-4 py-2">
                📄 Descargar / imprimir PDF
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <div class="flex items-center gap-3 border-b border-slate-800 pb-4 mb-4">
            <span class="h-10 w-10 rounded-full bg-[#1E4D8C] text-white grid place-items-center font-bold text-xs">ISG</span>
            <div>
                <p class="font-bold text-slate-800">Instituto Superior San Gregorio</p>
                <p class="text-xs text-slate-400">San Pedro de Jujuy — Pedro Goyena 33 — Tel. 03888-480686</p>
            </div>
        </div>

        <div class="flex justify-between items-center mb-4 text-sm">
            <div class="flex gap-6">
                <span>L: <strong>{{ $acta->libro ?? '—' }}</strong></span>
                <span>F: <strong>{{ $acta->folio ?? '—' }}</strong></span>
            </div>
            <span class="border border-slate-300 rounded px-3 py-1 text-sm">{{ \App\Support\FechaEsp::corta($mesa->fecha_examen) }}</span>
        </div>

        <h3 class="text-center font-bold text-slate-800 mb-4">ACTA DE EXÁMENES {{ mb_strtoupper($mesa->turnoExamen->nombre_turno) }} {{ $mesa->anioLectivo->anio }}</h3>

        <p class="text-sm mb-1"><strong>Carrera:</strong> {{ $mesa->materia->carrera->nombre }}.</p>
        <p class="text-sm mb-1"><strong>Asignatura:</strong> {{ mb_strtoupper($mesa->materia->nombre) }}</p>
        <p class="text-sm mb-1"><strong>Llamado:</strong> {{ $mesa->llamadoExamen->nombre_llamado }}</p>
        <p class="text-sm mb-4"><strong>Examen de Alumnos:</strong> REGULAR</p>

        <table class="w-full text-xs border-collapse mb-4">
            <thead>
                <tr class="bg-slate-50">
                    <th class="border border-slate-300 px-2 py-1">N°</th>
                    <th class="border border-slate-300 px-2 py-1">Documento</th>
                    <th class="border border-slate-300 px-3 py-1 text-left">Apellido y Nombre</th>
                    <th class="border border-slate-300 px-2 py-1" colspan="3">Calificación</th>
                </tr>
                <tr class="bg-slate-50 text-slate-500">
                    <th class="border border-slate-300"></th>
                    <th class="border border-slate-300"></th>
                    <th class="border border-slate-300"></th>
                    <th class="border border-slate-300 px-2 py-1">Escrito</th>
                    <th class="border border-slate-300 px-2 py-1">Oral</th>
                    <th class="border border-slate-300 px-2 py-1">Resultado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inscripcionesAceptadas as $i => $inscripcion)
                    @php $detalle = $acta?->detalles->firstWhere('id_persona_alumno', $inscripcion->id_persona_alumno); @endphp
                    <tr>
                        <td class="border border-slate-300 text-center py-1">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="border border-slate-300 text-center">DNI {{ $inscripcion->personaAlumno->dni }}</td>
                        <td class="border border-slate-300 px-3">{{ mb_strtoupper($inscripcion->personaAlumno->apellido) }}, {{ mb_strtoupper($inscripcion->personaAlumno->nombre) }}</td>
                        <td class="border border-slate-300 text-center py-1">{{ $detalle->nota_escrito ?? '—' }}</td>
                        <td class="border border-slate-300 text-center py-1">{{ $detalle->nota_oral ?? '—' }}</td>
                        <td class="border border-slate-300 text-center py-1 font-semibold">
                            @if ($detalle?->nota_final)
                                {{ $detalle->nota_final }} · {{ ucfirst($detalle->resultado ?: '—') }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="border border-slate-300 text-center py-4 text-slate-400">Nadie se inscribió a esta mesa todavía.</td></tr>
                @endforelse
            </tbody>
        </table>

        <label class="block text-xs font-semibold text-slate-500 mb-1">OBSERVACIONES:</label>
        <p class="text-sm text-slate-600 border-b border-slate-200 pb-2 mb-6 min-h-[1.5rem]">{{ $acta->observaciones ?? '—' }}</p>

        <div class="flex justify-end gap-8 text-sm mb-8">
            <p>TOTAL DE ALUMNOS: <strong>{{ $inscripcionesAceptadas->count() }}</strong></p>
            <p>APROBADOS: <strong>{{ $acta?->detalles->where('resultado', 'aprobado')->count() ?: '—' }}</strong></p>
            <p>DESAPROBADOS: <strong>{{ $acta?->detalles->where('resultado', 'desaprobado')->count() ?: '—' }}</strong></p>
            <p>AUSENTES: <strong>{{ $acta?->detalles->where('resultado', 'ausente')->count() ?: '—' }}</strong></p>
        </div>

        <div class="grid grid-cols-3 gap-6 text-center text-sm">
            @foreach (['Presidente', 'Vocal 1', 'Vocal 2'] as $rol)
                <div class="border-t border-slate-400 pt-2">
                    <p class="font-semibold text-slate-700">
                        {{ isset($tribunalPorRol[$rol]) ? $tribunalPorRol[$rol]->profesor->apellido . ', ' . $tribunalPorRol[$rol]->profesor->nombre : '—' }}
                    </p>
                    <p class="text-xs text-slate-400">{{ $rol }}</p>
                </div>
            @endforeach
        </div>

        @if ($acta?->fecha_generacion)
            <p class="text-center text-xs text-slate-400 mt-6">Generada el {{ \App\Support\FechaEsp::corta($acta->fecha_generacion) }}</p>
        @endif
    </div>
@endsection
