@extends('layouts.admin')

@section('titulo', 'Planes de Estudio')

@section('contenido')
    <x-sg.encabezado titulo="Planes de estudio" :subtitulo="$carrera->nombre_carrera . ' — historial de planes y su vigencia'" eyebrow="Carreras y planes" icono="book">
        <a href="{{ route('admin.carreras.index') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="arrow-left" /> Carreras y planes</a>
    </x-sg.encabezado>

    @php
        $coloresEstado = ['Vigente' => 'sg-badge--verde', 'En extinción' => 'sg-badge--amarillo', 'Caducado' => 'sg-badge--rojo', 'Próximo' => 'sg-badge--azul'];
        $campoAnio = 'type="text" inputmode="numeric" data-solo="numeros" data-max-len="4" class="sg-input w-full"';
    @endphp

    {{-- Nuevo plan --}}
    <div class="sg-card sg-revelar overflow-hidden mb-8">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="plus" /></span>
                <div><h3>Nuevo plan de estudios</h3><p>Se copian las materias y correlativas del plan que elijas, y después editás solo lo que cambia</p></div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.carreras.planes.store', $carrera) }}" class="p-6 grid sm:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="sg-label">NOMBRE *</label>
                <input type="text" name="nombre" maxlength="40" required value="{{ old('nombre', 'Plan ' . (now()->year + 1)) }}" class="sg-input w-full">
            </div>
            <div>
                <label class="sg-label">RESOLUCIÓN MINISTERIAL</label>
                <input type="text" name="resolucion_ministerial" maxlength="25" value="{{ old('resolucion_ministerial') }}" class="sg-input w-full">
            </div>
            <div>
                <label class="sg-label">DURACIÓN (AÑOS) *</label>
                <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="2" name="duracion_anos" required value="{{ old('duracion_anos', $carrera->duracion_anos) }}" class="sg-input w-full">
            </div>
            <div>
                <label class="sg-label">VIGENTE DESDE (AÑO) *</label>
                <input {!! $campoAnio !!} name="anio_desde" required value="{{ old('anio_desde', now()->year + 1) }}">
            </div>
            <div>
                <label class="sg-label">INSCRIPCIONES HASTA (AÑO)</label>
                <input {!! $campoAnio !!} name="anio_cierre_inscripcion" value="{{ old('anio_cierre_inscripcion') }}" placeholder="Vacío = abiertas">
            </div>
            <div>
                <label class="sg-label">CADUCA EN (AÑO)</label>
                <input {!! $campoAnio !!} name="anio_caducidad" value="{{ old('anio_caducidad') }}" placeholder="Vacío = sin fecha">
            </div>
            <div class="sm:col-span-2">
                <label class="sg-label">COPIAR MATERIAS Y CORRELATIVAS DE</label>
                <select name="copiar_de" class="sg-input w-full">
                    @foreach ($planes as $p)
                        <option value="{{ $p->id_plan_estudio }}">{{ $p->nombre }} ({{ $p->materias_count }} materias)</option>
                    @endforeach
                    <option value="">No copiar (plan vacío)</option>
                </select>
            </div>
            <div class="flex items-end justify-end">
                <button type="submit" class="sg-btn sg-btn--azul"><x-sg.icono nombre="check-circle" /> Crear plan</button>
            </div>
            <p class="sg-aviso sg-aviso--info text-xs sm:col-span-3"><span>
                Los alumnos nuevos entran al plan que acepta inscripciones ese año. Los que ya estaban siguen en su plan hasta que caduca.
                Al crear el plan nuevo, al plan copiado se le cierran las inscripciones el año anterior (si no tenía cierre). Cargale vos en qué año caduca.
            </span></p>
        </form>
    </div>

    {{-- Pasar a todos los alumnos de un plan a otro (ej. cuando caduca) --}}
    @php
        $conAlumnos = $planes->where('inscripciones_count', '>', 0);
        $vigentes = $planes->filter(fn ($p) => $p->estado() === 'Vigente');
    @endphp
    @if ($conAlumnos->isNotEmpty() && $vigentes->count() > 0)
        <div class="sg-card sg-revelar overflow-hidden mb-8">
            <div class="sg-card__head">
                <div class="sg-card__titulo">
                    <span class="sg-chip sg-chip--chico sg-chip--amarillo"><x-sg.icono nombre="users" /></span>
                    <div><h3>Pasar alumnos de plan</h3><p>Los que están cursando una materia se saltean hasta que cierren la cursada</p></div>
                </div>
            </div>
            <div class="p-6 space-y-3">
                @foreach ($conAlumnos as $origen)
                    @php $destinos = $vigentes->where('id_plan_estudio', '!=', $origen->id_plan_estudio); @endphp
                    @if ($destinos->isNotEmpty())
                        <form method="POST" action="{{ route('admin.carreras.planes.pasar-alumnos', [$carrera, $origen]) }}" class="flex flex-wrap items-center gap-3"
                              onsubmit="return confirm('¿Pasar a los {{ $origen->inscripciones_count }} alumno(s) del {{ $origen->nombre }} al plan elegido?');">
                            @csrf
                            <span class="text-sm text-slate-700"><strong>{{ $origen->nombre }}</strong> ({{ $origen->inscripciones_count }} alumnos) → </span>
                            <select name="id_plan_destino" class="sg-input">
                                @foreach ($destinos as $d)
                                    <option value="{{ $d->id_plan_estudio }}">{{ $d->nombre }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="sg-btn sg-btn--azul sg-btn--chico"><x-sg.icono nombre="refresh" /> Pasar alumnos</button>
                        </form>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- Historial de planes --}}
    <div class="sg-card sg-revelar overflow-hidden">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--verde"><x-sg.icono nombre="book" /></span>
                <div><h3>Historial de planes</h3><p>Del más nuevo al más viejo</p></div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="sg-tabla">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Estado</th>
                        <th>Vigencia</th>
                        <th>Materias</th>
                        <th>Alumnos</th>
                        <th style="text-align:center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @foreach ($planes as $p)
                        <tr>
                            <td>
                                <p class="font-semibold text-slate-800">{{ $p->nombre }}</p>
                                <p class="text-xs text-slate-400">{{ $p->resolucion_ministerial ? 'Res. ' . $p->resolucion_ministerial : 'Sin resolución' }} · {{ $p->duracion_anos }} años</p>
                            </td>
                            <td><span class="sg-badge {{ $coloresEstado[$p->estado()] ?? 'sg-badge--gris' }} sg-badge--sin-punto">{{ $p->estado() }}</span></td>
                            <td class="text-xs text-slate-500">
                                Desde {{ $p->anio_desde }}<br>
                                Inscripciones {{ $p->anio_cierre_inscripcion ? 'hasta ' . $p->anio_cierre_inscripcion : 'abiertas' }}<br>
                                {{ $p->anio_caducidad ? 'Caduca en ' . $p->anio_caducidad : 'Sin caducidad' }}
                            </td>
                            <td><a href="{{ route('admin.carreras.plan', [$carrera, 'plan' => $p->id_plan_estudio]) }}" class="sg-link">{{ $p->materias_count }} materias</a></td>
                            <td class="text-slate-600">{{ $p->inscripciones_count }}</td>
                            <td><div class="flex justify-center gap-1">
                                <button type="button" onclick="document.getElementById('editar-plan-{{ $p->id_plan_estudio }}').classList.toggle('hidden')"
                                        class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-[#0B3D91] transition-colors" title="Editar datos y vigencia"><x-sg.icono nombre="pencil" class="h-4 w-4" /></button>
                                @if ($p->inscripciones_count === 0 && $planes->count() > 1)
                                    <form method="POST" action="{{ route('admin.carreras.planes.destroy', [$carrera, $p]) }}" onsubmit="return confirm('¿Eliminar el {{ $p->nombre }} con sus materias y correlativas?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors" title="Eliminar plan"><x-sg.icono nombre="trash" class="h-4 w-4" /></button>
                                    </form>
                                @endif
                            </div></td>
                        </tr>
                        {{-- Edición de datos y vigencia (oculta hasta tocar el lápiz) --}}
                        <tr id="editar-plan-{{ $p->id_plan_estudio }}" class="hidden bg-slate-50">
                            <td colspan="6">
                                <form method="POST" action="{{ route('admin.carreras.planes.update', [$carrera, $p]) }}" class="grid sm:grid-cols-7 gap-3 items-end">
                                    @csrf @method('PUT')
                                    <div><label class="sg-label">Nombre</label><input type="text" name="nombre" maxlength="40" required value="{{ $p->nombre }}" class="sg-input w-full"></div>
                                    <div><label class="sg-label">Resolución</label><input type="text" name="resolucion_ministerial" maxlength="25" value="{{ $p->resolucion_ministerial }}" class="sg-input w-full"></div>
                                    <div><label class="sg-label">Duración</label><input type="text" inputmode="numeric" data-solo="numeros" data-max-len="2" name="duracion_anos" required value="{{ $p->duracion_anos }}" class="sg-input w-full"></div>
                                    <div><label class="sg-label">Desde</label><input {!! $campoAnio !!} name="anio_desde" required value="{{ $p->anio_desde }}"></div>
                                    <div><label class="sg-label">Inscrip. hasta</label><input {!! $campoAnio !!} name="anio_cierre_inscripcion" value="{{ $p->anio_cierre_inscripcion }}"></div>
                                    <div><label class="sg-label">Caduca en</label><input {!! $campoAnio !!} name="anio_caducidad" value="{{ $p->anio_caducidad }}"></div>
                                    <button type="submit" class="sg-btn sg-btn--azul sg-btn--chico"><x-sg.icono nombre="check" /> Guardar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
