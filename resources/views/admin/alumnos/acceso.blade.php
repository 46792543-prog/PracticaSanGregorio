@extends('layouts.admin')

@section('titulo', 'Registrar Alumno')

@section('contenido')
    <x-sg.encabezado titulo="Registrar Alumno" subtitulo="Completá los tres pasos para dar de alta al alumno" eyebrow="Alumnos · Nueva inscripción" icono="user-plus" />

    <div class="flex flex-col lg:flex-row gap-6 items-start">
        @include('admin.alumnos.partials.stepper', ['paso' => 3])

        <div class="flex-1 w-full max-w-3xl sg-card sg-revelar">
            <div class="sg-card__head">
                <div class="sg-card__titulo">
                    <span class="sg-chip sg-chip--verde"><x-sg.icono nombre="key" /></span>
                    <div>
                        <h3>Acceso al Sistema</h3>
                        <p>Credenciales iniciales del alumno</p>
                    </div>
                </div>
            </div>
            <div class="sg-card__body">

            <div class="sg-aviso sg-aviso--info mb-6">
                <x-sg.icono nombre="info" class="h-5 w-5 shrink-0" />
                <span>El usuario se crea con el email ingresado. La clave inicial se genera automáticamente en formato ISG-[últimos 4 del DNI]-[código] y se le envía al alumno por mail al registrar. En su primer ingreso el sistema le pide cambiarla por una contraseña propia.</span>
            </div>

            <form method="POST" action="{{ route('admin.alumnos.confirmar') }}">
                @csrf
                <div class="mb-4">
                    <label class="sg-label">USUARIO (EMAIL)</label>
                    <input type="text" disabled value="{{ $personales['email'] }}"
                           class="sg-input w-full bg-slate-50 text-slate-500">
                </div>
                <div class="mb-6">
                    <label class="sg-label">NOMBRE/S</label>
                    <input type="text" disabled value="{{ $personales['nombre'] }} {{ $personales['apellido'] }}"
                           class="sg-input w-full bg-slate-50 text-slate-500">
                </div>
                <div class="mb-8">
                    <label class="sg-label">CLAVE INICIAL GENERADA</label>
                    <input type="text" disabled value="{{ $clave }}"
                           class="sg-input w-full !border-[#FFC107] !bg-amber-50 text-lg font-display font-bold text-[#0B3D91] tracking-widest">
                </div>

                <div class="flex justify-between gap-3 pt-5 mt-2 border-t border-slate-100">
                    <a href="{{ route('admin.alumnos.academico') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="arrow-left" /> Volver</a>
                    <button type="submit" class="sg-btn sg-btn--primario">
                        <x-sg.icono nombre="check-circle" /> Registrar Alumno y generar clave
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>
@endsection
