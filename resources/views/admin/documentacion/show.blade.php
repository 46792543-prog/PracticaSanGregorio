@extends('layouts.admin')

@section('titulo', 'Revisión de documentación')

@section('contenido')
    <p class="text-sm text-slate-400 mb-4">
        <a href="{{ route('admin.documentacion.index') }}" class="hover:underline">Alumnos</a> ›
        <span class="text-slate-600">{{ $alumno->nombre }} {{ $alumno->apellido }}</span> › Documentación
    </p>

    @php
        $obligatorios = $documentos->where('obligatorio', true);
        $estadoGeneral = $obligatorios->contains(fn ($d) => ($d->documentosAlumno->first()->estado ?? 'pendiente') !== 'aprobado') ? 'Pendiente de aprobación' : 'Documentación completa';
    @endphp

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6 flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <span class="h-14 w-14 rounded-full bg-blue-100 text-blue-700 font-bold grid place-items-center">
                {{ mb_substr($alumno->nombre, 0, 1) }}{{ mb_substr($alumno->apellido, 0, 1) }}
            </span>
            <div>
                <h1 class="font-bold text-lg text-slate-800">{{ $alumno->nombre }} {{ $alumno->apellido }}</h1>
                <p class="text-sm text-slate-400">DNI {{ $alumno->dni }} · {{ $carrera?->nombre }}</p>
            </div>
        </div>
        <span class="text-sm font-semibold rounded-full px-4 py-2 {{ $estadoGeneral === 'Documentación completa' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
            {{ $estadoGeneral }}
        </span>
    </div>

    <p class="text-xs font-semibold text-slate-400 uppercase mb-3">Documentos enviados</p>

    <div class="space-y-3">
        @foreach ($documentos as $documento)
            @php $docAlumno = $documento->documentosAlumno->first(); $estado = $docAlumno->estado ?? 'pendiente'; @endphp
            <div @class([
                    'bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex items-center justify-between gap-4 border-l-4',
                    'border-amber-400' => $estado === 'entregado',
                    'border-green-500' => $estado === 'aprobado',
                    'border-red-400' => in_array($estado, ['pendiente', 'rechazado']),
                ])>
                <div>
                    <p class="font-semibold text-slate-700">{{ $documento->nombre }}</p>
                    <p class="text-xs text-slate-400">
                        {{ $documento->obligatorio ? 'Obligatorio' : 'Opcional' }}
                        @if ($docAlumno?->fecha_aprobacion) · Aprobado el {{ \App\Support\FechaEsp::corta($docAlumno->fecha_aprobacion) }} @endif
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <span @class([
                            'text-xs font-semibold rounded-full px-3 py-1.5',
                            'bg-slate-100 text-slate-400' => $estado === 'pendiente',
                            'bg-amber-100 text-amber-700' => $estado === 'entregado',
                            'bg-green-100 text-green-700' => $estado === 'aprobado',
                            'bg-red-100 text-red-600' => $estado === 'rechazado',
                        ])>
                        {{ match($estado) { 'pendiente' => 'Sin entregar', 'entregado' => 'Entregado', 'aprobado' => 'Aprobado', 'rechazado' => 'Rechazado' } }}
                    </span>

                    @if ($docAlumno && $estado === 'entregado')
                        <form method="POST" action="{{ route('admin.documentacion.actualizar', $docAlumno) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="estado" value="aprobado">
                            <button type="submit" class="text-xs font-semibold rounded-lg bg-green-600 text-white px-3 py-1.5">Aprobar</button>
                        </form>
                        <form method="POST" action="{{ route('admin.documentacion.actualizar', $docAlumno) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="estado" value="rechazado">
                            <button type="submit" class="text-xs font-semibold rounded-lg bg-red-500 text-white px-3 py-1.5">Rechazar</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        <a href="{{ route('admin.documentacion.index') }}" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] font-semibold text-sm px-6 py-2.5">Volver</a>
    </div>
@endsection
