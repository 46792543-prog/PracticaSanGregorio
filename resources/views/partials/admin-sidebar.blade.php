@php
    $enlaces = [
        ['ruta' => 'admin.panel.index', 'texto' => 'Panel principal', 'icono' => 'home'],
        ['ruta' => 'admin.alumnos.index', 'texto' => 'Alumnos', 'icono' => 'users'],
        ['ruta' => 'admin.profesores.index', 'texto' => 'Profesores', 'icono' => 'briefcase'],
        ['ruta' => 'admin.carreras.index', 'texto' => 'Carreras y planes', 'icono' => 'cap'],
        ['ruta' => 'admin.mesas.index', 'texto' => 'Mesas de examen', 'icono' => 'clipboard'],
        ['ruta' => 'admin.actas.index', 'texto' => 'Actas', 'icono' => 'document'],
        ['ruta' => 'admin.inscripciones.index', 'texto' => 'Inscripciones', 'icono' => 'inbox'],
    ];

    $iconos = [
        'home' => 'M11.47 3.84a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 1 1-1.06 1.06l-.22-.22V19.5a1.5 1.5 0 0 1-1.5 1.5h-3a.75.75 0 0 1-.75-.75V15a.75.75 0 0 0-.75-.75h-1.5a.75.75 0 0 0-.75.75v5.25a.75.75 0 0 1-.75.75h-3a1.5 1.5 0 0 1-1.5-1.5v-7.32l-.22.22a.75.75 0 1 1-1.06-1.06l7.5-7.5Z',
        'users' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z M19.5 8.25a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM21.75 20.25c0-2.153-.966-4.083-2.49-5.377a4.5 4.5 0 0 1 3.037 4.077c.001.076.002.152.002.228v.001a17.951 17.951 0 0 1-2.549 1.071Z',
        'briefcase' => 'M20.25 14.15v4.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25v-4.25M15 6.75V4.5A2.25 2.25 0 0 0 12.75 2.25h-1.5A2.25 2.25 0 0 0 9 4.5v2.25M3.75 9.75h16.5a1.5 1.5 0 0 1 1.5 1.5v2.4c0 .69-.463 1.293-1.13 1.47A18.729 18.729 0 0 1 12 16.5a18.73 18.73 0 0 1-8.62-1.38A1.5 1.5 0 0 1 2.25 13.65v-2.4a1.5 1.5 0 0 1 1.5-1.5Z',
        'cap' => 'M4.26 10.147a60.437 60.437 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443',
        'clipboard' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h3.28c.34-.6.97-1 1.72-1s1.38.4 1.72 1H17a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2ZM9 6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2',
        'document' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
        'inbox' => 'M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M2.25 13.5V17.25a2.25 2.25 0 0 0 2.25 2.25h15a2.25 2.25 0 0 0 2.25-2.25V13.5M2.25 13.5 5.25 4.5h13.5l3 9',
        'folder' => 'M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-19.5 0v6a2.25 2.25 0 0 0 2.25 2.25h15a2.25 2.25 0 0 0 2.25-2.25v-6m-19.5 0v-.15c0-1.03.84-1.85 1.85-1.85h3.729c.535 0 1.045.226 1.406.622l1.03 1.128c.36.396.87.622 1.406.622h4.104c1.01 0 1.85.82 1.85 1.85v.15',
        'upload' => 'M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 8.25 12 3.75m0 0L7.5 8.25M12 3.75v12',
        'logout' => 'M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M18 12H8.25m9.75 0-3-3m3 3-3 3',
        'chevron' => 'm8.25 4.5 7.5 7.5-7.5 7.5',
        'back' => 'M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18',
    ];
@endphp

