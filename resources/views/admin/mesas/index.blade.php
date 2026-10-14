@extends('layouts.admin')

@section('titulo', 'Mesas de examen')
@section('subtitulo', \App\Support\FechaEsp::larga(now()))

@section('contenido')
    <x-sg.encabezado titulo="Mesas de examen" :subtitulo="\App\Support\FechaEsp::larga(now())" eyebrow="Gestión académica" icono="clipboard">
        <a href="{{ route('admin.mesas.create') }}" class="sg-btn sg-btn--primario">
            <x-sg.icono nombre="plus" /> Nueva mesa de examen
        </a>
    </x-sg.encabezado>

    <form method="GET" class="sg-card sg-revelar p-4 mb-6 flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[220px]">
            <label class="sg-label">Buscar</label>
            <div class="sg-campo-icono">
                <x-sg.icono nombre="search" />
                <input type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar por materia..." maxlength="50" class="sg-input w-full">
            </div>
        </div>
        <div class="w-full sm:w-64">
            <label class="sg-label">Turno</label>
            <select name="turno" onchange="this.form.submit()" class="sg-input">
                @foreach ($turnos as $t)
                    <option value="{{ $t->id_turno }}" @selected($turno == $t->id_turno)>{{ $t->nombre_turno }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <x-sg.tarjeta :cuerpo="false">
        <table class="sg-tabla">
            <thead>
                <tr>
                    <th>Materia</th>
                    <th>Fecha examen</th>
                    <th>Inscripción</th>
                    <th>Llamado</th>
                    <th>Tribunal</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="sg-filas">
                @forelse ($mesas as $mesa)
                    @php
                        $presidente = $mesa->tribunal->first(fn ($t) => $t->rolTribunal->nombre_rol === 'Presidente');
                        $estadoMesa = $mesa->estadoMesa->nombre_estado;
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-xl bg-blue-50 text-[#0B3D91] leading-none">
                                    <span class="font-display text-sm font-extrabold">{{ $mesa->fecha_examen?->format('d') }}</span>
                                    <span class="text-[9px] font-bold uppercase">{{ $mesa->fecha_examen ? \App\Support\FechaEsp::mesAbrev($mesa->fecha_examen) : '' }}</span>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800">{{ $mesa->materia->nombre }}</p>
                                    <p class="text-xs text-slate-400">{{ $mesa->materia->carrera->nombre_carrera }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap">{{ \App\Support\FechaEsp::corta($mesa->fecha_examen) }}</td>
                        <td class="text-xs whitespace-nowrap">
                            {{ \App\Support\FechaEsp::corta($mesa->fecha_inicio_inscripcion) }}
                            <x-sg.icono nombre="arrow-right" class="inline h-3 w-3 text-slate-300" />
                            {{ \App\Support\FechaEsp::corta($mesa->fecha_fin_inscripcion) }}
                        </td>
                        <td><span class="sg-badge sg-badge--celeste sg-badge--sin-punto">{{ $mesa->llamadoExamen->nombre_llamado }}</span></td>
                        <td>
                            @if ($presidente)
                                <span class="inline-flex items-center gap-2">
                                    <span class="sg-avatar !h-7 !w-7 !rounded-lg !text-[10px]">{{ mb_substr($presidente->profesor->nombre, 0, 1) }}{{ mb_substr($presidente->profesor->apellido, 0, 1) }}</span>
                                    <span class="text-sm">{{ $presidente->profesor->apellido . ', ' . mb_substr($presidente->profesor->nombre, 0, 1) . '. (Pres.)' }}</span>
                                </span>
                            @else
                                <span class="text-slate-400 italic">Sin asignar</span>
                            @endif
                        </td>
                        <td>
                            <span @class([
                                    'sg-badge',
                                    'sg-badge--verde' => $estadoMesa === 'Finalizada',
                                    'sg-badge--azul' => $estadoMesa === 'Programada',
                                    'sg-badge--rojo' => $estadoMesa === 'Cancelada',
                                ])>
                                {{ $estadoMesa }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.mesas.acta', $mesa) }}" class="sg-btn sg-btn--suave sg-btn--chico"><x-sg.icono nombre="document" /> Acta</a>
                                @if ($estadoMesa === 'Programada')
                                    <a href="{{ route('admin.mesas.edit', $mesa) }}" class="sg-btn sg-btn--borde sg-btn--icono" title="Editar"><x-sg.icono nombre="pencil" class="!h-4 !w-4" /></a>
                                    <form method="POST" action="{{ route('admin.mesas.destroy', $mesa) }}" onsubmit="return confirm('¿Cancelar esta mesa?');">
                                        @csrf @method('DELETE')
                                        <button class="sg-btn sg-btn--peligro sg-btn--icono" title="Cancelar mesa"><x-sg.icono nombre="x" class="!h-4 !w-4" /></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-sg.vacio icono="clipboard" titulo="Sin mesas">No hay mesas programadas para este turno.</x-sg.vacio></td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($mesas->hasPages())
            <x-slot:pie>{{ $mesas->links() }}</x-slot:pie>
        @endif
    </x-sg.tarjeta>
@endsection
