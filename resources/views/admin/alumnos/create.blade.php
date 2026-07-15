@extends('layouts.admin')

@section('titulo', 'Registrar Alumno')

@section('contenido')
    <div class="flex gap-8">
        @include('admin.alumnos.partials.stepper', ['paso' => 1])

        <div class="flex-1 bg-white rounded-2xl shadow-sm border border-slate-100 border-t-4 border-[#1E4D8C] p-6 max-w-3xl">
            <h2 class="font-bold text-[#1E4D8C] flex items-center gap-2 mb-1">👤 Datos Personales</h2>
            <p class="text-xs text-slate-400 mb-6">Información del alumno según DNI</p>

            <form method="POST" action="{{ route('admin.alumnos.store') }}">
                @csrf
                <div class="grid sm:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">DNI *</label>
                        <input type="text" name="dni" value="{{ old('dni', $datos['dni'] ?? '') }}" required placeholder="Ej: 38.472.190"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">APELLIDO *</label>
                        <input type="text" name="apellido" value="{{ old('apellido', $datos['apellido'] ?? '') }}" required placeholder="Ej: González"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">NOMBRE *</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $datos['nombre'] ?? '') }}" required placeholder="Ej: Lucas Andrés"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">FECHA DE NACIMIENTO *</label>
                        <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $datos['fecha_nacimiento'] ?? '') }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">TELÉFONO</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $datos['telefono'] ?? '') }}" placeholder="Ej: +54 388 412-5678"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">DIRECCIÓN</label>
                    <input type="text" name="direccion" value="{{ old('direccion', $datos['direccion'] ?? '') }}" placeholder="Calle, número, barrio, localidad"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                </div>
                <div class="grid sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">EMAIL *</label>
                        <input type="email" name="email" value="{{ old('email', $datos['email'] ?? '') }}" required placeholder="alumno@email.com"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">LOCALIDAD</label>
                        <input type="text" name="localidad" value="{{ old('localidad', $datos['localidad'] ?? '') }}" placeholder="Ej: San Pedro de Jujuy"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.alumnos.index') }}" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] font-semibold text-sm px-6 py-2.5">← Volver</a>
                    <button type="submit" class="rounded-xl bg-[#1E4D8C] shadow-sm hover:shadow-md hover:bg-[#173d70] transition text-white font-semibold text-sm px-6 py-2.5">Continuar →</button>
                </div>
            </form>
        </div>
    </div>
@endsection
