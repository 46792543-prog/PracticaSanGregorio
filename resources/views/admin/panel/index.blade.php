@extends('layouts.admin')

@section('titulo', 'Panel principal')

@section('contenido')
    @php
        $iconos = [
            'users' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
            'cap' => 'M4.26 10.147a60.437 60.437 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443',
            'clipboard' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h3.28c.34-.6.97-1 1.72-1s1.38.4 1.72 1H17a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2ZM9 6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2',
            'document' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
            'plus-user' => 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z',
            'ban' => 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636',
            'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008Z',
            'clock' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            'arrow-right' => 'M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3',
        ];
        $stats = [
            ['label' => 'Alumnos activos', 'valor' => $alumnosActivos, 'icono' => 'users', 'color' => 'blue'],
            ['label' => 'Carreras', 'valor' => $carreras, 'icono' => 'cap', 'color' => 'amber'],
            ['label' => 'Mesas hoy', 'valor' => $mesasHoy, 'sub' => "{$mesasEnCurso} en curso", 'icono' => 'clipboard', 'color' => 'emerald'],
            ['label' => 'Actas por generar', 'valor' => $actasPorGenerar, 'icono' => 'document', 'color' => 'rose'],
        ];
        $colorMap = [
            'blue' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'ring' => 'ring-blue-100'],
            'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'ring' => 'ring-amber-100'],
            'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-100'],
            'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'ring' => 'ring-rose-100'],
        ];
        $acciones = [
            ['ruta' => 'admin.mesas.create', 'titulo' => 'Nueva mesa de examen', 'texto' => 'Crear turno, asignar docente y materia', 'icono' => 'clipboard', 'color' => 'blue'],
            ['ruta' => 'admin.carreras.index', 'titulo' => 'Gestionar planes', 'texto' => 'Editar materias, correlativas y carreras', 'icono' => 'cap', 'color' => 'amber'],
            ['ruta' => 'admin.actas.index', 'titulo' => 'Emitir actas', 'texto' => 'Generar actas autocompletadas por mesa', 'icono' => 'document', 'color' => 'emerald'],
            ['ruta' => 'admin.alumnos.create', 'titulo' => 'Inscribir alumno', 'texto' => 'Verificar cuotas y correlativas', 'icono' => 'plus-user', 'color' => 'violet'],
        ];
        $colorMap['violet'] = ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'ring' => 'ring-violet-100'];
    @endphp

    <div class="relative rounded-3xl bg-gradient-to-br from-[#173d70] via-[#1E4D8C] to-[#2a63b0] text-white px-8 py-7 mb-8 overflow-hidden shadow-lg shadow-blue-900/10">
        <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/5"></div>
        <div class="absolute right-24 -bottom-10 h-32 w-32 rounded-full bg-[#D4A017]/10"></div>
        <div class="relative flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Buen día, {{ auth()->user()->nombre }} 👋</h1>
                <p class="text-sm text-blue-100/80 mt-1">{{ \App\Support\FechaEsp::larga(now()) }}</p>
            </div>
            <a href="{{ route('admin.alumnos.create') }}" class="rounded-xl bg-[#D4A017] hover:brightness-105 text-[#122a52] font-bold text-sm px-5 py-3 shadow-md shadow-black/10 transition">
                + Inscribir alumno
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach ($stats as $stat)
            @php $c = $colorMap[$stat['color']]; @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $c['bg'] }} {{ $c['text'] }} ring-4 {{ $c['ring'] }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos[$stat['icono']] }}" />
                        </svg>
                    </span>
                </div>
                <p class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ $stat['valor'] }}</p>
                <p class="text-xs font-semibold text-slate-400 mt-0.5">{{ $stat['label'] }}</p>
                @if (isset($stat['sub']))
                    <p class="text-[11px] text-emerald-600 font-semibold mt-1">{{ $stat['sub'] }}</p>
                @endif
            </div>
        @endforeach
    </div>

    <div class="flex items-center gap-2 mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-[#D4A017]">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 9 3l9 6.5-5.25 10.5L3.75 13.5Z" />
        </svg>
        <h3 class="font-bold text-slate-700 text-sm">Acciones rápidas</h3>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach ($acciones as $accion)
            @php $c = $colorMap[$accion['color']]; @endphp
            <a href="{{ route($accion['ruta']) }}" class="group bg-white rounded-2xl shadow-sm border border-slate-100 p-4 flex items-start gap-3 hover:shadow-md hover:-translate-y-0.5 transition-all">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $c['bg'] }} {{ $c['text'] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos[$accion['icono']] }}" />
                    </svg>
                </span>
                <span class="min-w-0">
                    <span class="block font-semibold text-sm text-slate-700 group-hover:text-[#1E4D8C]">{{ $accion['titulo'] }}</span>
                    <span class="block text-xs text-slate-400 mt-0.5 leading-snug">{{ $accion['texto'] }}</span>
                </span>
            </a>
        @endforeach
        <div class="bg-slate-50 rounded-2xl border border-dashed border-slate-200 p-4 flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos['ban'] }}" />
                </svg>
            </span>
            <span>
                <span class="block font-semibold text-sm text-slate-500">Baja de alumno</span>
                <span class="block text-xs text-slate-400 mt-0.5">Proceso exclusivo de Secretaría</span>
                <span class="inline-block text-[10px] font-bold tracking-wide text-rose-500 bg-rose-50 rounded-full px-2 py-0.5 mt-1.5">SOLO SECRETARÍA</span>
            </span>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="flex items-center gap-2.5 px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-transparent">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px] text-[#1E4D8C]">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos['calendar'] }}" />
                </svg>
                <h3 class="font-bold text-slate-700 text-sm">Mesas de hoy</h3>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse ($mesasDeHoy as $mesa)
                    <div class="flex items-center justify-between px-6 py-3.5 hover:bg-slate-50/70 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="h-2 w-2 rounded-full bg-[#D4A017]"></span>
                            <span class="text-sm font-medium text-slate-700">{{ $mesa->materia->nombre }}</span>
                        </div>
                        <span class="text-xs font-medium text-slate-400">{{ \App\Support\FechaEsp::corta($mesa->fecha_examen) }}</span>
                    </div>
                @empty
                    <p class="px-6 py-8 text-sm text-slate-400 text-center">No hay mesas programadas.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="flex items-center gap-2.5 px-6 py-4 border-b border-slate-100">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px] text-[#1E4D8C]">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos['clock'] }}" />
                </svg>
                <h3 class="font-bold text-slate-700 text-sm">Actividad reciente</h3>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse ($actividad as $evento)
                    <div class="flex items-center gap-3 px-6 py-3.5 hover:bg-slate-50/70 transition-colors">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs">{{ $evento['icono'] }}</span>
                        <span class="text-sm text-slate-600">{{ $evento['titulo'] }}</span>
                    </div>
                @empty
                    <p class="px-6 py-8 text-sm text-slate-400 text-center">Todavía no hay actividad registrada.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
