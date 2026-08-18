@extends('layouts.admin')

@section('titulo', 'Inscripciones a mesas de examen')

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">📋 Inscripciones a mesas de examen</h1>
        <p class="text-sm text-slate-400">Aprobá o rechazá las inscripciones enviadas por los alumnos</p>
    </div>

    <div class="flex gap-2 mb-6">
        @foreach (['En proceso' => 'Pendientes', 'Aceptado' => 'Aceptadas', 'Rechazado' => 'Rechazadas', 'todas' => 'Todas'] as $valor => $texto)
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
                    <th class="px-6 py-3 font-semibold">Nota / Resultado</th>
                    <th class="px-6 py-3 font-semibold">Estado</th>
                    <th class="px-6 py-3 font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($inscripciones as $inscripcion)
                    @php $estadoNombre = $inscripcion->estadoInscripcion->nombre_estado; @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-700">{{ $inscripcion->personaAlumno->nombre }} {{ $inscripcion->personaAlumno->apellido }}</p>
                            <p class="text-xs text-slate-400">DNI {{ $inscripcion->personaAlumno->dni }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-slate-700">{{ $inscripcion->mesaExamen->materia->nombre }}</p>
                            <p class="text-xs text-slate-400">{{ $inscripcion->mesaExamen->materia->carrera->nombre_carrera }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ \App\Support\FechaEsp::corta($inscripcion->fecha_inscripcion) }}</td>
                        <td class="px-6 py-4">
                            <form method="POST" action="{{ route('admin.inscripciones.actualizar', $inscripcion) }}" class="flex items-center gap-1.5">
                                @csrf @method('PUT')
                                <input type="hidden" name="estado" value="{{ $estadoNombre === 'En proceso' ? 'Aceptado' : $estadoNombre }}">
                                <input type="text" inputmode="numeric" data-nota name="nota_examen" value="{{ $inscripcion->nota_examen }}" placeholder="Nota"
                                       class="w-16 rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                <select name="resultado" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                    <option value="">—</option>
                                    <option value="aprobado" @selected($inscripcion->resultado === 'aprobado')>Aprobado</option>
                                    <option value="desaprobado" @selected($inscripcion->resultado === 'desaprobado')>Desaprobado</option>
                                    <option value="ausente" @selected($inscripcion->resultado === 'ausente')>Ausente</option>
                                </select>
                                <button type="submit" class="text-[#1E4D8C] text-xs font-semibold hover:underline">Guardar</button>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            <span @class([
                                    'text-xs font-semibold rounded-full px-3 py-1',
                                    'bg-amber-100 text-amber-700' => $estadoNombre === 'En proceso',
                                    'bg-green-100 text-green-700' => $estadoNombre === 'Aceptado',
                                    'bg-red-100 text-red-600' => $estadoNombre === 'Rechazado',
                                ])>
                                {{ ['En proceso' => 'Pendiente', 'Aceptado' => 'Aceptada', 'Rechazado' => 'Rechazada'][$estadoNombre] ?? $estadoNombre }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if ($estadoNombre === 'En proceso')
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('admin.inscripciones.actualizar', $inscripcion) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="estado" value="Aceptado">
                                        <button class="rounded-lg bg-green-600 text-white text-xs font-semibold px-3 py-1.5">Aceptar</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.inscripciones.actualizar', $inscripcion) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="estado" value="Rechazado">
                                        <button class="rounded-lg bg-red-500 text-white text-xs font-semibold px-3 py-1.5">Rechazar</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400">No hay inscripciones para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
