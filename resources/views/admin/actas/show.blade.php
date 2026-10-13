@extends('layouts.admin')

@section('titulo', 'Ver acta')
@section('subtitulo', 'Gestión académica / Mesas de examen')

@section('contenido')
    <x-sg.encabezado titulo="Acta de examen" :subtitulo="$mesa->materia->nombre . ' · ' . $mesa->llamadoExamen->nombre_llamado" eyebrow="Mesas de examen" icono="document">
        <a href="{{ route('admin.mesas.index', ['turno' => $mesa->id_turno]) }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="arrow-left" /> Volver a mesas</a>
        <div class="flex items-center gap-3">
            <span @class([
                    'sg-badge',
                    'sg-badge--verde' => ($acta?->estado ?? null) === 'generada',
                    'sg-badge--amarillo' => ($acta?->estado ?? null) === 'borrador',
                    'sg-badge--gris' => ! $acta,
                ])>
                {{ match ($acta?->estado) { 'generada' => 'Acta generada', 'borrador' => 'Borrador guardado', default => 'Todavía no se cargó el acta' } }}
            </span>
            <a href="{{ route('admin.mesas.acta.pdf', $mesa) }}" target="_blank"
               class="sg-btn sg-btn--primario sg-btn--chico">
                <x-sg.icono nombre="printer" /> Descargar / imprimir PDF
            </a>
        </div>
    </x-sg.encabezado>

    <div class="sg-revelar rounded-3xl bg-gradient-to-br from-slate-100 to-blue-50 p-3 sm:p-6">
    <div class="relative bg-white rounded-xl shadow-lg ring-1 ring-slate-200 p-6 sm:p-10 max-w-4xl mx-auto overflow-hidden">
        <span class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-[#0B3D91] via-[#4FC3F7] to-[#FFC107]"></span>
        <div class="flex items-center gap-3 border-b-2 border-[#0B3D91] pb-4 mb-4">
            <span class="h-11 w-11 rounded-xl bg-gradient-to-br from-[#4FC3F7] to-[#0B3D91] ring-2 ring-[#FFC107] text-white grid place-items-center font-display font-bold text-xs">ISG</span>
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
            <span class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 text-[#0B3D91] font-semibold px-3 py-1 text-sm"><x-sg.icono nombre="calendar" class="h-4 w-4" />{{ \App\Support\FechaEsp::corta($mesa->fecha_examen) }}</span>
        </div>

        <h3 class="text-center font-display font-extrabold text-[#0B3D91] tracking-wide mb-5">ACTA DE EXÁMENES {{ mb_strtoupper($mesa->turnoExamen->nombre_turno) }} {{ $mesa->anioLectivo->anio }}</h3>

        <p class="text-sm mb-1"><strong>Carrera:</strong> {{ $mesa->materia->carrera->nombre }}.</p>
        <p class="text-sm mb-1"><strong>Asignatura:</strong> {{ mb_strtoupper($mesa->materia->nombre) }}</p>
        <p class="text-sm mb-1"><strong>Llamado:</strong> {{ $mesa->llamadoExamen->nombre_llamado }}</p>
        <p class="text-sm mb-4"><strong>Examen de Alumnos:</strong> REGULAR</p>

        <table class="w-full text-xs border-collapse mb-4">
            <thead>
                <tr class="bg-[#0B3D91] text-white">
                    <th class="border border-slate-300 px-2 py-1">N°</th>
                    <th class="border border-slate-300 px-2 py-1">Documento</th>
                    <th class="border border-slate-300 px-3 py-1 text-left">Apellido y Nombre</th>
                    <th class="border border-slate-300 px-2 py-1" colspan="3">Calificación</th>
                </tr>
                <tr class="bg-blue-50 text-[#0B3D91]">
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
                        <td class="border border-slate-300 text-center py-1.5">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="border border-slate-300 text-center">DNI {{ $inscripcion->personaAlumno->dni }}</td>
                        <td class="border border-slate-300 px-3">{{ mb_strtoupper($inscripcion->personaAlumno->apellido) }}, {{ mb_strtoupper($inscripcion->personaAlumno->nombre) }}</td>
                        <td class="border border-slate-300 text-center py-1.5">{{ $detalle->nota_escrito ?? '—' }}</td>
                        <td class="border border-slate-300 text-center py-1.5">{{ $detalle->nota_oral ?? '—' }}</td>
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

        <label class="sg-label">OBSERVACIONES:</label>
        <p class="text-sm text-slate-600 border-b border-slate-200 pb-2 mb-6 min-h-[1.5rem]">{{ $acta->observaciones ?? '—' }}</p>

        <div class="flex flex-wrap justify-end gap-x-8 gap-y-2 text-sm mb-10 rounded-xl bg-slate-50 px-5 py-3">
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
    </div>
@endsection
