@extends('layouts.admin')

@section('titulo', 'Registrar Alumno')

@section('contenido')
    <div class="flex gap-8">
        @include('admin.alumnos.partials.stepper', ['paso' => 3])

        <div class="flex-1 bg-white rounded-2xl shadow-sm border border-slate-100 border-t-4 border-[#1E4D8C] p-6 max-w-3xl">
            <h2 class="font-bold text-[#1E4D8C] flex items-center gap-2 mb-1">🔒 Acceso al Sistema</h2>
            <p class="text-xs text-slate-400 mb-6">Credenciales iniciales del alumno</p>

            <div class="flex items-start gap-3 rounded-xl bg-blue-50 border border-[#1E4D8C]/30 text-[#1E4D8C] text-sm px-4 py-3 mb-6">
                <span>ℹ️</span>
                <span>El usuario se crea con el email ingresado. La clave inicial se genera automáticamente en formato ISG-[últimos 4 del DNI]-[código] y debe entregarse al alumno al registrar.</span>
            </div>

            <form method="POST" action="{{ route('admin.alumnos.confirmar') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">USUARIO (EMAIL)</label>
                    <input type="text" disabled value="{{ $personales['email'] }}"
                           class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-500">
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">NOMBRE/S</label>
                    <input type="text" disabled value="{{ $personales['nombre'] }} {{ $personales['apellido'] }}"
                           class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-500">
                </div>
                <div class="mb-8">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">CLAVE INICIAL GENERADA</label>
                    <input type="text" disabled value="{{ $clave }}"
                           class="w-full rounded-lg border border-[#D4A017] bg-amber-50 px-3 py-3 text-base font-bold text-[#1E4D8C] tracking-wide">
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.alumnos.academico') }}" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] font-semibold text-sm px-6 py-2.5">← Volver</a>
                    <button type="submit" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow-md hover:brightness-105 transition text-[#1E4D8C] font-bold text-sm px-6 py-2.5">
                        Registrar Alumno y generar clave
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
