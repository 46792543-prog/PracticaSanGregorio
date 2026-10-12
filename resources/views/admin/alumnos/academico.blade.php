@extends('layouts.admin')

@section('titulo', 'Registrar Alumno')

@section('contenido')
    <x-sg.encabezado titulo="Registrar Alumno" subtitulo="Completá los tres pasos para dar de alta al alumno" eyebrow="Alumnos · Nueva inscripción" icono="user-plus" />

    <div class="flex flex-col lg:flex-row gap-6 items-start">
        @include('admin.alumnos.partials.stepper', ['paso' => 2])

        <div class="flex-1 w-full max-w-3xl sg-card sg-revelar">
            <div class="sg-card__head">
                <div class="sg-card__titulo">
                    <span class="sg-chip sg-chip--amarillo"><x-sg.icono nombre="cap" /></span>
                    <div>
                        <h3>Datos Académicos</h3>
                        <p>Carrera e inscripción del alumno — {{ $personales['nombre'] }} {{ $personales['apellido'] }}</p>
                    </div>
                </div>
            </div>
            <div class="sg-card__body">

            <form method="POST" action="{{ route('admin.alumnos.academico.store') }}">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="sg-label">CARRERA *</label>
                        <select name="carrera_id" required class="sg-input w-full">
                            <option value="">Seleccioná carrera...</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera->id_carrera }}" @selected(($academicos['carrera_id'] ?? null) == $carrera->id_carrera)>{{ $carrera->nombre_carrera }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sg-label">AÑO LECTIVO *</label>
                        <select name="anio_lectivo_id" required class="sg-input w-full">
                            @foreach ($aniosLectivos as $anio)
                                <option value="{{ $anio->id_anio_lectivo }}" @selected(($academicos['anio_lectivo_id'] ?? null) == $anio->id_anio_lectivo)>{{ $anio->anio }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid sm:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="sg-label">AÑO QUE CURSA *</label>
                        <select name="anio_cursada_id" required class="sg-input w-full">
                            @foreach ($aniosCursada as $anioCursada)
                                <option value="{{ $anioCursada->id_anio_cursada }}" @selected(($academicos['anio_cursada_id'] ?? null) == $anioCursada->id_anio_cursada)>{{ $anioCursada->nombre_anio }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sg-label">TURNO *</label>
                        <select name="turno_cursada_id" required class="sg-input w-full">
                            @foreach ($turnos as $turno)
                                <option value="{{ $turno->id_turno_cursada }}" @selected(($academicos['turno_cursada_id'] ?? null) == $turno->id_turno_cursada)>{{ $turno->nombre_turno }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sg-label">CONDICIÓN DE INGRESO *</label>
                        <select name="condicion_id" required class="sg-input w-full">
                            @foreach ($condiciones as $condicion)
                                <option value="{{ $condicion->id_condicion }}" @selected(($academicos['condicion_id'] ?? null) == $condicion->id_condicion)>{{ $condicion->nombre_condicion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-between gap-3 pt-5 mt-2 border-t border-slate-100">
                    <a href="{{ route('admin.alumnos.create') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="arrow-left" /> Volver</a>
                    <button type="submit" class="sg-btn sg-btn--azul">Continuar <x-sg.icono nombre="arrow-right" class="sg-flecha" /></button>
                </div>
            </form>
            </div>
        </div>
    </div>
@endsection
