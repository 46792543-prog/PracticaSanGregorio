@extends('layouts.portal')

@section('titulo', 'Completá tus datos')
@section('subtitulo', 'Primer ingreso al sistema')

@section('contenido')
    @php
        $badgesDoc = [
            'pendiente' => 'bg-red-100 text-red-600',
            'entregado' => 'bg-amber-100 text-amber-700',
            'aprobado' => 'bg-green-100 text-green-700',
            'rechazado' => 'bg-red-100 text-red-600',
        ];
        $etiquetasDoc = [
            'pendiente' => 'Pendiente',
            'entregado' => 'En revisión',
            'aprobado' => 'Aprobado',
            'rechazado' => 'Rechazado',
        ];
    @endphp

    @if ($primerIngreso)
        <div class="mb-6 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm flex items-start gap-2">
            <span>⚠️</span>
            <span>Es tu primer ingreso. Completá y confirmá tus datos personales para poder usar el resto del sistema.</span>
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-bold text-slate-800 mb-5">👤 Datos personales</h3>

            <form method="POST" action="{{ route('mis-datos.update') }}" id="form-mis-datos">
                @csrf
                @method('PUT')

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">NOMBRE</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $alumno->nombre) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">APELLIDO</label>
                        <input type="text" name="apellido" value="{{ old('apellido', $alumno->apellido) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">DNI</label>
                        <input type="text" name="dni" value="{{ old('dni', $alumno->dni) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">FECHA DE NACIMIENTO</label>
                        <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', optional($alumno->fecha_nacimiento)->format('Y-m-d')) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">TELÉFONO</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $alumno->telefono) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">DIRECCIÓN</label>
                        <input type="text" name="direccion" value="{{ old('direccion', $alumno->direccion) }}" placeholder="Calle, número, localidad"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">EMAIL DE CONTACTO</label>
                        <input type="email" name="email" value="{{ old('email', $alumno->email) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                </div>

                @error('dni')<p class="text-xs text-red-500 mt-2">{{ $message }}</p>@enderror
                @error('email')<p class="text-xs text-red-500 mt-2">{{ $message }}</p>@enderror

                <div class="flex justify-end gap-3 mt-8">
                    <button type="submit" formnovalidate
                            class="rounded-lg border border-slate-300 text-slate-600 text-sm font-semibold px-5 py-2.5">
                        Guardar borrador
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-5 py-2.5">
                        Confirmar y continuar →
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 h-fit">
            <h3 class="font-bold text-slate-800 mb-5">📎 Documentación requerida</h3>
            <div class="space-y-4">
                @foreach ($documentos as $documento)
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-700">{{ $documento->nombre }}</p>
                            <p class="text-xs text-slate-400">{{ $documento->obligatorio ? 'Obligatorio' : 'Opcional' }}</p>
                        </div>
                        <span class="shrink-0 text-xs font-semibold rounded-full px-3 py-1 {{ $badgesDoc[$documento->estado_alumno] }}">
                            {{ $etiquetasDoc[$documento->estado_alumno] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
