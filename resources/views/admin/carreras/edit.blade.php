@extends('layouts.admin')

@section('titulo', 'Carreras y Planes de Estudio')

@section('contenido')
    <x-sg.encabezado titulo="Editar carrera" subtitulo="Carreras y Planes de Estudio · Gestión de oferta académica" eyebrow="Configuración académica" icono="cap">
        <a href="{{ route('admin.carreras.index') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="arrow-left" /> Carreras y planes</a>
    </x-sg.encabezado>

    <div class="grid gap-6 lg:grid-cols-3 items-start">
    <div class="sg-card sg-revelar lg:col-span-2">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--azul"><x-sg.icono nombre="pencil" /></span>
                <div><h3>Editar Carrera</h3><p>Modificá los datos institucionales y de resolución de la carrera.</p></div>
            </div>
        </div>
        <div class="sg-card__body">

        <form method="POST" action="{{ route('admin.carreras.update', $carrera) }}" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label class="sg-label">Nombre Oficial de la Carrera *</label>
                <input type="text" maxlength="50" name="nombre_carrera" value="{{ old('nombre_carrera', $carrera->nombre_carrera) }}" required placeholder="Ej. Tecnicatura Superior en Desarrollo de Software"
                       class="sg-input w-full">
            </div>
            <div>
                <label class="sg-label">Familia Profesional</label>
                <input type="text" maxlength="100" name="familia_profesional" value="{{ old('familia_profesional', $carrera->familia_profesional) }}" placeholder="Ej. Salud"
                       class="sg-input w-full">
            </div>
            <div>
                <label class="sg-label">Número de Resolución / Decreto</label>
                <input type="text" maxlength="25" name="resolucion_ministerial" value="{{ old('resolucion_ministerial', $carrera->resolucion_ministerial) }}" placeholder="Ej. Res. N° 102/22"
                       class="sg-input w-full">
            </div>
            <div>
                <label class="sg-label">Duración de la Carrera *</label>
                <select name="duracion_anos" class="sg-input w-full">
                    @foreach ([1, 2, 3, 4] as $anio)
                        <option value="{{ $anio }}" @selected(old('duracion_anos', $carrera->duracion_anos) == $anio)>{{ $anio }} {{ $anio === 1 ? 'Año' : 'Años' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="sg-label">Estado</label>
                <select name="id_estado_carrera" class="sg-input w-full">
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->id_estado_carrera }}" @selected(old('id_estado_carrera', $carrera->id_estado_carrera) == $estado->id_estado_carrera)>{{ $estado->nombre_estado }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-5 border-t border-slate-100">
                <a href="{{ route('admin.carreras.index') }}" class="sg-btn sg-btn--borde">Cancelar</a>
                <button type="submit" class="sg-btn sg-btn--primario"><x-sg.icono nombre="check" /> Guardar cambios</button>
            </div>
        </form>
        </div>
    </div>

    <aside class="sg-card sg-revelar p-6 bg-gradient-to-br from-blue-50 to-white">
        <span class="sg-chip sg-chip--marca sg-chip--grande mb-4"><x-sg.icono nombre="info" /></span>
        <h3 class="font-display font-bold text-slate-800 mb-2">Antes de guardar</h3>
        <ul class="space-y-2.5 text-sm text-slate-600">
            <li class="flex gap-2"><x-sg.icono nombre="check" class="h-4 w-4 text-emerald-500 mt-0.5 shrink-0" /> Usá el nombre oficial tal como figura en la resolución.</li>
            <li class="flex gap-2"><x-sg.icono nombre="check" class="h-4 w-4 text-emerald-500 mt-0.5 shrink-0" /> La duración define los años del plan de estudio.</li>
            <li class="flex gap-2"><x-sg.icono nombre="check" class="h-4 w-4 text-emerald-500 mt-0.5 shrink-0" /> Después vas a poder cargar materias y correlativas.</li>
        </ul>
    </aside>
    </div>
@endsection
