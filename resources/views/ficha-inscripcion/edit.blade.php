@extends('layouts.portal')

@section('titulo', 'Mi ficha de inscripción')
@section('subtitulo', 'Completá tus datos — los puede configurar la secretaría, así que pueden variar con el tiempo')

@section('contenido')
    @if ($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    @if ($secciones->isEmpty() || $secciones->every(fn ($s) => $s->campos->isEmpty()))
        <div class="bg-white rounded-xl shadow-sm p-8 text-center text-slate-400 text-sm">
            Todavía no hay ningún campo configurado para la ficha de inscripción.
        </div>
    @else
        <form method="POST" action="{{ route('ficha-inscripcion.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            @foreach ($secciones as $seccion)
                @continue($seccion->campos->isEmpty())
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-bold text-slate-800 mb-5">{{ $seccion->nombre }}</h3>
                    <div class="grid sm:grid-cols-2 gap-4">
                        @foreach ($seccion->campos as $campo)
                            @php
                                $nombreInput = "campo_{$campo->id_campo}";
                                $valorActual = old($nombreInput, $respuestas->get($campo->id_campo)?->valor);
                            @endphp
                            <div class="{{ $campo->tipo === 'texto_largo' ? 'sm:col-span-2' : '' }}">
                                <label class="block text-xs font-semibold text-slate-500 mb-1">
                                    {{ mb_strtoupper($campo->etiqueta) }} {{ $campo->obligatorio ? '*' : '' }}
                                </label>

                                @switch($campo->tipo)
                                    @case('texto_largo')
                                        <textarea name="{{ $nombreInput }}" rows="2" {{ $campo->obligatorio ? 'required' : '' }}
                                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">{{ $valorActual }}</textarea>
                                        @break

                                    @case('numero')
                                        <input type="number" step="any" name="{{ $nombreInput }}" value="{{ $valorActual }}" {{ $campo->obligatorio ? 'required' : '' }}
                                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                                        @break

                                    @case('fecha')
                                        <input type="date" name="{{ $nombreInput }}" value="{{ $valorActual }}" {{ $campo->obligatorio ? 'required' : '' }}
                                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                                        @break

                                    @case('seleccion')
                                        <select name="{{ $nombreInput }}" {{ $campo->obligatorio ? 'required' : '' }}
                                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                                            <option value="">Seleccioná...</option>
                                            @foreach (($campo->opciones ?? []) as $opcion)
                                                <option value="{{ $opcion }}" @selected($valorActual === $opcion)>{{ $opcion }}</option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case('si_no')
                                        <select name="{{ $nombreInput }}" {{ $campo->obligatorio ? 'required' : '' }}
                                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                                            <option value="">Seleccioná...</option>
                                            <option value="Sí" @selected($valorActual === 'Sí')>Sí</option>
                                            <option value="No" @selected($valorActual === 'No')>No</option>
                                        </select>
                                        @break

                                    @default
                                        <input type="text" maxlength="500" name="{{ $nombreInput }}" value="{{ $valorActual }}" {{ $campo->obligatorio ? 'required' : '' }}
                                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                                @endswitch
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold text-sm px-6 py-2.5 transition">Guardar ficha</button>
            </div>
        </form>
    @endif
@endsection
