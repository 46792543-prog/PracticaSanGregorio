@extends('layouts.director')

@section('titulo', 'Usuarios del Sistema')
@section('subtitulo', 'Secretarios y directores con acceso al sistema')

@section('contenido')
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-slate-400">Acá se da de alta a los usuarios de secretaría y dirección. El alta de alumnos se hace desde el panel administrativo.</p>
        <a href="{{ route('director.usuarios.create') }}" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow-md hover:brightness-105 transition text-white font-semibold text-sm px-5 py-2.5 shrink-0 ml-4">
            + Nuevo usuario
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left text-xs font-semibold text-slate-500 border-b border-slate-100">
                    <th class="px-6 py-3">Nombre</th>
                    <th class="px-6 py-3">DNI</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Rol</th>
                    <th class="px-6 py-3">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($usuarios as $usuario)
                    <tr>
                        <td class="px-6 py-3.5 font-semibold text-slate-700">{{ $usuario->persona->apellido }}, {{ $usuario->persona->nombre }}</td>
                        <td class="px-6 py-3.5 text-slate-500">{{ $usuario->persona->dni }}</td>
                        <td class="px-6 py-3.5 text-slate-500">{{ $usuario->email }}</td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ match ($usuario->rol->nombre_rol) { 'Director' => 'bg-[#1E4D8C]/10 text-[#1E4D8C]', 'Secretario Administrativo' => 'bg-sky-50 text-sky-700', default => 'bg-amber-50 text-amber-700' } }}">
                                {{ $usuario->rol->nombre_rol }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $usuario->estadoUsuario->nombre_estado === 'Activo' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                                {{ $usuario->estadoUsuario->nombre_estado }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-slate-400">Todavía no hay secretarios ni directores cargados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
