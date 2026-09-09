@extends('layouts.director')

@section('titulo', 'Auditoría')
@section('subtitulo', 'Registro de quién hizo qué en el sistema')

@section('contenido')
    @php
        $colores = [
            'inscripcion' => 'bg-emerald-50 text-emerald-700',
            'baja_alumno' => 'bg-rose-50 text-rose-600',
            'caja' => 'bg-amber-50 text-amber-700',
            'documentacion' => 'bg-blue-50 text-blue-700',
            'baja_mesa' => 'bg-rose-50 text-rose-600',
            'acta' => 'bg-violet-50 text-violet-700',
            'configuracion' => 'bg-slate-100 text-slate-600',
            'baja_profesor' => 'bg-rose-50 text-rose-600',
            'reactivacion_profesor' => 'bg-emerald-50 text-emerald-700',
        ];
    @endphp

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between gap-4 flex-wrap px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-transparent">
            <h3 class="font-bold text-slate-700 text-sm flex items-center gap-2">🕵️ Línea de tiempo de acciones</h3>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 px-6 py-4 border-b border-slate-100">
            <select name="tipo" onchange="this.form.submit()" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                <option value="">Todos los tipos</option>
                @foreach ($tipos as $clave => $label)
                    <option value="{{ $clave }}" @selected($filtros['tipo'] === $clave)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="desde" value="{{ $filtros['desde'] }}" placeholder="Desde"
                   class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <input type="date" name="hasta" value="{{ $filtros['hasta'] }}" placeholder="Hasta"
                   class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <button class="rounded-xl bg-[#1E4D8C] text-white text-sm font-semibold px-4">Filtrar</button>
            @if ($filtros['tipo'] || $filtros['desde'] || $filtros['hasta'])
                <a href="{{ route('director.auditoria.index') }}" class="rounded-xl border border-slate-200 text-slate-500 text-sm font-semibold px-4 py-2">Limpiar</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wide bg-slate-50/80">
                        <th class="px-6 py-3 font-semibold">Fecha y hora</th>
                        <th class="px-6 py-3 font-semibold">Tipo</th>
                        <th class="px-6 py-3 font-semibold">Detalle</th>
                        <th class="px-6 py-3 font-semibold">Realizado por</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($eventos as $evento)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-6 py-3 text-slate-500 whitespace-nowrap">
                                {{ $evento['fecha']?->format('d/m/Y') }}
                                <span class="text-slate-400">· {{ $evento['fecha']?->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-xs font-semibold rounded-full px-3 py-1 {{ $colores[$evento['tipo']] }}">
                                    {{ $evento['tipoLabel'] }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-slate-700">{{ $evento['descripcion'] }}</td>
                            <td class="px-6 py-3">
                                @if ($evento['actor'])
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="h-6 w-6 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold grid place-items-center">{{ mb_substr($evento['actor']->nombre, 0, 1) }}{{ mb_substr($evento['actor']->apellido, 0, 1) }}</span>
                                        <span class="text-slate-600 text-xs">{{ $evento['actor']->nombre }} {{ $evento['actor']->apellido }}</span>
                                        <span class="text-[10px] font-semibold rounded px-1.5 py-0.5 {{ $evento['actor']->usuario?->esDirector() ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $evento['actor']->usuario?->esDirector() ? 'Dir.' : 'Sec.' }}
                                        </span>
                                    </span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-slate-400">No hay acciones registradas con estos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100">
            {{ $eventos->links() }}
        </div>
    </div>
@endsection
