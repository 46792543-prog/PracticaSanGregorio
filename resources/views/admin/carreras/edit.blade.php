@extends('layouts.admin')

@section('titulo', 'Carreras y Planes de Estudio')

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">Carreras y Planes de Estudio</h1>
        <p class="text-sm text-slate-400">Gestión de oferta académica e itinerarios curriculares</p>
    </div>

    <p class="text-sm text-slate-400 mb-4">
        <a href="{{ route('admin.carreras.index') }}" class="hover:underline">Carreras y planes</a> / <span class="text-blue-600 font-semibold">Editar carrera</span>
    </p>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 max-w-3xl">
        <h2 class="font-bold text-slate-800 mb-1">Editar Carrera</h2>
        <p class="text-sm text-slate-400 mb-6">Modificá los datos institucionales y de resolución de la carrera.</p>

        <form method="POST" action="{{ route('admin.carreras.update', $carrera) }}" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre Oficial de la Carrera *</label>
                <input type="text" maxlength="50" name="nombre_carrera" value="{{ old('nombre_carrera', $carrera->nombre_carrera) }}" required placeholder="Ej. Tecnicatura Superior en Desarrollo de Software"
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Familia Profesional</label>
                <input type="text" maxlength="100" name="familia_profesional" value="{{ old('familia_profesional', $carrera->familia_profesional) }}" placeholder="Ej. Salud"
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Número de Resolución / Decreto</label>
                <input type="text" maxlength="25" name="resolucion_ministerial" value="{{ old('resolucion_ministerial', $carrera->resolucion_ministerial) }}" placeholder="Ej. Res. N° 102/22"
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Duración de la Carrera *</label>
                <select name="duracion_anos" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    @foreach ([1, 2, 3, 4] as $anio)
                        <option value="{{ $anio }}" @selected(old('duracion_anos', $carrera->duracion_anos) == $anio)>{{ $anio }} {{ $anio === 1 ? 'Año' : 'Años' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Estado</label>
                <select name="id_estado_carrera" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->id_estado_carrera }}" @selected(old('id_estado_carrera', $carrera->id_estado_carrera) == $estado->id_estado_carrera)>{{ $estado->nombre_estado }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('admin.carreras.index') }}" class="rounded-xl border border-slate-300 text-slate-600 font-bold text-sm px-8 py-3">Cancelar</a>
                <button type="submit" class="rounded-xl bg-[#D4A017] hover:brightness-95 text-[#1E4D8C] font-bold text-sm px-8 py-3">Guardar cambios</button>
            </div>
        </form>
    </div>
@endsection
