@extends('layouts.portal')

@section('titulo', 'Completá tus datos')
@section('subtitulo', 'Primer ingreso al sistema')

@section('contenido')
    @php
        $badgesDoc = [
            'pendiente' => 'sg-badge--rojo',
            'entregado' => 'sg-badge--amarillo',
            'aprobado' => 'sg-badge--verde',
            'rechazado' => 'sg-badge--rojo',
        ];
        $etiquetasDoc = [
            'pendiente' => 'Pendiente',
            'entregado' => 'En revisión',
            'aprobado' => 'Aprobado',
            'rechazado' => 'Rechazado',
        ];
    @endphp

    @if ($primerIngreso)
        <div class="sg-aviso sg-aviso--alerta mb-6 sg-revelar">
            <x-sg.icono nombre="alert" class="h-5 w-5 shrink-0" />
            <span>Es tu primer ingreso. Completá y confirmá tus datos personales para poder usar el resto del sistema.</span>
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6 items-start">
        <div class="lg:col-span-2 sg-card sg-revelar overflow-hidden">
            <div class="sg-card__head">
                <div class="sg-card__titulo">
                    <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="user" /></span>
                    <div><h3>Datos personales</h3><p>Los usa secretaría para tu legajo</p></div>
                </div>
            </div>

            <form method="POST" action="{{ route('mis-datos.update') }}" id="form-mis-datos" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="sg-label" for="nombre">Nombre</label>
                        <input type="text" id="nombre" data-solo="letras" data-max-len="50" maxlength="50" name="nombre" value="{{ old('nombre', $alumno->nombre) }}" required class="sg-input w-full">
                    </div>
                    <div>
                        <label class="sg-label" for="apellido">Apellido</label>
                        <input type="text" id="apellido" data-solo="letras" data-max-len="50" maxlength="50" name="apellido" value="{{ old('apellido', $alumno->apellido) }}" required class="sg-input w-full">
                    </div>
                    <div>
                        <label class="sg-label" for="dni">DNI</label>
                        <input type="text" id="dni" inputmode="numeric" data-solo="numeros" data-max-len="8" maxlength="8" name="dni" value="{{ old('dni', $alumno->dni) }}" required class="sg-input w-full">
                        @error('dni')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="sg-label" for="fecha_nacimiento">Fecha de nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', optional($alumno->fecha_nacimiento)->format('Y-m-d')) }}" class="sg-input w-full">
                    </div>
                    <div>
                        <label class="sg-label" for="telefono">Teléfono</label>
                        <input type="text" id="telefono" data-solo="telefono" data-max-len="15" maxlength="15" name="telefono" value="{{ old('telefono', $alumno->telefono) }}" class="sg-input w-full">
                    </div>
                    <div>
                        <label class="sg-label" for="direccion">Dirección</label>
                        <input type="text" id="direccion" data-solo="alfanumerico" data-max-len="35" maxlength="35" name="direccion" value="{{ old('direccion', $alumno->direccion) }}" placeholder="Calle, número, localidad" class="sg-input w-full">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="sg-label" for="email">Email de contacto</label>
                        <input type="email" id="email" maxlength="25" name="email" value="{{ old('email', $alumno->usuario->email) }}" required class="sg-input w-full">
                        @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex flex-wrap justify-end gap-3 mt-8 pt-5 border-t border-slate-100">
                    <button type="submit" formnovalidate class="sg-btn sg-btn--borde">
                        <x-sg.icono nombre="pencil" /> Guardar borrador
                    </button>
                    <button type="submit" class="sg-btn sg-btn--primario">
                        Confirmar y continuar <x-sg.icono nombre="arrow-right" />
                    </button>
                </div>
            </form>
        </div>

        <div class="sg-card sg-revelar overflow-hidden">
            <div class="sg-card__head">
                <div class="sg-card__titulo">
                    <span class="sg-chip sg-chip--chico sg-chip--amarillo"><x-sg.icono nombre="folder" /></span>
                    <div><h3>Documentación requerida</h3><p>PDF, JPG o PNG</p></div>
                </div>
            </div>

            <div class="p-5 space-y-3">
                @forelse ($documentos as $documento)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-800 break-words">{{ $documento->nombre }}</p>
                                <p class="text-xs text-slate-400">{{ $documento->obligatorio ? 'Obligatorio' : 'Opcional' }}</p>
                            </div>
                            <span class="sg-badge {{ $badgesDoc[$documento->estado_alumno] }} shrink-0">{{ $etiquetasDoc[$documento->estado_alumno] }}</span>
                        </div>

                        @if ($documento->miEntrega?->archivo_path)
                            <a href="{{ route('documentacion.descarga', $documento->miEntrega) }}" class="mt-2 inline-flex max-w-full items-center gap-1.5 text-xs font-semibold text-[#0B3D91] hover:underline">
                                <x-sg.icono nombre="document" class="h-4 w-4 shrink-0" />
                                <span class="truncate">{{ $documento->miEntrega->archivo_nombre_original }}</span>
                            </a>
                        @endif

                        @if (in_array($documento->estado_alumno, ['pendiente', 'rechazado']))
                            @if ($documento->estado_alumno === 'rechazado' && $documento->miEntrega?->observaciones)
                                <p class="text-xs text-red-500 mt-2">Motivo: {{ $documento->miEntrega->observaciones }}</p>
                            @endif
                            <form method="POST" action="{{ route('documentacion.store', $documento) }}" enctype="multipart/form-data" class="mt-3 space-y-2">
                                @csrf
                                <label class="flex items-center gap-2 cursor-pointer rounded-xl border-[1.5px] border-dashed border-slate-300 bg-white px-3 py-2.5 text-xs text-slate-500 hover:border-[#4FC3F7] hover:text-[#0B3D91] transition-colors">
                                    <x-sg.icono nombre="upload" class="h-4 w-4 shrink-0" />
                                    <span class="truncate" data-nombre-archivo>Elegir archivo…</span>
                                    <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" required class="sr-only"
                                           onchange="this.previousElementSibling.textContent = this.files[0]?.name || 'Elegir archivo…'">
                                </label>
                                <button type="submit" class="sg-btn sg-btn--primario sg-btn--chico w-full justify-center"><x-sg.icono nombre="upload" /> Subir</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <x-sg.vacio icono="folder">No hay documentación requerida por ahora.</x-sg.vacio>
                @endforelse
                @error('archivo')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
@endsection
