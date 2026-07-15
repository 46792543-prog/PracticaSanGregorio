@extends('layouts.admin')

@section('titulo', 'Documentación requerida')

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">Buen día, {{ auth()->user()->nombre }} 👋</h1>
        <p class="text-sm text-slate-400">{{ \App\Support\FechaEsp::larga(now()) }}</p>
    </div>

    <div class="bg-blue-50 rounded-xl px-6 py-4 mb-6">
        <h2 class="font-bold text-[#1E4D8C]">📎 Documentación requerida — Revisión de alumnos</h2>
    </div>

    <div class="grid sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <p class="text-xs font-semibold text-slate-400 uppercase">Alumnos totales</p>
            <p class="text-2xl font-bold text-slate-800">{{ $totales['total'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <p class="text-xs font-semibold text-slate-400 uppercase">Pendientes</p>
            <p class="text-2xl font-bold text-red-500">{{ $alumnos->whereIn('estado_documentacion', ['pendiente', 'sin_enviar'])->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <p class="text-xs font-semibold text-slate-400 uppercase">En revisión</p>
            <p class="text-2xl font-bold text-amber-500">{{ $totales['en_revision'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <p class="text-xs font-semibold text-slate-400 uppercase">Completos</p>
            <p class="text-2xl font-bold text-green-600">{{ $totales['completos'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-6">
        <div class="flex flex-wrap items-center gap-3 p-4 border-b border-slate-100">
            <form method="GET" class="flex-1 min-w-[200px]">
                <input type="text" name="q" value="{{ $busqueda }}" placeholder="🔍 Buscar alumno..." onchange="this.form.submit()"
                       class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
                <input type="hidden" name="filtro" value="{{ $filtro }}">
            </form>
            <div class="flex gap-2">
                @foreach (['todos' => 'Todos', 'pendientes' => 'Pendientes', 'completos' => 'Completos'] as $valor => $texto)
                    <a href="{{ route('admin.documentacion.index', ['filtro' => $valor, 'q' => $busqueda]) }}"
                       class="text-sm font-semibold px-4 py-2 rounded-lg {{ $filtro === $valor ? 'bg-[#1E4D8C] text-white' : 'bg-slate-100 text-slate-600' }}">
                        {{ $texto }}
                    </a>
                @endforeach
            </div>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-400 uppercase">
                    <th class="px-6 py-3 font-semibold">Alumno</th>
                    <th class="px-6 py-3 font-semibold">Documentos</th>
                    <th class="px-6 py-3 font-semibold">Estado</th>
                    <th class="px-6 py-3 font-semibold">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($alumnos as $alumno)
                    @php
                        $inscripcion = $alumno->inscripcionesCarrera->first();
                        $badges = ['pendiente' => 'bg-red-50 text-red-500', 'entregado' => 'bg-amber-50 text-amber-600', 'aprobado' => 'bg-green-50 text-green-600', 'rechazado' => 'bg-red-50 text-red-500'];
                        $estadoTexto = ['pendiente' => 'Pendiente', 'revision' => 'En revisión', 'sin_enviar' => 'Sin enviar', 'completo' => 'Completo'];
                        $estadoBadge = ['pendiente' => 'bg-amber-100 text-amber-700', 'revision' => 'bg-blue-100 text-blue-700', 'sin_enviar' => 'bg-red-100 text-red-600', 'completo' => 'bg-green-100 text-green-700'];
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-700">{{ $alumno->nombre }} {{ $alumno->apellido }}</p>
                            <p class="text-xs text-slate-400">{{ $inscripcion?->anio_actual }}° Año · {{ $inscripcion?->carrera?->nombre }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-1 flex-wrap">
                                @foreach ($alumno->documentosAlumno as $doc)
                                    <span class="text-xs px-2 py-0.5 rounded {{ $badges[$doc->estado] }}">
                                        {{ \Illuminate\Support\Str::limit($doc->documentoRequisito->nombre, 8, '') }}
                                        {{ in_array($doc->estado, ['aprobado']) ? '✓' : (in_array($doc->estado, ['pendiente', 'rechazado']) ? '✗' : '⏳') }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold rounded-full px-3 py-1 {{ $estadoBadge[$alumno->estado_documentacion] }}">
                                {{ $estadoTexto[$alumno->estado_documentacion] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.documentacion.show', $alumno) }}" class="rounded-xl bg-[#1E4D8C] shadow-sm hover:shadow transition text-white text-xs font-semibold px-4 py-2">Revisar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-slate-400">No hay alumnos para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
