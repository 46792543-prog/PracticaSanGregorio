@extends('layouts.director')

@section('titulo', 'Nuevo Usuario')
@section('subtitulo', 'Dar de alta a un secretario o director')

@section('contenido')
    <div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-slate-100 border-t-4 border-[#1E4D8C] p-6">
        <h2 class="font-bold text-[#1E4D8C] flex items-center gap-2 mb-1">👤 Datos del usuario</h2>
        <p class="text-xs text-slate-400 mb-6">La clave inicial se genera automáticamente y se muestra una sola vez al confirmar — hay que entregársela a la persona.</p>

        <form method="POST" action="{{ route('director.usuarios.store') }}">
            @csrf
            <div class="grid sm:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">DNI *</label>
                    <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="8" maxlength="8" name="dni" value="{{ old('dni') }}" required placeholder="Ej: 38472190"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">APELLIDO *</label>
                    <input type="text" data-solo="letras" data-max-len="50" maxlength="50" name="apellido" value="{{ old('apellido') }}" required placeholder="Ej: González"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">NOMBRE *</label>
                    <input type="text" data-solo="letras" data-max-len="50" maxlength="50" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Lucas Andrés"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-500 mb-1">EMAIL *</label>
                <input type="email" maxlength="100" name="email" value="{{ old('email') }}" required placeholder="usuario@email.com"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
            </div>
            <div class="mb-6">
                <label class="block text-xs font-semibold text-slate-500 mb-1">ROL *</label>
                <select name="id_rol" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    <option value="">Seleccioná un rol...</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id_rol }}" @selected(old('id_rol') == $rol->id_rol)>{{ $rol->nombre_rol }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('director.usuarios.index') }}" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] font-semibold text-sm px-6 py-2.5">← Volver</a>
                <button type="submit" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow-md hover:brightness-105 transition text-[#1E4D8C] font-bold text-sm px-6 py-2.5">
                    Registrar usuario y generar clave
                </button>
            </div>
        </form>
    </div>
@endsection
