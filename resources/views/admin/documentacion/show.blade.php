@extends('layouts.admin')

@section('titulo', 'Revisión de documentación')

@section('contenido')
    @php
        $obligatorios = $documentos->where('obligatorio', true);
        $estadoGeneral = $obligatorios->contains(fn ($d) => ($d->controlDocumentacion->first()->estadoDocumento->nombre_estado ?? 'Pendiente') !== 'Aprobado') ? 'Pendiente de aprobación' : 'Documentación completa';
        $aprobados = $documentos->filter(fn ($d) => ($d->controlDocumentacion->first()->estadoDocumento->nombre_estado ?? 'Pendiente') === 'Aprobado')->count();
    @endphp

    <a href="{{ route('admin.documentacion.index') }}" class="sg-link mb-4 sg-revelar"><x-sg.icono nombre="arrow-left" class="!h-4 !w-4" /> Volver a documentación</a>

    <section class="sg-card sg-revelar p-6 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-5">
            <div class="flex items-center gap-4">
                <span class="sg-avatar sg-avatar--grande sg-avatar--marca">
                    {{ mb_substr($alumno->nombre, 0, 1) }}{{ mb_substr($alumno->apellido, 0, 1) }}
                </span>
                <div>
                    <p class="sg-eyebrow">Revisión de documentación</p>
                    <h1 class="font-display font-extrabold text-xl text-slate-900 mt-0.5">{{ $alumno->nombre }} {{ $alumno->apellido }}</h1>
                    <p class="text-sm text-slate-500">DNI {{ $alumno->dni }} · {{ $carrera?->nombre }}</p>
                </div>
            </div>
            <div class="text-right min-w-[220px]">
                <span class="sg-badge {{ $estadoGeneral === 'Documentación completa' ? 'sg-badge--verde' : 'sg-badge--amarillo' }} text-sm px-4 py-2">
                    {{ $estadoGeneral }}
                </span>
                @if ($documentos->count() > 0)
                    <div class="sg-progreso mt-3"><span style="width: {{ round($aprobados / $documentos->count() * 100) }}%"></span></div>
                    <p class="text-xs text-slate-400 mt-1.5">{{ $aprobados }} de {{ $documentos->count() }} documentos aprobados</p>
                @endif
            </div>
        </div>
    </section>

    <div class="sg-seccion-titulo sg-revelar">
        <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="document" /></span>
        <h2>Documentos enviados</h2>
    </div>

    <div class="grid gap-4 md:grid-cols-2" data-sg-escalonar>
        @foreach ($documentos as $documento)
            @php $docAlumno = $documento->controlDocumentacion->first(); $estado = $docAlumno->estadoDocumento->nombre_estado ?? 'Pendiente'; @endphp
            <div class="sg-card sg-card--hover p-5 overflow-hidden">
                <span @class([
                        'absolute inset-y-0 left-0 w-1.5',
                        'bg-amber-400' => $estado === 'Entregado',
                        'bg-emerald-500' => $estado === 'Aprobado',
                        'bg-rose-400' => in_array($estado, ['Pendiente', 'Rechazado']),
                    ])></span>
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3 min-w-0">
                        <span @class([
                                'sg-chip',
                                'sg-chip--amarillo' => $estado === 'Entregado',
                                'sg-chip--verde' => $estado === 'Aprobado',
                                'sg-chip--gris' => $estado === 'Pendiente',
                                'sg-chip--rojo' => $estado === 'Rechazado',
                            ])>
                            <x-sg.icono :nombre="$estado === 'Aprobado' ? 'document-check' : 'document'" />
                        </span>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800">{{ $documento->nombre }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $documento->obligatorio ? 'Obligatorio' : 'Opcional' }}
                                @if ($docAlumno?->fecha_aprobacion) · Aprobado el {{ \App\Support\FechaEsp::corta($docAlumno->fecha_aprobacion) }} @endif
                            </p>
                            @if ($docAlumno?->archivo_path)
                                <a href="{{ route('admin.documentacion.descarga', $docAlumno) }}" class="sg-link mt-2">
                                    <x-sg.icono nombre="download" /> {{ $docAlumno->archivo_nombre_original }}
                                </a>
                            @endif
                        </div>
                    </div>
                    <span @class([
                            'sg-badge shrink-0',
                            'sg-badge--gris' => $estado === 'Pendiente',
                            'sg-badge--amarillo' => $estado === 'Entregado',
                            'sg-badge--verde' => $estado === 'Aprobado',
                            'sg-badge--rojo' => $estado === 'Rechazado',
                        ])>
                        {{ $estado === 'Pendiente' ? 'Sin entregar' : $estado }}
                    </span>
                </div>

                @if ($docAlumno && $estado === 'Entregado')
                    <div class="flex justify-end gap-2 mt-4 pt-4 border-t border-slate-100">
                        <form method="POST" action="{{ route('admin.documentacion.actualizar', $docAlumno) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="estado" value="Rechazado">
                            <button type="submit" class="sg-btn sg-btn--peligro sg-btn--chico"><x-sg.icono nombre="x" /> Rechazar</button>
                        </form>
                        <form method="POST" action="{{ route('admin.documentacion.actualizar', $docAlumno) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="estado" value="Aprobado">
                            <button type="submit" class="sg-btn sg-btn--verde sg-btn--chico"><x-sg.icono nombre="check" /> Aprobar</button>
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endsection
