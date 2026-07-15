@extends('layouts.director')

@section('titulo', 'Panel principal')

@section('contenido')
    @php
        $iconos = [
            'users' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
            'cash' => 'M2.25 8.25h19.5M2.25 8.25v9a2.25 2.25 0 0 0 2.25 2.25h15a2.25 2.25 0 0 0 2.25-2.25v-9M2.25 8.25l1.72-3.44A1.5 1.5 0 0 1 5.31 4h13.38a1.5 1.5 0 0 1 1.34.81l1.72 3.44M12 15a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z',
            'card' => 'M2.25 8.25h19.5M2.25 8.25v9a2.25 2.25 0 0 0 2.25 2.25h15a2.25 2.25 0 0 0 2.25-2.25v-9M2.25 8.25l1.72-3.44A1.5 1.5 0 0 1 5.31 4h13.38a1.5 1.5 0 0 1 1.34.81l1.72 3.44M12 15a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z',
            'grid' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z',
        ];
    @endphp

    <div class="relative rounded-3xl bg-gradient-to-br from-[#173d70] via-[#1E4D8C] to-[#2a63b0] text-white px-8 py-7 mb-8 overflow-hidden shadow-lg shadow-blue-900/10">
        <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/5"></div>
        <div class="absolute right-24 -bottom-10 h-32 w-32 rounded-full bg-[#D4A017]/10"></div>
        <div class="relative flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Buen día, Dra. {{ auth()->user()->apellido }} 👋</h1>
                <p class="text-sm text-blue-100/80 mt-1">{{ \App\Support\FechaEsp::larga(now()) }}</p>
            </div>
            <a href="{{ route('director.cuotas.index') }}" class="rounded-xl bg-[#D4A017] hover:brightness-105 text-[#122a52] font-bold text-sm px-5 py-3 shadow-md shadow-black/10 transition">
                + Registrar pago
            </a>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-4 ring-blue-100 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos['users'] }}" />
                </svg>
            </span>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Alumnos activos</p>
            <p class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1">{{ $alumnosActivos }}</p>
            @if ($alumnosNuevos > 0)
                <p class="text-[11px] text-emerald-600 font-semibold mt-1">↑ +{{ $alumnosNuevos }} este mes</p>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-4 ring-emerald-100 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos['cash'] }}" />
                </svg>
            </span>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Ingresos del mes</p>
            <p class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1">$ {{ number_format($ingresosMes, 0, ',', '.') }}</p>
            @if ($variacionIngresos !== null)
                <p class="text-[11px] {{ $variacionIngresos >= 0 ? 'text-emerald-600' : 'text-rose-500' }} font-semibold mt-1">
                    {{ $variacionIngresos >= 0 ? '↑' : '↓' }} {{ abs($variacionIngresos) }}% vs mes anterior
                </p>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-2 mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-[#D4A017]">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 9 3l9 6.5-5.25 10.5L3.75 13.5Z" />
        </svg>
        <h3 class="font-bold text-slate-700 text-sm">Acciones rápidas</h3>
    </div>
    <div class="grid md:grid-cols-2 gap-4">
        <a href="{{ route('director.cuotas.index') }}" class="group bg-white rounded-2xl shadow-sm border border-slate-100 p-4 flex items-start gap-3 hover:shadow-md hover:-translate-y-0.5 transition-all">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos['card'] }}" />
                </svg>
            </span>
            <span class="min-w-0">
                <span class="block font-semibold text-sm text-slate-700 group-hover:text-[#1E4D8C]">Registrar Pagos</span>
                <span class="block text-xs text-slate-400 mt-0.5">Cobrar cuotas y asentarlas en caja</span>
            </span>
        </a>
        <a href="{{ route('admin.panel.index') }}" class="group bg-white rounded-2xl shadow-sm border border-slate-100 p-4 flex items-start gap-3 hover:shadow-md hover:-translate-y-0.5 transition-all">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos['grid'] }}" />
                </svg>
            </span>
            <span class="min-w-0">
                <span class="block font-semibold text-sm text-slate-700 group-hover:text-[#1E4D8C]">Ir al Panel Administrativo</span>
                <span class="block text-xs text-slate-400 mt-0.5">Alumnos, carreras, mesas y actas</span>
            </span>
        </a>
    </div>
@endsection