<aside class="w-72 shrink-0 bg-gradient-to-b from-[#122a52] via-[#0e2242] to-[#0a1a34] text-white flex flex-col relative">
    <div class="absolute inset-0 opacity-[0.04] pointer-events-none"
         style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 22px 22px;"></div>

    <div class="relative px-6 pt-7 pb-6 flex items-center gap-3 border-b border-white/10">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#a97b0e] shadow-lg shadow-black/20 ring-1 ring-white/10">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#0e2242" stroke-width="1.7" class="h-6 w-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25 4.5 5.4v5.4c0 5.13 3.24 9.6 7.5 10.95 4.26-1.35 7.5-5.82 7.5-10.95V5.4L12 2.25Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2.25 2.25L15.5 9.5" />
            </svg>
        </div>
        <div class="min-w-0">
            <p class="font-bold text-[15px] leading-tight truncate">Instituto Superior</p>
            <p class="font-bold text-[15px] leading-tight truncate">San Gregorio</p>
            <p class="text-[10px] font-semibold tracking-widest text-[#e8c465] mt-0.5">PANEL DE GESTIÓN</p>
        </div>
    </div>

    <nav class="relative flex-1 px-4 py-5 space-y-1 overflow-y-auto">
        @foreach ($enlaces as $enlace)
            @php $activo = request()->routeIs($enlace['ruta'] . '*'); @endphp
            <a href="{{ route($enlace['ruta']) }}"
               class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all
                      {{ $activo ? 'bg-white text-[#122a52] shadow-md shadow-black/20' : 'text-blue-100/80 hover:bg-white/[0.07] hover:text-white' }}">
                <span @class([
                        'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-colors',
                        'bg-[#D4A017]/15 text-[#D4A017]' => $activo,
                        'bg-white/[0.06] text-blue-200 group-hover:bg-white/10 group-hover:text-white' => ! $activo,
                    ])>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos[$enlace['icono']] }}" />
                    </svg>
                </span>
                <span class="truncate {{ $activo ? 'font-semibold' : '' }}">{{ $enlace['texto'] }}</span>
                @if ($activo)
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-3.5 w-3.5 ml-auto text-[#D4A017]">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos['chevron'] }}" />
                    </svg>
                @endif
            </a>
        @endforeach

        <p class="px-3.5 pt-5 pb-1.5 text-[10px] font-bold tracking-widest text-blue-300/50 uppercase">Documentación</p>

        @php
            $docEnlaces = [
                ['ruta' => 'admin.documentacion.index', 'texto' => 'Del alumno', 'icono' => 'folder', 'exact_not' => 'admin.documentacion.requisitos'],
                ['ruta' => 'admin.documentacion.requisitos', 'texto' => 'Requisitos', 'icono' => 'upload'],
            ];
        @endphp
        @foreach ($docEnlaces as $enlace)
            @php
                $activo = isset($enlace['exact_not'])
                    ? request()->routeIs('admin.documentacion.*') && ! request()->routeIs($enlace['exact_not'])
                    : request()->routeIs($enlace['ruta']);
            @endphp
            <a href="{{ route($enlace['ruta']) }}"
               class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all
                      {{ $activo ? 'bg-white text-[#122a52] shadow-md shadow-black/20' : 'text-blue-100/80 hover:bg-white/[0.07] hover:text-white' }}">
                <span @class([
                        'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-colors',
                        'bg-[#D4A017]/15 text-[#D4A017]' => $activo,
                        'bg-white/[0.06] text-blue-200 group-hover:bg-white/10 group-hover:text-white' => ! $activo,
                    ])>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos[$enlace['icono']] }}" />
                    </svg>
                </span>
                <span class="truncate {{ $activo ? 'font-semibold' : '' }}">{{ $enlace['texto'] }}</span>
            </a>
        @endforeach
    </nav>

    @if (auth()->user()->esDirector())
        <div class="relative px-4 pb-3">
            <a href="{{ route('director.panel.index') }}"
               class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold bg-[#D4A017]/15 text-[#D4A017] hover:bg-[#D4A017]/25 transition-colors">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#D4A017]/15">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos['back'] }}" />
                    </svg>
                </span>
                <span class="truncate">Volver a mi Panel de Dirección</span>
            </a>
        </div>
    @endif

    <div class="relative px-4 py-4 border-t border-white/10">
        <div class="flex items-center gap-3 rounded-xl bg-white/[0.06] px-3 py-2.5 mb-2">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#D4A017] to-[#a97b0e] text-[#122a52] text-xs font-bold">
                {{ mb_substr(auth()->user()->nombre, 0, 1) }}{{ mb_substr(auth()->user()->apellido, 0, 1) }}
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold truncate">{{ auth()->user()->nombre }} {{ auth()->user()->apellido }}</p>
                <p class="text-[11px] text-blue-300/70 capitalize">{{ auth()->user()->rol }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-blue-100/70 hover:bg-white/[0.07] hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px] ml-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos['logout'] }}" />
                </svg>
                Cerrar sesión
            </button>
        </form>
    </div>
</aside>
