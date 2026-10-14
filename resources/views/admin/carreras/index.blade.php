@extends('layouts.admin')

@section('titulo', 'Carreras y Planes de Estudio')

@section('contenido')
    <x-sg.encabezado titulo="Carreras y Planes de Estudio" subtitulo="Gestión de oferta académica e itinerarios curriculares" eyebrow="Configuración académica" icono="cap">
        <a href="{{ route('admin.carreras.create') }}" class="sg-btn sg-btn--primario">
            <x-sg.icono nombre="plus" /> Nueva carrera
        </a>
    </x-sg.encabezado>

    <form method="GET" class="mb-6 sg-revelar">
        <div class="sg-campo-icono max-w-xl">
            <x-sg.icono nombre="search" />
            <input type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar por nombre de carrera o código de plan..." onchange="this.form.submit()" maxlength="50"
                   class="sg-input w-full py-3">
        </div>
    </form>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-sg-escalonar>
        @forelse ($carreras as $carrera)
            @php $estadoNombre = $carrera->estadoCarrera->nombre_estado ?? null; @endphp
            <article class="sg-card sg-card--hover overflow-hidden flex flex-col">
                <div class="relative h-24 bg-gradient-to-br from-[#0B3D91] to-[#1565C0] px-5 pt-4 overflow-hidden">
                    <span class="absolute -right-8 -bottom-12 h-32 w-32 rounded-full border-[12px] border-[#FFC107]/25"></span>
                    <span class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 16px 16px;"></span>
                    <div class="relative flex items-start justify-between">
                        <span class="text-[11px] font-bold tracking-widest uppercase text-[#FFD54F]">Res. N° {{ $carrera->resolucion_ministerial ?? '—' }}</span>
                        <span @class([
                                'sg-badge',
                                'sg-badge--verde' => $estadoNombre === 'Activa',
                                'sg-badge--gris' => $estadoNombre === 'En espera',
                                'sg-badge--rojo' => $estadoNombre === 'Inactiva',
                            ])>{{ $estadoNombre ?? '—' }}</span>
                    </div>
                </div>
                <div class="px-5 -mt-7 relative">
                    <span class="sg-chip sg-chip--grande bg-white text-[#0B3D91] shadow-md ring-4 ring-white"><x-sg.icono nombre="cap" /></span>
                </div>
                <div class="px-5 pt-3 pb-5 flex-1">
                    <h3 class="font-display font-bold text-[17px] text-slate-900 leading-snug">{{ $carrera->nombre_carrera }}</h3>
                    @if ($carrera->familia_profesional)
                        <p class="text-xs text-slate-500 mt-1">Familia Profesional: {{ $carrera->familia_profesional }}</p>
                    @endif
                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Duración</p>
                            <p class="font-display font-bold text-slate-800">{{ $carrera->duracion_anos }} Años</p>
                        </div>
                        <a href="{{ route('admin.carreras.plan', $carrera) }}" class="rounded-xl bg-blue-50 px-3 py-2.5 hover:bg-blue-100 transition-colors">
                            <p class="text-[11px] font-semibold text-[#1565C0] uppercase tracking-wide">Materias</p>
                            <p class="font-display font-bold text-[#0B3D91]">{{ $carrera->materias_count }} asignaturas</p>
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-4 border-t border-slate-100 text-xs font-semibold">
                    <a href="{{ route('admin.carreras.plan', $carrera) }}" class="flex flex-col items-center gap-1 py-3 text-slate-500 hover:bg-blue-50 hover:text-[#0B3D91] transition-colors"><x-sg.icono nombre="eye" class="h-4 w-4" />Ver plan</a>
                    <a href="{{ route('admin.carreras.materias', $carrera) }}" class="flex flex-col items-center gap-1 py-3 text-slate-500 hover:bg-blue-50 hover:text-[#0B3D91] transition-colors border-l border-slate-100"><x-sg.icono nombre="book" class="h-4 w-4" />Materias</a>
                    <a href="{{ route('admin.carreras.correlativas', $carrera) }}" class="flex flex-col items-center gap-1 py-3 text-slate-500 hover:bg-blue-50 hover:text-[#0B3D91] transition-colors border-l border-slate-100"><x-sg.icono nombre="link" class="h-4 w-4" />Correlativas</a>
                    <a href="{{ route('admin.carreras.edit', $carrera) }}" class="flex flex-col items-center gap-1 py-3 text-slate-500 hover:bg-blue-50 hover:text-[#0B3D91] transition-colors border-l border-slate-100"><x-sg.icono nombre="pencil" class="h-4 w-4" />Editar</a>
                </div>
            </article>
        @empty
            <div class="sg-card md:col-span-2 xl:col-span-3">
                <x-sg.vacio icono="cap" titulo="Sin carreras">No hay carreras cargadas.</x-sg.vacio>
            </div>
        @endforelse
    </div>
@endsection
