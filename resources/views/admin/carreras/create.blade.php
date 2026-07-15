@extends('layouts.admin')

@section('titulo', 'Carreras y Planes de Estudio')

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">Carreras y Planes de Estudio</h1>
        <p class="text-sm text-slate-400">Gestión de oferta académica e itinerarios curriculares</p>
    </div>

    <p class="text-sm text-slate-400 mb-4">
        <a href="{{ route('admin.carreras.index') }}" class="hover:underline">Carreras y planes</a> / <span class="text-blue-600 font-semibold">Nueva carrera</span>
    </p>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 max-w-3xl">
        <h2 class="font-bold text-slate-800 mb-1">Formulario de Alta de Carrera</h2>
        <p class="text-sm text-slate-400 mb-6">Ingrese los datos institucionales y de resolución para la apertura del nuevo trayecto educativo.</p>

        <form method="POST" action="{{ route('admin.carreras.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre Oficial de la Carrera *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej. Tecnicatura Superior en Desarrollo de Software"
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Familia Profesional</label>
                <input type="text" name="familia_profesional" value="{{ old('familia_profesional') }}" placeholder="Ej. Tecnología y Sistemas"
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Número de Resolución / Decreto</label>
                <input type="text" name="resolucion_ministerial" value="{{ old('resolucion_ministerial') }}" placeholder="Ej. Res. N° 102/22"
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Duración de la Carrera *</label>
                <select name="duracion_anios" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    @foreach ([1, 2, 3, 4] as $anio)
                        <option value="{{ $anio }}" @selected(old('duracion_anios') == $anio)>{{ $anio }} {{ $anio === 1 ? 'Año' : 'Años' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Estado</label>
                <select name="estado" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    <option value="activa">Activa — habilitar inmediatamente para inscripciones</option>
                    <option value="en_espera">En espera</option>
                    <option value="inactiva">Inactiva</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('admin.carreras.index') }}" class="rounded-xl border border-slate-300 text-slate-600 font-bold text-sm px-8 py-3">Volver</a>
                <button type="submit" class="rounded-xl bg-[#D4A017] hover:brightness-95 text-[#1E4D8C] font-bold text-sm px-8 py-3">Siguiente</button>
            </div>
        </form>
    </div>
@endsection
