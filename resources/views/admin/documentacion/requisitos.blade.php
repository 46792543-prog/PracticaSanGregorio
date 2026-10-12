@extends('layouts.admin')

@section('titulo', 'Requisitos de documentación')

@section('contenido')
    <x-sg.encabezado titulo="Requisitos de documentación" subtitulo="Configurá qué documentos se les solicitan a los alumnos al inscribirse" eyebrow="Documentación" icono="upload">
        <a href="{{ route('admin.documentacion.index') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="folder" /> Documentación de alumnos</a>
    </x-sg.encabezado>

    <div class="grid gap-6 xl:grid-cols-3 items-start">
        <div class="sg-card sg-revelar overflow-hidden xl:col-span-2">
            <div class="sg-card__head">
                <div class="sg-card__titulo">
                    <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="list" /></span>
                    <h3>Requisitos configurados</h3>
                </div>
                <form method="GET" class="w-full sm:w-64">
                    <div class="sg-campo-icono">
                        <x-sg.icono nombre="search" />
                        <input type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar requisito..." onchange="this.form.submit()" maxlength="25"
                               oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]/g, '')"
                               class="sg-input w-full py-2">
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
            <table class="sg-tabla">
                <thead>
                    <tr>
                        <th>Nombre del documento</th>
                        <th>Tipo</th>
                        <th>Aplica</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @foreach ($requisitos as $requisito)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="sg-chip sg-chip--chico sg-chip--celeste"><x-sg.icono nombre="document" /></span>
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $requisito->nombre }}</p>
                                        <p class="text-xs text-slate-400">{{ $requisito->descripcion }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="sg-badge {{ $requisito->obligatorio ? 'sg-badge--amarillo' : 'sg-badge--gris' }}">
                                    {{ $requisito->obligatorio ? 'Obligatorio' : 'Opcional' }}
                                </span>
                            </td>
                            <td>{{ $requisito->carrera?->nombre ?? 'Todas las carreras' }}</td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('admin.documentacion.requisitos.destroy', $requisito) }}" onsubmit="return confirm('¿Eliminar este requisito?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="sg-btn sg-btn--peligro sg-btn--icono" title="Eliminar"><x-sg.icono nombre="trash" class="!h-4 !w-4" /></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <div class="sg-card sg-revelar border-t-4 border-t-[#FFC107] xl:sticky xl:top-24">
            <div class="sg-card__head">
                <div class="sg-card__titulo">
                    <span class="sg-chip sg-chip--amarillo"><x-sg.icono nombre="plus" /></span>
                    <div><h3>Agregar nuevo requisito</h3><p>Se pedirá a los alumnos al inscribirse</p></div>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.documentacion.requisitos.store') }}" class="sg-card__body space-y-4">
                @csrf
                <div>
                    <label class="sg-label">Nombre del documento</label>
                    <input type="text" maxlength="25" name="nombre_documento" required placeholder="Ej: Partida de nacimiento"
                           oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]/g, '')"
                           class="sg-input w-full">
                </div>
                <div>
                    <label class="sg-label">Descripción breve</label>
                    <input type="text" maxlength="25" name="descripcion" placeholder="Aclaración para el alumno"
                           oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]/g, '')"
                           class="sg-input w-full">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="sg-label">Tipo</label>
                        <select name="es_obligatorio" class="sg-input w-full">
                            <option value="1">Obligatorio</option>
                            <option value="0">Opcional</option>
                        </select>
                    </div>
                    <div>
                        <label class="sg-label">Aplica a</label>
                        <select name="id_carrera" class="sg-input w-full">
                            <option value="">Todas las carreras</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera->id_carrera }}">{{ $carrera->nombre_carrera }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="sg-btn sg-btn--azul w-full"><x-sg.icono nombre="check" /> Guardar requisito</button>
            </form>
        </div>
    </div>
@endsection
