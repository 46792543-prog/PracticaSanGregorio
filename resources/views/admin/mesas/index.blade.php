@extends('layouts.admin')

@section('titulo', 'Mesas de examen')
@section('subtitulo', \App\Support\FechaEsp::larga(now()))

@section('contenido')
    <p class="text-sm text-slate-400 mb-2">Gestión académica / Mesas de examen</p>

    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <form method="GET" class="flex gap-3">
            <input type="text" name="q" value="{{ $busqueda }}" placeholder="🔍 Buscar por materia..."
                   class="rounded-lg border border-slate-300 px-4 py-2 text-sm w-64">
            <select name="turno" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">
                @foreach ($turnos as $valor => $texto)
                    <option value="{{ $valor }}" @selected($turno === $valor)>{{ $texto }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('admin.mesas.create') }}" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow-md hover:brightness-105 transition text-white font-semibold text-sm px-5 py-2.5">
            + Nueva mesa de examen
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-400 uppercase bg-slate-50">
                    <th class="px-6 py-3 font-semibold">Materia</th>
                    <th class="px-6 py-3 font-semibold">Fecha examen</th>
                    <th class="px-6 py-3 font-semibold">Inscripción</th>
                    <th class="px-6 py-3 font-semibold">Llamado</th>
                    <th class="px-6 py-3 font-semibold">Tribunal</th>
                    <th class="px-6 py-3 font-semibold">Estado</th>
                    <th class="px-6 py-3 font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($mesas as $mesa)
                    @php $presidente = $mesa->tribunal->firstWhere('rol', 'presidente'); @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-700">{{ $mesa->materia->nombre }}</p>
                            <p class="text-xs text-slate-400">{{ $mesa->materia->carrera->nombre }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ \App\Support\FechaEsp::corta($mesa->fecha_examen) }}</td>
                        <td class="px-6 py-4 text-slate-500 text-xs">
                            {{ \App\Support\FechaEsp::corta($mesa->fecha_inicio_inscripcion) }} →
                            {{ \App\Support\FechaEsp::corta($mesa->fecha_fin_inscripcion) }}
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $mesa->llamado === 'primer_llamado' ? '1er llamado' : '2do llamado' }}</td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ $presidente ? $presidente->profesor->apellido . ', ' . mb_substr($presidente->profesor->nombre, 0, 1) . '. (Pres.)' : 'Sin asignar' }}
                        </td>
                        <td class="px-6 py-4">
                            <span @class([
                                    'text-xs font-semibold rounded-full px-3 py-1',
                                    'bg-green-100 text-green-700' => $mesa->estado === 'finalizada',
                                    'bg-slate-100 text-slate-500' => $mesa->estado === 'programada',
                                    'bg-red-100 text-red-600' => $mesa->estado === 'cancelada',
                                ])>
                                {{ ucfirst($mesa->estado) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if ($mesa->estado === 'programada')
                                <form method="POST" action="{{ route('admin.mesas.destroy', $mesa) }}" onsubmit="return confirm('¿Cancelar esta mesa?');">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 text-xs font-semibold hover:underline">Cancelar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400">No hay mesas programadas para este turno.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
