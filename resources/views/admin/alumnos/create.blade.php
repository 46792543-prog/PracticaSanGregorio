@extends('layouts.admin')

@section('titulo', 'Registrar Alumno')

@section('contenido')
    <x-sg.encabezado titulo="Registrar Alumno" subtitulo="Completá los tres pasos para dar de alta al alumno" eyebrow="Alumnos · Nueva inscripción" icono="user-plus" />

    <div class="flex flex-col lg:flex-row gap-6 items-start">
        @include('admin.alumnos.partials.stepper', ['paso' => 1])

        <div class="flex-1 w-full max-w-3xl sg-card sg-revelar">
            <div class="sg-card__head">
                <div class="sg-card__titulo">
                    <span class="sg-chip sg-chip--azul"><x-sg.icono nombre="user" /></span>
                    <div>
                        <h3>Datos Personales</h3>
                        <p>Información del alumno según DNI</p>
                    </div>
                </div>
            </div>
            <div class="sg-card__body">

            <form method="POST" action="{{ route('admin.alumnos.store') }}">
                @csrf
                <div class="grid sm:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="sg-label">DNI *</label>
                        <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="8" maxlength="8" name="dni" value="{{ old('dni', $datos['dni'] ?? '') }}" required placeholder="Ej: 38472190"
                               class="sg-input w-full">
                    </div>
                    <div>
                        <label class="sg-label">APELLIDO *</label>
                        <input type="text" data-solo="letras" data-max-len="50" maxlength="50" name="apellido" value="{{ old('apellido', $datos['apellido'] ?? '') }}" required placeholder="Ej: González"
                               class="sg-input w-full">
                    </div>
                    <div>
                        <label class="sg-label">NOMBRE *</label>
                        <input type="text" data-solo="letras" data-max-len="50" maxlength="50" name="nombre" value="{{ old('nombre', $datos['nombre'] ?? '') }}" required placeholder="Ej: Lucas Andrés"
                               class="sg-input w-full">
                    </div>
                </div>
                <div class="mb-6">
                    <label class="sg-label">EMAIL *</label>
                    <input type="email" maxlength="100" name="email" value="{{ old('email', $datos['email'] ?? '') }}" required placeholder="alumno@email.com"
                           class="sg-input w-full">
                </div>
                <p class="text-xs text-slate-400 mb-6">El resto de los datos personales (fecha de nacimiento, contacto, domicilio, etc.) los va a completar el alumno desde su propio portal, en "Mi ficha de inscripción".</p>

                <div class="flex justify-between gap-3 pt-5 mt-2 border-t border-slate-100">
                    <a href="{{ route('admin.alumnos.index') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="arrow-left" /> Volver</a>
                    <button type="submit" class="sg-btn sg-btn--azul">Continuar <x-sg.icono nombre="arrow-right" class="sg-flecha" /></button>
                </div>
            </form>
            </div>
        </div>
    </div>
@endsection
