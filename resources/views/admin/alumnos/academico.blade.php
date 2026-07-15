@extends('layouts.admin')

@section('titulo', 'Registrar Alumno')

@section('contenido')
    <div class="flex gap-8">
        @include('admin.alumnos.partials.stepper', ['paso' => 2])

        <div class="flex-1 bg-white rounded-2xl shadow-sm border border-slate-100 border-t-4 border-[#1E4D8C] p-6 max-w-3xl">
            <h2 class="font-bold text-[#1E4D8C] flex items-center gap-2 mb-1">🎓 Datos Académicos</h2>
            <p class="text-xs text-slate-400 mb-6">Carrera e inscripción del alumno — {{ $personales['nombre'] }} {{ $personales['apellido'] }}</p>

            <form method="POST" action="{{ route('admin.alumnos.academico.store') }}">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">CARRERA *</label>
                        <select name="carrera_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                            <option value="">Seleccioná carrera...</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera->id }}" @selected(($academicos['carrera_id'] ?? null) == $carrera->id)>{{ $carrera->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">AÑO LECTIVO *</label>
                        <select name="anio_lectivo_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                            @foreach ($aniosLectivos as $anio)
                                <option value="{{ $anio->id }}" @selected(($academicos['anio_lectivo_id'] ?? null) == $anio->id)>{{ $anio->anio }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid sm:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">AÑO QUE CURSA *</label>
                        <select name="anio_actual" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                            @foreach ([1, 2, 3] as $anio)
                                <option value="{{ $anio }}" @selected(($academicos['anio_actual'] ?? 1) == $anio)>{{ $anio }}° Año</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">TURNO *</label>
                        <select name="turno" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                            @foreach (['mañana' => 'Mañana', 'tarde' => 'Tarde', 'noche' => 'Noche'] as $valor => $texto)
                                <option value="{{ $valor }}" @selected(($academicos['turno'] ?? 'mañana') === $valor)>{{ $texto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">CONDICIÓN DE INGRESO *</label>
                        <select name="condicion" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                            @foreach (['regular' => 'Regular', 'promocion' => 'Promoción', 'libre' => 'Libre'] as $valor => $texto)
                                <option value="{{ $valor }}" @selected(($academicos['condicion'] ?? 'regular') === $valor)>{{ $texto }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.alumnos.create') }}" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] font-semibold text-sm px-6 py-2.5">← Volver</a>
                    <button type="submit" class="rounded-xl bg-[#1E4D8C] shadow-sm hover:shadow-md hover:bg-[#173d70] transition text-white font-semibold text-sm px-6 py-2.5">Continuar →</button>
                </div>
            </form>
        </div>
    </div>
@endsection
