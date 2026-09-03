@extends('layouts.director')

@section('titulo', 'Configuración Institucional')
@section('subtitulo', 'Datos generales de la institución, usados en actas y documentación oficial')

@section('contenido')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
        <div class="bg-[#1E4D8C] px-6 py-3.5">
            <h2 class="text-white font-bold text-sm flex items-center gap-2">🏫 Datos de la Institución</h2>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('director.configuracion.update') }}">
                @csrf
                @method('PUT')

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">NOMBRE DE LA INSTITUCIÓN *</label>
                        <input type="text" name="nombre_institucion" data-solo="letras" data-max-len="40" maxlength="40" required
                               value="{{ old('nombre_institucion', $configuracion->nombre_institucion ?? '') }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">DIRECCIÓN</label>
                        <input type="text" name="direccion" data-solo="alfanumerico" data-max-len="20" maxlength="20"
                               value="{{ old('direccion', $configuracion->direccion ?? '') }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">NOMBRE DEL/LA DIRECTOR/A</label>
                        <input type="text" name="nombre_director" data-solo="letras" data-max-len="20" maxlength="20"
                               value="{{ old('nombre_director', $configuracion->nombre_director ?? '') }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">TELÉFONO DE CONTACTO</label>
                        <input type="text" name="telefono_contacto" data-solo="numeros" data-max-len="20" maxlength="20"
                               value="{{ old('telefono_contacto', $configuracion->telefono_contacto ?? '') }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">EMAIL DE CONTACTO</label>
                        <input type="email" name="email_contacto" maxlength="40"
                               value="{{ old('email_contacto', $configuracion->email_contacto ?? '') }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
                    </div>
                </div>

                @if ($configuracion?->fecha_ultima_modificacion)
                    <p class="text-xs text-slate-400 mb-4">
                        Última modificación: {{ \App\Support\FechaEsp::corta($configuracion->fecha_ultima_modificacion) }}
                        @if ($configuracion->secretarioModifica)
                            por {{ $configuracion->secretarioModifica->apellido }}, {{ $configuracion->secretarioModifica->nombre }}
                        @endif
                    </p>
                @endif

                <div class="flex justify-end">
                    <button type="submit" class="rounded-xl bg-[#1E4D8C] hover:shadow-md text-white font-semibold text-sm px-6 py-2.5 transition">Guardar cambios</button>
                </div>
            </form>

            @if ($configuracion)
                <form method="POST" action="{{ route('director.configuracion.destroy') }}"
                      onsubmit="return confirm('¿Eliminar los datos institucionales cargados? Esta acción no se puede deshacer.');"
                      class="mt-4 pt-4 border-t border-slate-100 flex justify-end">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-red-200 text-red-600 hover:bg-red-50 font-semibold text-sm px-6 py-2.5 transition">Eliminar datos</button>
                </form>
            @endif
        </div>
    </div>
@endsection
