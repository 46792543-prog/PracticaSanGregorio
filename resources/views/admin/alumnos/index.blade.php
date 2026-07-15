@extends('layouts.admin')

@section('titulo', 'Alumnos Registrados')

@section('contenido')
    @php
        $iconos = [
            'users' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
            'check' => 'm4.5 12.75 6 6 9-13.5',
            'clock' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            'alert' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        ];
        $stats = [
            ['label' => 'Alumnos en esta carrera', 'valor' => $totalEnCarrera, 'icono' => 'users', 'color' => 'blue'],
            ['label' => 'Activos', 'valor' => $activos, 'icono' => 'check', 'color' => 'emerald'],
            ['label' => 'Doc. pendiente', 'valor' => $docPendiente, 'icono' => 'clock', 'color' => 'amber'],
            ['label' => 'Con cuotas en deuda', 'valor' => $conDeuda, 'icono' => 'alert', 'color' => 'rose'],
        ];
        $colorMap = [
            'blue' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'ring' => 'ring-blue-100'],
            'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-100'],
            'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'ring' => 'ring-amber-100'],
            'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'ring' => 'ring-rose-100'],
        ];
    @endphp

    <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Alumnos Registrados</h1>
            <p class="text-sm text-slate-400 mt-0.5">Listado completo por carrera — Año lectivo {{ now()->year }}</p>
        </div>
        <a href="{{ route('admin.alumnos.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#D4A017] hover:brightness-105 text-[#122a52] font-bold text-sm px-5 py-3 shadow-md shadow-black/5 transition">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Registrar nuevo alumno
        </a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ($stats as $stat)
            @php $c = $colorMap[$stat['color']]; @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $c['bg'] }} {{ $c['text'] }} ring-4 {{ $c['ring'] }} mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos[$stat['icono']] }}" />
                    </svg>
                </span>
                <p class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ $stat['valor'] }}</p>
                <p class="text-xs font-semibold text-slate-400 mt-0.5">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6 grid sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">CARRERA</label>
            <select name="carrera" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
                <option value="">Todas las carreras</option>
                @foreach ($carreras as $carrera)
                    <option value="{{ $carrera->id }}" @selected($carreraId == $carrera->id)>{{ $carrera->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">ESTADO</label>
            <select name="estado" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
                <option value="">Todos</option>
                <option value="activo" @selected($estado === 'activo')>Activo</option>
                <option value="baja" @selected($estado === 'baja')>Baja</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">BUSCAR</label>
            <input type="text" name="q" value="{{ $busqueda }}" placeholder="Nombre, apellido o DNI..."
                   class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
        </div>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wide bg-slate-50/80">
                    <th class="px-6 py-3.5 font-semibold">Alumno</th>
                    <th class="px-6 py-3.5 font-semibold">Carrera</th>
                    <th class="px-6 py-3.5 font-semibold">Año</th>
                    <th class="px-6 py-3.5 font-semibold">Estado</th>
                    <th class="px-6 py-3.5 font-semibold">Cuotas</th>
                    <th class="px-6 py-3.5 font-semibold">Fecha alta</th>
                    <th class="px-6 py-3.5 font-semibold"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($alumnos as $alumno)
                    @php
                        $inscripcion = $alumno->inscripcionesCarrera->first();
                        $vencidas = $alumno->cuotas()->where('estado', 'vencido')->count();
                        $iniciales = mb_substr($alumno->nombre, 0, 1) . mb_substr($alumno->apellido, 0, 1);
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="h-9 w-9 shrink-0 rounded-full bg-gradient-to-br from-blue-100 to-blue-50 text-blue-700 text-xs font-bold grid place-items-center ring-1 ring-blue-100">{{ $iniciales }}</span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-700 truncate">{{ $alumno->apellido }}, {{ $alumno->nombre }}</p>
                                    <p class="text-xs text-slate-400">DNI {{ $alumno->dni }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 text-slate-500">{{ $inscripcion?->carrera?->nombre ?? '—' }}</td>
                        <td class="px-6 py-3.5 text-slate-500">{{ $inscripcion?->anio_actual ?? '—' }}°</td>
                        <td class="px-6 py-3.5">
                            @if (! $inscripcion)
                                <span class="text-xs font-semibold rounded-full px-3 py-1 bg-slate-100 text-slate-500">Sin carrera</span>
                            @elseif ($inscripcion->estado === 'baja')
                                <span class="text-xs font-semibold rounded-full px-3 py-1 bg-red-50 text-red-600">Baja</span>
                            @else
                                <span class="text-xs font-semibold rounded-full px-3 py-1 bg-emerald-50 text-emerald-700">Activo</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            @if ($vencidas > 0)
                                <span class="text-xs font-semibold rounded-full px-3 py-1 bg-red-50 text-red-600">{{ $vencidas }} cuotas</span>
                            @else
                                <span class="text-xs font-semibold rounded-full px-3 py-1 bg-emerald-50 text-emerald-700">Al día</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5 text-slate-500">{{ \App\Support\FechaEsp::corta($alumno->created_at) }}</td>
                        <td class="px-6 py-3.5">
                            <a href="{{ route('admin.alumnos.show', $alumno) }}" class="inline-flex items-center gap-1 text-[#1E4D8C] hover:text-[#122a52] text-xs font-bold">
                                Ver
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-3 w-3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">No se encontraron alumnos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $alumnos->links() }}
        </div>
    </div>
@endsection
