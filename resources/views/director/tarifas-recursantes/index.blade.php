@extends('layouts.director')

@section('titulo', 'Cuotas de Recursantes')
@section('subtitulo', 'Precio de la cuota según cuántas materias cursa el alumno recursante')

@section('contenido')
    <x-sg.encabezado titulo="Cuotas de Recursantes" subtitulo="El sistema cuenta cuántas materias está cursando cada recursante y le cobra el precio de esa cantidad." eyebrow="Finanzas" icono="users" />

    <div class="sg-card sg-revelar overflow-hidden mb-8">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="plus" /></span>
                <div><h3>Cargar precio</h3><p>Por año lectivo, carrera y cantidad de materias</p></div>
            </div>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('director.tarifas-recursantes.store') }}" class="grid sm:grid-cols-5 gap-4 items-end">
                @csrf
                <div>
                    <label class="sg-label">PERÍODO LECTIVO *</label>
                    <select name="id_anio_lectivo" required class="sg-input w-full">
                        @foreach ($anios as $anio)
                            <option value="{{ $anio->id_anio_lectivo }}" @selected((int) $anio->id_anio_lectivo === (int) old('id_anio_lectivo', $anioActivoId))>{{ $anio->anio }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sg-label">CARRERA *</label>
                    <select name="id_carrera" required class="sg-input w-full">
                        @foreach ($carreras as $carrera)
                            <option value="{{ $carrera->id_carrera }}" @selected((int) $carrera->id_carrera === (int) old('id_carrera'))>{{ $carrera->nombre_carrera }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sg-label">CANTIDAD DE MATERIAS *</label>
                    <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="2" name="cantidad_materias" value="{{ old('cantidad_materias') }}" required placeholder="Ej: 2" class="sg-input w-full">
                </div>
                <div>
                    <label class="sg-label">MONTO DE LA CUOTA *</label>
                    <div class="flex items-center gap-1">
                        <span class="text-slate-400 text-sm">$</span>
                        <input type="number" step="0.01" min="0.01" max="1000000" data-max-digitos="7" data-max-valor="1000000" name="monto" value="{{ old('monto') }}" required class="sg-input w-full">
                    </div>
                </div>
                <div class="flex sm:justify-end">
                    <button type="submit" class="sg-btn sg-btn--azul"><x-sg.icono nombre="check-circle" /> Guardar precio</button>
                </div>
            </form>

            <p class="sg-aviso sg-aviso--info text-xs mt-4"><span>
                Solo se aplica a los alumnos con condición <strong>Recursante</strong> en esa carrera. El sistema cuenta las materias que están cursando y les cobra el precio de esa cantidad; si cambian de cantidad, sus cuotas impagas se actualizan solas. Los aumentos de Configuración de Cuotas no afectan estos precios. El período, el vencimiento y la mora se toman de Configuración de Cuotas.
            </span></p>
        </div>
    </div>

    <div class="sg-card sg-revelar overflow-hidden">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--verde"><x-sg.icono nombre="chart" /></span>
                <div><h3>Precios cargados</h3><p>Uno por cantidad de materias</p></div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="sg-tabla">
                <thead>
                    <tr>
                        <th>Año lectivo</th>
                        <th>Carrera</th>
                        <th>Materias</th>
                        <th>Monto</th>
                        <th style="text-align:center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @forelse ($tarifas as $tarifa)
                        <tr>
                            <td class="font-semibold text-slate-700">{{ $tarifa->anioLectivo->anio }}</td>
                            <td class="text-slate-500">{{ $tarifa->carrera->nombre_carrera }}</td>
                            <td class="text-slate-500">{{ $tarifa->cantidad_materias }} materia{{ $tarifa->cantidad_materias === 1 ? '' : 's' }}</td>
                            <td class="font-display font-bold text-[#0B3D91]">$ {{ number_format($tarifa->monto, 0, ',', '.') }}</td>
                            <td><div class="flex justify-center gap-1">
                                <button type="button" onclick="document.getElementById('editar-tarifa-{{ $tarifa->id_tarifa_recursante }}').classList.toggle('hidden')"
                                        class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-[#0B3D91] transition-colors" title="Editar precio"><x-sg.icono nombre="pencil" class="h-4 w-4" /></button>
                                <form method="POST" action="{{ route('director.tarifas-recursantes.destroy', $tarifa) }}"
                                      data-confirm="¿Eliminar el precio para {{ $tarifa->cantidad_materias }} materia(s)?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors" title="Eliminar precio"><x-sg.icono nombre="trash" class="h-4 w-4" /></button>
                                </form>
                            </div></td>
                        </tr>
                        {{-- Fila de edición (oculta hasta tocar el lápiz) --}}
                        <tr id="editar-tarifa-{{ $tarifa->id_tarifa_recursante }}" class="hidden bg-slate-50">
                            <td colspan="5">
                                <form method="POST" action="{{ route('director.tarifas-recursantes.update', $tarifa) }}" class="flex flex-wrap items-end gap-3">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="sg-label">MATERIAS</label>
                                        <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="2" name="cantidad_materias" value="{{ $tarifa->cantidad_materias }}" required class="sg-input">
                                    </div>
                                    <div>
                                        <label class="sg-label">MONTO</label>
                                        <input type="number" step="0.01" min="0.01" max="1000000" data-max-digitos="7" data-max-valor="1000000" name="monto" value="{{ (float) $tarifa->monto }}" required class="sg-input">
                                    </div>
                                    <button type="submit" class="sg-btn sg-btn--azul"><x-sg.icono nombre="check" /> Guardar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-sg.vacio icono="users">Todavía no se cargó ningún precio para recursantes.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Búsqueda de alumnos recursantes --}}
    <div class="sg-card sg-revelar overflow-hidden mt-8">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--amarillo"><x-sg.icono nombre="search" /></span>
                <div><h3>Alumnos recursantes</h3><p>Qué materias recursa cada uno, en qué carrera y cuánto paga</p></div>
            </div>
        </div>

        <form method="GET" class="flex flex-wrap items-end gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <div class="flex-1 min-w-[200px]">
                <label class="sg-label">Buscar alumno</label>
                <div class="sg-campo-icono">
                    <x-sg.icono nombre="search" />
                    <input type="text" name="q" value="{{ $filtros['q'] }}" placeholder="Nombre, apellido o DNI..." maxlength="50" class="sg-input w-full">
                </div>
            </div>
            <div>
                <label class="sg-label">Carrera</label>
                <select name="carrera" class="sg-input">
                    <option value="">Todas</option>
                    @foreach ($carreras as $carrera)
                        <option value="{{ $carrera->id_carrera }}" @selected($filtros['carrera'] === $carrera->id_carrera)>{{ $carrera->nombre_carrera }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="sg-label">Año lectivo</label>
                <select name="anio" class="sg-input">
                    @foreach ($anios as $anio)
                        <option value="{{ $anio->id_anio_lectivo }}" @selected($filtros['anio'] === $anio->id_anio_lectivo)>{{ $anio->anio }}</option>
                    @endforeach
                </select>
            </div>
            <button class="sg-btn sg-btn--azul"><x-sg.icono nombre="filter" /> Buscar</button>
            @if ($filtros['q'] || $filtros['carrera'])
                <a href="{{ route('director.tarifas-recursantes.index') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="x" /> Limpiar</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="sg-tabla">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Carrera</th>
                        <th>Materias que recursa</th>
                        <th>Cuota</th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @forelse ($recursantes as $r)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="sg-avatar">{{ mb_substr($r['alumno']->nombre, 0, 1) }}{{ mb_substr($r['alumno']->apellido, 0, 1) }}</span>
                                    <div>
                                        <a href="{{ route('admin.alumnos.show', [$r['alumno'], 'carrera' => $r['carrera']->id_carrera]) }}" class="font-semibold text-slate-800 hover:text-[#0B3D91]">{{ $r['alumno']->apellido }}, {{ $r['alumno']->nombre }}</a>
                                        <p class="text-xs text-slate-400">DNI {{ $r['alumno']->dni }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-slate-500">{{ $r['carrera']->nombre_carrera }}</td>
                            <td>
                                @forelse ($r['materias'] as $materia)
                                    <span class="sg-badge sg-badge--azul sg-badge--sin-punto mr-1 mb-1">{{ $materia->nombre }} · {{ $materia->vecesRecursada($r['alumno']) }}ª vez</span>
                                @empty
                                    <span class="text-xs text-slate-400">No está cursando materias este año</span>
                                @endforelse
                            </td>
                            <td class="whitespace-nowrap">
                                <p class="text-xs text-slate-500">{{ $r['materias']->count() }} materia{{ $r['materias']->count() === 1 ? '' : 's' }}</p>
                                @if ($r['monto'] !== null)
                                    <p class="font-display font-bold text-[#0B3D91]">$ {{ number_format($r['monto'], 0, ',', '.') }}</p>
                                @elseif ($r['materias']->isNotEmpty())
                                    <span class="sg-badge sg-badge--amarillo sg-badge--sin-punto">Sin precio para {{ $r['materias']->count() }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-sg.vacio icono="users">No hay alumnos recursantes con estos filtros.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
