@extends('layouts.director')

@section('titulo', 'Panel principal')

@section('contenido')
    <x-sg.hero saludo="Buen día, Dra. {{ e(auth()->user()->apellido) }} <span class='sg-saludo-mano'>👋</span>" etiqueta="Dirección general">
        {{ \App\Support\FechaEsp::larga(now()) }} · Resumen institucional y financiero.
        <x-slot:acciones>
            <a href="{{ route('admin.panel.index') }}" class="sg-btn bg-white/10 text-white border-white/25 hover:bg-white/20">
                <x-sg.icono nombre="grid" /> Panel administrativo
            </a>
            <a href="{{ route('director.cuotas.index') }}" class="sg-btn sg-btn--primario">
                <x-sg.icono nombre="plus" /> Registrar pago
            </a>
        </x-slot:acciones>
    </x-sg.hero>

    @if ($alertasCount > 0)
        <x-sg.banner-alertas :cantidad="$alertasCount" :destacadas="$alertasDestacadas" :href="route('director.alertas.index')" />
    @endif

    <div class="grid gap-5 sm:grid-cols-2 mb-10" data-sg-escalonar>
        <x-sg.stat etiqueta="Alumnos activos" :valor="$alumnosActivos" icono="users" tono="azul">
            @if ($alumnosNuevos > 0)
                <span class="sg-badge sg-badge--verde"><x-sg.icono nombre="trending-up" class="h-3.5 w-3.5" /> +{{ $alumnosNuevos }} este mes</span>
            @endif
        </x-sg.stat>

        <x-sg.stat etiqueta="Ingresos del mes" :valor="'$ ' . number_format($ingresosMes, 0, ',', '.')" icono="banknotes" tono="verde">
            @if ($variacionIngresos !== null)
                <span class="sg-badge {{ $variacionIngresos >= 0 ? 'sg-badge--verde' : 'sg-badge--rojo' }}">
                    <x-sg.icono :nombre="$variacionIngresos >= 0 ? 'trending-up' : 'trending-down'" class="h-3.5 w-3.5" />
                    {{ abs($variacionIngresos) }}% vs mes anterior
                </span>
            @endif
        </x-sg.stat>
    </div>

    <div class="sg-seccion-titulo sg-revelar">
        <span class="sg-chip sg-chip--chico sg-chip--amarillo"><x-sg.icono nombre="bolt" /></span>
        <h2>Accesos rápidos</h2>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-sg-escalonar>
        @foreach ([
            ['director.cuotas.index', 'Registrar pagos', 'Cobrar cuotas y asentarlas en caja', 'card', 'verde'],
            ['director.pagos.index', 'Estado de pagos', 'Quién está al día y quién debe', 'chart', 'azul'],
            ['director.caja.index', 'Libro de caja', 'Ingresos y egresos del período', 'book', 'amarillo'],
            ['director.reportes.ingresos.index', 'Reporte de ingresos', 'Totales por mes, concepto y medio', 'trending-up', 'violeta'],
        ] as [$ruta, $titulo, $texto, $icono, $tono])
            <a href="{{ route($ruta) }}" class="group sg-card sg-card--hover p-5 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <span class="sg-chip sg-chip--grande sg-chip--{{ $tono }}"><x-sg.icono :nombre="$icono" /></span>
                    <x-sg.icono nombre="arrow-right" class="h-4 w-4 text-slate-300 transition-all group-hover:text-[#0B3D91] group-hover:translate-x-1" />
                </div>
                <div>
                    <p class="font-display font-bold text-[15px] text-slate-800 group-hover:text-[#0B3D91] transition-colors">{{ $titulo }}</p>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $texto }}</p>
                </div>
            </a>
        @endforeach
    </div>
@endsection
