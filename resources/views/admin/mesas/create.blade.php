@extends('layouts.admin')

@section('titulo', 'Nueva mesa de examen')
@section('subtitulo', 'Gestión académica / Configurar mesa')

@section('contenido')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 max-w-3xl">
        <h2 class="font-bold text-slate-800 flex items-center gap-2 mb-6">📋 Completar datos de la mesa</h2>

        <form method="POST" action="{{ route('admin.mesas.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">SELECCIONAR MATERIA *</label>
                <select name="materia_id" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                    <option value="">Ej: Fundamentos de Enfermería Básica y Comunitaria</option>
                    @foreach ($carreras as $carrera)
                        <optgroup label="{{ $carrera->nombre }}">
                            @foreach ($carrera->materias as $materia)
                                <option value="{{ $materia->id }}">{{ $materia->nombre }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">FECHA DEL EXAMEN *</label>
                    <input type="date" name="fecha_examen" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">TURNO EXAMEN *</label>
                    <select name="turno" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                        @foreach ($turnos as $valor => $texto)
                            <option value="{{ $valor }}">{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">APERTURA DE INSCRIPCIÓN *</label>
                    <input type="date" name="fecha_inicio_inscripcion" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">CIERRE DE INSCRIPCIÓN *</label>
                    <input type="date" name="fecha_fin_inscripcion" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">LLAMADO *</label>
                <select name="llamado" required class="w-full sm:w-64 rounded-xl border border-slate-300 px-4 py-3 text-sm">
                    <option value="primer_llamado">1er llamado</option>
                    <option value="segundo_llamado">2do llamado</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-2">TRIBUNAL EXAMINADOR (PRESIDENTE / VOCALES)</label>
                <div class="grid sm:grid-cols-3 gap-3">
                    <select name="presidente_id" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                        <option value="">Presidente...</option>
                        @foreach ($profesores as $profesor)
                            <option value="{{ $profesor->id }}">{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                        @endforeach
                    </select>
                    <select name="vocal1_id" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                        <option value="">Vocal 1...</option>
                        @foreach ($profesores as $profesor)
                            <option value="{{ $profesor->id }}">{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                        @endforeach
                    </select>
                    <select name="vocal2_id" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                        <option value="">Vocal 2...</option>
                        @foreach ($profesores as $profesor)
                            <option value="{{ $profesor->id }}">{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="rounded-xl bg-[#D4A017] hover:brightness-95 text-white font-bold text-sm px-8 py-3.5">Crear mesa</button>
                <a href="{{ route('admin.mesas.index') }}" class="rounded-xl border border-slate-300 text-slate-600 font-bold text-sm px-8 py-3.5">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
