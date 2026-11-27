@extends('layouts.admin')

@section('titulo', 'Profesores')

@section('contenido')
    <x-sg.encabezado titulo="Profesores y horarios" subtitulo="Alta y ficha de los docentes — Año lectivo {{ $anioLectivo?->anio }}" eyebrow="Plantel docente" icono="briefcase" />

    {{-- Alta / edición de profesor, en 3 pasos --}}
    <div id="tarjeta-profesor" class="sg-card sg-revelar mb-8 overflow-hidden">
        <div class="relative bg-gradient-to-r from-[#082A66] via-[#0B3D91] to-[#1565C0] px-6 py-4 overflow-hidden">
            <span class="absolute -right-8 -top-10 h-28 w-28 rounded-full border-[10px] border-[#FFC107]/25"></span>
            <h2 class="relative text-white font-display font-bold flex items-center gap-3"><span class="grid h-9 w-9 place-items-center rounded-xl bg-white/15 text-[#FFD54F]"><x-sg.icono nombre="user-plus" class="h-5 w-5" /></span> <span id="titulo-form-profesor">Agregar nuevo profesor</span></h2>
        </div>

        <form id="form-profesor" method="POST" action="{{ route('admin.profesores.store') }}" class="p-6" novalidate>
            @csrf
            <input type="hidden" name="_method" id="input-profesor-method" value="">

            {{-- Pestañas de los pasos --}}
            <div class="grid grid-cols-3 gap-3 mb-2">
                @foreach ([1 => 'Identidad', 2 => 'Contacto', 3 => 'Títulos y antigüedad'] as $n => $nombrePaso)
                    <button type="button" data-paso-tab="{{ $n }}" onclick="irAPaso({{ $n }})"
                            class="paso-tab flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left transition">
                        <span class="paso-num grid h-9 w-9 shrink-0 place-items-center rounded-full bg-blue-50 text-sm font-bold text-[#0B3D91]">{{ $n }}</span>
                        <span class="min-w-0">
                            <span class="block text-[10px] font-bold uppercase tracking-wider opacity-70">Paso {{ $n }}</span>
                            <span class="block truncate font-display font-bold">{{ $nombrePaso }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
            <div class="h-1.5 rounded-full bg-slate-100 mb-6 overflow-hidden"><div id="barra-pasos" class="h-full rounded-full bg-gradient-to-r from-[#FFC107] to-[#FFD54F] transition-all" style="width:33%"></div></div>

            {{-- PASO 1: Identidad --}}
            <div data-paso="1" class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="sg-label">Apellido <span class="text-red-500">*</span></label>
                    <input type="text" data-solo="letras" data-max-len="25" maxlength="25" name="apellido" id="input-profesor-apellido" placeholder="Ej.: Fernández" required class="sg-input w-full">
                </div>
                <div>
                    <label class="sg-label">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" data-solo="letras" data-max-len="25" maxlength="25" name="nombre" id="input-profesor-nombre" placeholder="Ej.: María Laura" required class="sg-input w-full">
                </div>
                <div>
                    <label class="sg-label">Sexo</label>
                    <select name="sexo" id="input-profesor-sexo" class="sg-input w-full">
                        <option value="">Elegir...</option>
                        @foreach (\App\Models\Profesor::SEXOS as $valor => $texto)
                            <option value="{{ $valor }}">{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sg-label">DNI <span class="text-red-500">*</span></label>
                    <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="8" maxlength="8" name="dni" id="input-profesor-dni" placeholder="8 números" required class="sg-input w-full">
                </div>
                <div>
                    <label class="sg-label">CUIL</label>
                    <div class="flex gap-2">
                        <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="11" maxlength="11" name="cuil" id="input-profesor-cuil" placeholder="11 números, sin guiones" class="sg-input min-w-0 flex-1">
                        <button type="button" onclick="calcularCuil()" title="Generar con el DNI y el sexo" class="grid h-[46px] w-[46px] shrink-0 place-items-center rounded-full bg-blue-100 text-[#0B3D91] hover:bg-[#0B3D91] hover:text-white transition-colors"><x-sg.icono nombre="sparkles" class="h-5 w-5" /></button>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Escribilo o generalo con la varita (DNI + sexo).</p>
                </div>
                <div>
                    <label class="sg-label">Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento" id="input-profesor-nacimiento" max="{{ now()->subYears(16)->toDateString() }}" class="sg-input w-full">
                </div>
                <div>
                    <label class="sg-label">Condición <span class="text-red-500">*</span></label>
                    <select name="condicion" id="input-profesor-condicion" required class="sg-input w-full">
                        <option value="">Elegir...</option>
                        @foreach (\App\Models\Profesor::CONDICIONES as $condicion)
                            <option value="{{ $condicion }}">{{ $condicion }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- PASO 2: Contacto --}}
            <div data-paso="2" class="hidden grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="sg-label">Teléfono</label>
                    <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="15" maxlength="15" name="telefono" id="input-profesor-telefono" placeholder="Ej: 3884123456" class="sg-input w-full">
                </div>
                <div>
                    <label class="sg-label">Domicilio</label>
                    <input type="text" data-solo="alfanumerico" data-max-len="40" maxlength="40" name="direccion" id="input-profesor-direccion" placeholder="Calle y número" class="sg-input w-full">
                    <p class="text-xs text-slate-400 mt-1">Hasta 40 caracteres: letras, números y espacios.</p>
                </div>
                <div>
                    <label class="sg-label">Provincia <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <select id="input-profesor-provincia" onchange="cargarLocalidades()" class="sg-input min-w-0 flex-1">
                            <option value="">Elegir provincia...</option>
                            @foreach ($provincias as $provincia)
                                <option value="{{ $provincia->id_provincia }}">{{ $provincia->nombre }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="abrirModalLugar('provincia')" title="Agregar provincia" class="grid h-[46px] w-[46px] shrink-0 place-items-center rounded-full bg-[#FFC107] text-[#0B3D91] hover:brightness-95"><x-sg.icono nombre="plus" /></button>
                    </div>
                </div>
                <div>
                    <label class="sg-label">Localidad <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <select name="id_localidad" id="input-profesor-localidad" required class="sg-input min-w-0 flex-1">
                            <option value="">Primero elegí la provincia</option>
                        </select>
                        <button type="button" onclick="abrirModalLugar('localidad')" title="Agregar localidad" class="grid h-[46px] w-[46px] shrink-0 place-items-center rounded-full bg-[#FFC107] text-[#0B3D91] hover:brightness-95"><x-sg.icono nombre="plus" /></button>
                    </div>
                </div>
            </div>

            {{-- PASO 3: Títulos, especialidades y antigüedad --}}
            <div data-paso="3" class="hidden space-y-5">
                <div>
                    <label class="sg-label">Títulos <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <select id="input-profesor-titulo" onchange="elegirTitulo(this)" class="sg-input min-w-0 flex-1">
                            <option value="">Elegir título...</option>
                            @foreach ($titulosCatalogo as $titulo)
                                <option value="{{ $titulo->id_titulo }}">{{ $titulo->nombre }} · {{ $titulo->nivel }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="abrirModalTitulo()" title="Agregar un título nuevo" class="grid h-[46px] w-[46px] shrink-0 place-items-center rounded-full bg-[#FFC107] text-[#0B3D91] hover:brightness-95"><x-sg.icono nombre="plus" /></button>
                    </div>
                    <div id="titulos-elegidos" class="flex flex-wrap gap-2 mt-2"></div>
                    <p class="text-xs text-slate-400 mt-1">Podés agregar más de un título.</p>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="sg-label !mb-0">Especialidades <span class="text-red-500">*</span></label>
                        <button type="button" onclick="abrirModalEspecialidad()" class="sg-btn sg-btn--borde sg-btn--chico"><x-sg.icono nombre="plus" /> Nueva</button>
                    </div>
                    <div id="input-profesor-especialidad" class="grid sm:grid-cols-3 gap-2">
                        @foreach ($especialidades as $especialidad)
                            <label data-especialidad="{{ $especialidad->id_especialidad }}" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm cursor-pointer hover:border-[#4FC3F7] has-[:checked]:border-[#0B3D91] has-[:checked]:bg-blue-50">
                                <input type="checkbox" name="id_especialidad[]" value="{{ $especialidad->id_especialidad }}" class="h-4 w-4 rounded">
                                <span>{{ $especialidad->nombre_especialidad }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="sg-label">Fecha de ingreso a la docencia</label>
                        <input type="date" name="fecha_ingreso" id="input-profesor-ingreso" max="{{ now()->toDateString() }}" onchange="mostrarAntiguedad()" class="sg-input w-full">
                    </div>
                    <div>
                        <label class="sg-label">Años de antigüedad</label>
                        {{-- Bloqueado: se calcula solo con la fecha de ingreso --}}
                        <input type="text" id="input-profesor-anios" readonly tabindex="-1" placeholder="Se calcula con la fecha" class="sg-input w-full bg-slate-50 text-slate-600 cursor-not-allowed">
                        <p id="texto-antiguedad" class="text-xs text-slate-400 mt-1">Se calcula solo con la fecha de ingreso.</p>
                    </div>
                </div>
            </div>

            <p id="error-paso" class="hidden text-sm text-red-600 mt-4"></p>
            <span id="editando-profesor-aviso" class="hidden sg-aviso sg-aviso--alerta text-xs mt-4"><span>Editando un profesor existente: al guardar se actualiza.</span></span>

            {{-- Navegación --}}
            <div class="flex items-center justify-between gap-3 pt-5 mt-6 border-t border-dashed border-slate-200">
                <button type="button" id="btn-paso-anterior" onclick="irAPaso(pasoActual - 1)" class="sg-btn sg-btn--borde"><x-sg.icono nombre="arrow-left" /> Anterior</button>
                <button type="button" onclick="cancelarEdicionProfesor()" class="sg-link text-sm">Limpiar</button>
                <button type="button" id="btn-paso-siguiente" onclick="siguientePaso()" class="sg-btn sg-btn--azul">Siguiente sección <x-sg.icono nombre="arrow-right" /></button>
                <button type="submit" id="input-profesor-submit" class="hidden sg-btn sg-btn--primario"><x-sg.icono nombre="check" /> Guardar docente</button>
            </div>
        </form>
    </div>

    {{-- Ventanita: provincia o localidad nueva --}}
    <div id="modal-lugar" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[70] items-center justify-center p-4">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="grid h-10 w-10 place-items-center rounded-full bg-[#FFC107] text-[#0B3D91]"><x-sg.icono nombre="map" class="h-5 w-5" /></span>
                <div>
                    <h3 id="titulo-modal-lugar" class="font-display font-bold text-slate-800">Nueva localidad</h3>
                    <p id="subtitulo-modal-lugar" class="text-xs text-slate-400"></p>
                </div>
            </div>
            <label class="sg-label">Nombre</label>
            <input type="text" id="input-lugar-nombre" data-solo="letras" data-max-len="60" maxlength="60" class="sg-input w-full mb-2"
                   onkeydown="if (event.key === 'Enter') { event.preventDefault(); guardarLugar(); }">
            <p id="error-lugar" class="hidden text-xs text-red-600 mb-2"></p>
            <div class="flex gap-3 mt-4">
                <button type="button" onclick="cerrarModalLugar()" class="sg-btn sg-btn--borde flex-1 justify-center">Cancelar</button>
                <button type="button" onclick="guardarLugar()" class="sg-btn sg-btn--azul flex-1 justify-center"><x-sg.icono nombre="check" /> Guardar</button>
            </div>
        </div>
    </div>

    {{-- Ventanita: título nuevo (nombre + nivel) --}}
    <div id="modal-titulo" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[70] items-center justify-center p-4">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl w-full max-w-md p-6">
            <h3 class="font-display font-bold text-slate-800 mb-4">Nuevo título</h3>
            <label class="sg-label">Nombre del título</label>
            <input type="text" id="input-titulo-nombre" maxlength="150" placeholder="Ej.: Licenciado/a en Enfermería" class="sg-input w-full mb-3">
            <label class="sg-label">Nivel</label>
            <select id="input-titulo-nivel" class="sg-input w-full mb-2">
                @foreach (\App\Models\Titulo::NIVELES as $nivel)
                    <option value="{{ $nivel }}">{{ $nivel }}</option>
                @endforeach
            </select>
            <p id="error-titulo" class="hidden text-xs text-red-600 mb-2"></p>
            <div class="flex gap-3 mt-4">
                <button type="button" onclick="cerrarModalTitulo()" class="sg-btn sg-btn--borde flex-1 justify-center">Cancelar</button>
                <button type="button" onclick="guardarTituloNuevo()" class="sg-btn sg-btn--azul flex-1 justify-center">Guardar título</button>
            </div>
        </div>
    </div>

    <div class="sg-seccion-titulo sg-revelar">
        <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="users" /></span>
        <h2>Profesores registrados</h2>
    </div>
    <div class="sg-card sg-revelar mb-8 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="sg-tabla">
            <thead>
                <tr>
                    <th>Profesor</th>
                    <th>Especialidad</th>
                    <th>Condición</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="sg-filas">
                @forelse ($profesoresPaginados as $profesor)
                    @php
                        $edicionProfesorPayload = [
                            'id_profesor' => $profesor->id_profesor,
                            'dni' => $profesor->dni,
                            'apellido' => $profesor->apellido,
                            'nombre' => $profesor->nombre,
                            'email' => $profesor->email,
                            'id_especialidad' => $profesor->especialidades->pluck('id_especialidad'),
                            'condicion' => $profesor->condicion,
                            'cuil' => $profesor->cuil,
                            'sexo' => $profesor->sexo,
                            'fecha_ingreso' => $profesor->fecha_ingreso?->toDateString(),
                            'titulos' => $profesor->titulos->map->only(['id_titulo', 'nombre', 'nivel'])->values(),
                            'telefono' => $profesor->persona->telefono,
                            'direccion' => $profesor->persona->direccion,
                            'fecha_nacimiento' => $profesor->persona->fecha_nacimiento?->toDateString(),
                            'id_localidad' => $profesor->persona->id_localidad,
                            'id_provincia' => $profesor->persona->localidadDomicilio?->id_provincia,
                        ];
                    @endphp
                    <tr class="{{ $profesor->activo ? '' : 'opacity-60' }}">
                        <td>
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="sg-avatar">{{ mb_substr($profesor->nombre, 0, 1) }}{{ mb_substr($profesor->apellido, 0, 1) }}</span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800">{{ $profesor->apellido }}, {{ $profesor->nombre }}</p>
                                    <p class="text-xs text-slate-400 truncate max-w-[260px]">DNI {{ $profesor->dni }}@if ($profesor->cuil) · CUIL {{ $profesor->cuil_formateado }}@endif @if ($profesor->email) · {{ $profesor->email }}@endif</p>
                                    @if ($profesor->persona->localidadDomicilio)
                                        <p class="text-xs text-slate-400">{{ $profesor->persona->localidadDomicilio->nombre }}</p>
                                    @endif
                                    @if ($profesor->antiguedad)
                                        <p class="text-xs text-slate-500">Antigüedad: {{ $profesor->antiguedad }}</p>
                                    @endif
                                    @foreach ($profesor->titulos as $titulo)
                                        <span class="sg-badge sg-badge--azul sg-badge--sin-punto mt-1 mr-1">{{ $titulo->nombre }} · {{ $titulo->nivel }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                                <x-sg.icono nombre="academic" class="h-4 w-4 shrink-0 text-[#4FC3F7]" />
                                <span class="leading-tight">{{ $profesor->especialidades_texto }}</span>
                            </span>
                        </td>
                        <td>
                            <span class="sg-badge sg-badge--sin-punto {{ ['Titular' => 'sg-badge--azul', 'Interino' => 'sg-badge--verde'][$profesor->condicion] ?? 'sg-badge--amarillo' }}">{{ $profesor->condicion }}</span>
                        </td>
                        <td>
                            <span class="sg-badge {{ $profesor->activo ? 'sg-badge--verde' : 'sg-badge--rojo' }}">
                                {{ $profesor->activo ? 'Activo' : 'Dado de baja' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" class="sg-btn sg-btn--suave sg-btn--icono" title="Editar profesor" aria-label="Editar profesor"
                                        onclick='editarProfesor(@json($edicionProfesorPayload))'><x-sg.icono nombre="pencil" class="!h-4 !w-4" /></button>
                                @if ($profesor->activo)
                                    <form method="POST" action="{{ route('admin.profesores.baja', $profesor) }}" data-confirm="¿Dar de baja a este profesor? Ya no va a poder asignarse a nuevas materias ni tribunales.">
                                        @csrf @method('PUT')
                                        <button class="sg-btn sg-btn--peligro sg-btn--icono" title="Dar de baja" aria-label="Dar de baja"><x-sg.icono nombre="user-minus" class="!h-4 !w-4" /></button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.profesores.reactivar', $profesor) }}">
                                        @csrf @method('PUT')
                                        <button class="sg-btn sg-btn--verde sg-btn--icono" title="Reactivar" aria-label="Reactivar"><x-sg.icono nombre="refresh" class="!h-4 !w-4" /></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-sg.vacio icono="briefcase">No hay profesores cargados.</x-sg.vacio></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if ($profesoresPaginados->hasPages())
            <div class="sg-card__foot">{{ $profesoresPaginados->links() }}</div>
        @endif
    </div>

    {{-- Modal: especialidades --}}
    <div id="modal-especialidad" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[70] items-center justify-center p-4">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200/60 w-full max-w-sm p-6 max-h-[85vh] overflow-y-auto">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-display font-bold text-slate-800 flex items-center gap-3"><span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="plus" /></span> Especialidades</h3>
                <button type="button" onclick="cerrarModalEspecialidad()" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Cerrar"><x-sg.icono nombre="x" /></button>
            </div>
            <p class="text-xs text-slate-400 -mt-2 mb-4">Áreas de formación del profesor (ej. Enfermería Pediátrica, Farmacología) — no es el nombre de una materia puntual, eso se elige al armar la asignación.</p>

            <p id="especialidad-error" class="hidden text-xs text-red-500 mb-3"></p>

            <div id="lista-especialidades" class="space-y-2 mb-5">
                @foreach ($especialidades as $especialidad)
                    <div class="flex gap-2 items-center border border-slate-200 rounded-xl p-2 hover:border-blue-200 transition-colors" data-id="{{ $especialidad->id_especialidad }}">
                        <form method="POST" action="{{ route('admin.profesores.especialidades.update', $especialidad) }}" class="form-especialidad-update flex flex-1 gap-2 items-center min-w-0">
                            @csrf @method('PUT')
                            <input type="text" name="nombre_especialidad" value="{{ $especialidad->nombre_especialidad }}" required maxlength="120"
                                   class="sg-input sg-input--chico min-w-0 flex-1">
                            <button type="submit" title="Guardar cambios" class="sg-btn sg-btn--suave sg-btn--chico shrink-0">Guardar</button>
                        </form>
                        <form method="POST" action="{{ route('admin.profesores.especialidades.destroy', $especialidad) }}" class="form-especialidad-destroy" data-confirm-especialidad="{{ $especialidad->nombre_especialidad }}">
                            @csrf @method('DELETE')
                            <button type="submit" title="Eliminar especialidad" class="sg-btn sg-btn--peligro sg-btn--icono shrink-0"><x-sg.icono nombre="trash" class="!h-4 !w-4" /></button>
                        </form>
                    </div>
                @endforeach
            </div>

            <p class="sg-label mb-2">Agregar nueva especialidad</p>
            <form id="form-especialidad-nueva" method="POST" action="{{ route('admin.profesores.especialidades.store') }}" class="flex gap-2">
                @csrf
                <input type="text" name="nombre_especialidad" required autofocus maxlength="120" placeholder="Ej: Enfermería Pediátrica"
                       class="sg-input sg-input--chico min-w-0 flex-1">
                <button type="submit" class="sg-btn sg-btn--azul sg-btn--chico shrink-0">Agregar</button>
            </form>
        </div>
    </div>

    @php $localidadesJs = $provincias->mapWithKeys(fn ($p) => [$p->id_provincia => $p->localidades->map->only(['id_localidad', 'nombre'])->values()]); @endphp
    <script>
        function abrirModalEspecialidad() {
            const modal = document.getElementById('modal-especialidad');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function cerrarModalEspecialidad() {
            const modal = document.getElementById('modal-especialidad');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Las 3 acciones del modal de especialidades (agregar, guardar, eliminar) se
        // mandan por fetch en vez de un submit normal, para que el modal no se
        // cierre ni la página recargue al usarlas.
        (function () {
            const csrfToken = @json(csrf_token());
            const lista = document.getElementById('lista-especialidades');
            const errorBox = document.getElementById('especialidad-error');
            const selectEspecialidad = document.getElementById('input-profesor-especialidad');

            function mostrarError(mensaje) {
                errorBox.textContent = mensaje;
                errorBox.classList.remove('hidden');
            }

            function ocultarError() {
                errorBox.classList.add('hidden');
                errorBox.textContent = '';
            }

            function escaparHtml(texto) {
                const div = document.createElement('div');
                div.textContent = texto;
                return div.innerHTML;
            }

            function filaEspecialidad(id, nombre) {
                const nombreSeguro = escaparHtml(nombre);
                const div = document.createElement('div');
                div.className = 'flex gap-2 items-center border border-slate-200 rounded-xl p-2 hover:border-blue-200 transition-colors';
                div.dataset.id = id;
                div.innerHTML = `
                    <form method="POST" action="/admin/profesores/especialidades/${id}" class="form-especialidad-update flex flex-1 gap-2 items-center min-w-0">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="PUT">
                        <input type="text" name="nombre_especialidad" value="${nombreSeguro}" required maxlength="120" class="sg-input sg-input--chico min-w-0 flex-1">
                        <button type="submit" title="Guardar cambios" class="sg-btn sg-btn--suave sg-btn--chico shrink-0">Guardar</button>
                    </form>
                    <form method="POST" action="/admin/profesores/especialidades/${id}" class="form-especialidad-destroy" data-confirm-especialidad="${nombreSeguro}">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" title="Eliminar especialidad" class="sg-btn sg-btn--peligro sg-btn--icono shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="!h-4 !w-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        </button>
                    </form>
                `;

                return div;
            }

            // Especialidad nueva: se suma como casilla en el formulario.
            function agregarOptionSelect(id, nombre) {
                const label = document.createElement('label');
                label.dataset.especialidad = id;
                label.className = 'flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm cursor-pointer hover:border-[#4FC3F7] has-[:checked]:border-[#0B3D91] has-[:checked]:bg-blue-50';
                label.innerHTML = `<input type="checkbox" name="id_especialidad[]" value="${id}" class="h-4 w-4 rounded" checked><span></span>`;
                label.querySelector('span').textContent = nombre;
                selectEspecialidad.appendChild(label);
            }

            lista.addEventListener('submit', function (evento) {
                const form = evento.target;

                if (form.classList.contains('form-especialidad-update')) {
                    evento.preventDefault();
                    ocultarError();

                    const input = form.querySelector('[name="nombre_especialidad"]');

                    fetch(form.action, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: new FormData(form),
                    })
                        .then(async (res) => {
                            const datos = await res.json();
                            if (! res.ok) throw datos;

                            const casilla = selectEspecialidad.querySelector(`[data-especialidad="${datos.especialidad.id_especialidad}"] span`);
                            if (casilla) casilla.textContent = datos.especialidad.nombre_especialidad;

                            input.classList.add('ring-2', 'ring-emerald-300');
                            mostrarToast('Especialidad actualizada correctamente');
                            setTimeout(() => input.classList.remove('ring-2', 'ring-emerald-300'), 1200);
                        })
                        .catch((datos) => mostrarError(datos.message || Object.values(datos.errors ?? {}).flat().join(' ') || 'No se pudo guardar la especialidad.'));

                    return;
                }

                if (form.classList.contains('form-especialidad-destroy')) {
                    evento.preventDefault();

                    if (! confirm(`¿Eliminar la especialidad '${form.dataset.confirmEspecialidad}'?`)) return;

                    ocultarError();
                    const fila = form.closest('[data-id]');
                    const id = fila.dataset.id;

                    fetch(form.action, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: new FormData(form),
                    })
                        .then(async (res) => {
                            const datos = await res.json();
                            if (! res.ok) throw datos;

                            fila.remove();
                            selectEspecialidad.querySelector(`[data-especialidad="${id}"]`)?.remove();
                            mostrarToast('Especialidad eliminada');
                        })
                        .catch((datos) => mostrarError(datos.message || 'No se pudo eliminar la especialidad.'));
                }
            });

            document.getElementById('form-especialidad-nueva').addEventListener('submit', function (evento) {
                evento.preventDefault();
                ocultarError();

                const form = evento.target;
                const input = form.querySelector('[name="nombre_especialidad"]');

                fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: new FormData(form),
                })
                    .then(async (res) => {
                        const datos = await res.json();
                        if (! res.ok) throw datos;

                        lista.appendChild(filaEspecialidad(datos.especialidad.id_especialidad, datos.especialidad.nombre_especialidad));
                        agregarOptionSelect(datos.especialidad.id_especialidad, datos.especialidad.nombre_especialidad);
                        mostrarToast('Especialidad agregada correctamente');
                        input.value = '';
                        input.focus();
                    })
                    .catch((datos) => mostrarError(datos.message || Object.values(datos.errors ?? {}).flat().join(' ') || 'No se pudo agregar la especialidad.'));
            });
        })();

        const rutaBaseProfesores = @json(url('/admin/profesores'));
        const rutaGuardarProfesor = @json(route('admin.profesores.store'));
        const tokenCsrf = @json(csrf_token());
        const localidadesPorProvincia = @json($localidadesJs);

        // ---------- Pasos ----------
        let pasoActual = 1;
        function irAPaso(n) {
            if (n < 1 || n > 3) return;
            pasoActual = n;
            document.querySelectorAll('[data-paso]').forEach(el => el.classList.toggle('hidden', Number(el.dataset.paso) !== n));
            document.querySelectorAll('[data-paso-tab]').forEach(tab => {
                const t = Number(tab.dataset.pasoTab), activo = t === n, hecho = t < n;
                tab.classList.toggle('bg-gradient-to-r', activo); tab.classList.toggle('from-[#0B3D91]', activo); tab.classList.toggle('to-[#1565C0]', activo);
                tab.classList.toggle('text-white', activo); tab.classList.toggle('shadow-lg', activo); tab.classList.toggle('border-transparent', activo);
                const num = tab.querySelector('.paso-num');
                num.textContent = hecho ? '✓' : t;
                num.className = 'paso-num grid h-9 w-9 shrink-0 place-items-center rounded-full text-sm font-bold ' + (activo ? 'bg-white/20 text-white ring-2 ring-[#FFD54F]' : (hecho ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-[#0B3D91]'));
            });
            document.getElementById('barra-pasos').style.width = (n / 3 * 100) + '%';
            document.getElementById('btn-paso-anterior').classList.toggle('invisible', n === 1);
            document.getElementById('btn-paso-siguiente').classList.toggle('hidden', n === 3);
            document.getElementById('input-profesor-submit').classList.toggle('hidden', n !== 3);
            document.getElementById('error-paso').classList.add('hidden');
        }

        // Antes de avanzar, revisa los obligatorios del paso.
        function pasoValido(n) {
            const errores = [];
            document.querySelectorAll(`[data-paso="${n}"] [required]`).forEach(el => { if (!el.value.trim()) errores.push(el.closest('div').querySelector('.sg-label')?.textContent.replace('*', '').trim()); });
            if (n === 3) {
                if (!document.querySelector('#titulos-elegidos input')) errores.push('Títulos');
                if (!document.querySelector('#input-profesor-especialidad input:checked')) errores.push('Especialidades');
            }
            const caja = document.getElementById('error-paso');
            caja.textContent = errores.length ? 'Completá: ' + errores.join(', ') + '.' : '';
            caja.classList.toggle('hidden', !errores.length);
            return !errores.length;
        }
        function siguientePaso() { if (pasoValido(pasoActual)) irAPaso(pasoActual + 1); }
        document.getElementById('form-profesor').addEventListener('submit', function (e) {
            for (const n of [1, 2, 3]) { if (!pasoValido(n)) { e.preventDefault(); irAPaso(n); pasoValido(n); return; } }
        });

        // ---------- Provincia y localidad ----------
        function cargarLocalidades(seleccionada = '') {
            const prov = document.getElementById('input-profesor-provincia').value;
            const select = document.getElementById('input-profesor-localidad');
            const lista = localidadesPorProvincia[prov] || [];
            select.innerHTML = prov ? '<option value="">Elegir localidad...</option>' : '<option value="">Primero elegí la provincia</option>';
            lista.forEach(l => select.add(new Option(l.nombre, l.id_localidad)));
            select.value = seleccionada;
        }
        async function postJson(url, datos) {
            const res = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': tokenCsrf }, body: JSON.stringify(datos) });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || Object.values(json.errors ?? {}).flat().join(' ') || 'No se pudo guardar.');
            return json;
        }
        // Ventanita para agregar una provincia o una localidad.
        let tipoLugar = 'localidad';
        function abrirModalLugar(tipo) {
            const select = document.getElementById('input-profesor-provincia');
            if (tipo === 'localidad' && !select.value) { mostrarToast('Primero elegí la provincia', 'error'); select.focus(); return; }
            tipoLugar = tipo;
            document.getElementById('titulo-modal-lugar').textContent = tipo === 'provincia' ? 'Nueva provincia' : 'Nueva localidad';
            document.getElementById('subtitulo-modal-lugar').textContent = tipo === 'provincia' ? 'Se suma a la lista de provincias' : 'En ' + select.selectedOptions[0].textContent;
            document.getElementById('input-lugar-nombre').value = '';
            document.getElementById('error-lugar').classList.add('hidden');
            const m = document.getElementById('modal-lugar'); m.classList.remove('hidden'); m.classList.add('flex');
            document.getElementById('input-lugar-nombre').focus();
        }
        function cerrarModalLugar() { const m = document.getElementById('modal-lugar'); m.classList.add('hidden'); m.classList.remove('flex'); }
        async function guardarLugar() {
            const nombre = document.getElementById('input-lugar-nombre').value.trim();
            const error = document.getElementById('error-lugar');
            if (!nombre) { error.textContent = 'Escribí el nombre.'; error.classList.remove('hidden'); return; }
            try {
                if (tipoLugar === 'provincia') {
                    const { provincia } = await postJson(@json(route('admin.profesores.provincias.store')), { nombre });
                    localidadesPorProvincia[provincia.id_provincia] = [];
                    const select = document.getElementById('input-profesor-provincia');
                    select.add(new Option(provincia.nombre, provincia.id_provincia)); select.value = provincia.id_provincia; cargarLocalidades();
                } else {
                    const idProvincia = document.getElementById('input-profesor-provincia').value;
                    const { localidad } = await postJson(@json(route('admin.profesores.localidades.store')), { id_provincia: idProvincia, nombre });
                    localidadesPorProvincia[idProvincia].push({ id_localidad: localidad.id_localidad, nombre: localidad.nombre });
                    cargarLocalidades(localidad.id_localidad);
                }
                cerrarModalLugar();
                mostrarToast('Guardado');
            } catch (e) { error.textContent = e.message; error.classList.remove('hidden'); }
        }

        // Cartelito flotante abajo a la derecha ("Guardado").
        function mostrarToast(texto, tipo = 'ok') {
            const t = document.createElement('div');
            t.className = 'fixed bottom-6 right-6 z-[100] flex items-center gap-2 rounded-2xl px-5 py-3 text-sm font-semibold text-white shadow-xl transition-all duration-300 opacity-0 translate-y-2 '
                + (tipo === 'error' ? 'bg-red-600' : 'bg-emerald-600');
            t.textContent = (tipo === 'error' ? '⚠ ' : '✓ ') + texto;
            document.body.appendChild(t);
            requestAnimationFrame(() => t.classList.remove('opacity-0', 'translate-y-2'));
            setTimeout(() => { t.classList.add('opacity-0', 'translate-y-2'); setTimeout(() => t.remove(), 300); }, 2500);
        }

        // ---------- Títulos (se eligen de la lista y quedan como etiquetas) ----------
        function agregarChipTitulo(id, texto) {
            if (document.querySelector(`#titulos-elegidos input[value="${id}"]`)) return;
            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center gap-2 rounded-full bg-blue-50 text-[#0B3D91] text-sm font-semibold pl-3 pr-1 py-1';
            chip.innerHTML = `<input type="hidden" name="titulos[]" value="${id}"><span></span><button type="button" title="Quitar" class="h-6 w-6 rounded-full hover:bg-red-100 hover:text-red-600">✕</button>`;
            chip.querySelector('span').textContent = texto;
            chip.querySelector('button').onclick = () => chip.remove();
            document.getElementById('titulos-elegidos').appendChild(chip);
        }
        function elegirTitulo(select) {
            if (select.value) agregarChipTitulo(select.value, select.selectedOptions[0].textContent);
            select.value = '';
        }
        function abrirModalTitulo() { const m = document.getElementById('modal-titulo'); m.classList.remove('hidden'); m.classList.add('flex'); document.getElementById('input-titulo-nombre').focus(); }
        function cerrarModalTitulo() { const m = document.getElementById('modal-titulo'); m.classList.add('hidden'); m.classList.remove('flex'); document.getElementById('error-titulo').classList.add('hidden'); }
        async function guardarTituloNuevo() {
            const nombre = document.getElementById('input-titulo-nombre').value.trim(), nivel = document.getElementById('input-titulo-nivel').value;
            const error = document.getElementById('error-titulo');
            if (!nombre) { error.textContent = 'Escribí el nombre del título.'; error.classList.remove('hidden'); return; }
            try {
                const { titulo } = await postJson(@json(route('admin.profesores.titulos.store')), { nombre, nivel });
                const texto = `${titulo.nombre} · ${titulo.nivel}`;
                document.getElementById('input-profesor-titulo').add(new Option(texto, titulo.id_titulo));
                agregarChipTitulo(titulo.id_titulo, texto);
                document.getElementById('input-titulo-nombre').value = '';
                cerrarModalTitulo();
                mostrarToast('Guardado');
            } catch (e) { error.textContent = e.message; error.classList.remove('hidden'); }
        }

        // ---------- Antigüedad ----------
        function mostrarAntiguedad() {
            const valor = document.getElementById('input-profesor-ingreso').value;
            const texto = document.getElementById('texto-antiguedad');
            if (!valor) { document.getElementById('input-profesor-anios').value = ''; return; }
            const desde = new Date(valor + 'T00:00:00'), hoy = new Date();
            let meses = (hoy.getFullYear() - desde.getFullYear()) * 12 + (hoy.getMonth() - desde.getMonth());
            if (hoy.getDate() < desde.getDate()) meses--;
            if (meses < 0) return;
            const anios = Math.floor(meses / 12), resto = meses % 12;
            document.getElementById('input-profesor-anios').value = anios + (anios === 1 ? ' año' : ' años');
            const partes = [anios ? anios + (anios === 1 ? ' año' : ' años') : '', resto ? resto + (resto === 1 ? ' mes' : ' meses') : ''].filter(Boolean);
            texto.textContent = 'Antigüedad: ' + (partes.length ? partes.join(' y ') : 'menos de un mes');
            texto.className = 'text-xs text-[#0B3D91] font-semibold mt-1';
        }
        // CUIL a partir del DNI y el sexo (mismo cálculo que AFIP: 20 varón, 27 mujer, 23 si da 10).
        function calcularCuil() {
            const dni = document.getElementById('input-profesor-dni').value.padStart(8, '0');
            const sexo = document.getElementById('input-profesor-sexo').value;
            if (!/^\d{8}$/.test(dni) || !sexo) { alert('Cargá primero el DNI y el sexo.'); return; }
            const digito = (diez) => {
                const suma = [...diez].reduce((t, n, i) => t + n * [5, 4, 3, 2, 7, 6, 5, 4, 3, 2][i], 0);
                const d = 11 - (suma % 11);
                return d === 11 ? 0 : (d === 10 ? null : d);
            };
            const prefijo = sexo === 'F' ? '27' : '20';
            const d = digito(prefijo + dni);
            document.getElementById('input-profesor-cuil').value = d !== null ? prefijo + dni + d : '23' + dni + (sexo === 'F' ? '4' : '9');
        }

        // ---------- Editar / limpiar ----------
        function ponerFecha(id, valor) { const el = document.getElementById(id); el._flatpickr ? el._flatpickr.setDate(valor || null) : el.value = valor ?? ''; }
        function editarProfesor(profesor) {
            cancelarEdicionProfesor();
            const form = document.getElementById('form-profesor');
            form.action = rutaBaseProfesores + '/' + profesor.id_profesor;
            document.getElementById('input-profesor-method').value = 'PUT';
            for (const [campo, id] of [['dni', 'dni'], ['apellido', 'apellido'], ['nombre', 'nombre'], ['condicion', 'condicion'], ['cuil', 'cuil'], ['sexo', 'sexo'], ['telefono', 'telefono'], ['direccion', 'direccion']]) {
                document.getElementById('input-profesor-' + id).value = profesor[campo] ?? '';
            }
            ponerFecha('input-profesor-nacimiento', profesor.fecha_nacimiento);
            ponerFecha('input-profesor-ingreso', profesor.fecha_ingreso);
            document.getElementById('input-profesor-provincia').value = profesor.id_provincia ?? '';
            cargarLocalidades(profesor.id_localidad ?? '');
            const ids = (profesor.id_especialidad || []).map(String);
            document.querySelectorAll('#input-profesor-especialidad input').forEach(c => c.checked = ids.includes(c.value));
            (profesor.titulos || []).forEach(t => agregarChipTitulo(t.id_titulo, `${t.nombre} · ${t.nivel}`));
            mostrarAntiguedad();
            document.getElementById('titulo-form-profesor').textContent = 'Editar profesor';
            document.getElementById('input-profesor-submit').innerHTML = 'Guardar cambios';
            document.getElementById('editando-profesor-aviso').classList.remove('hidden');
            irAPaso(1);
            document.getElementById('tarjeta-profesor').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelarEdicionProfesor() {
            const form = document.getElementById('form-profesor');
            form.reset();
            form.action = rutaGuardarProfesor;
            document.getElementById('input-profesor-method').value = '';
            ['input-profesor-nacimiento', 'input-profesor-ingreso'].forEach(id => ponerFecha(id, null));
            document.getElementById('titulos-elegidos').innerHTML = '';
            cargarLocalidades();
            document.getElementById('texto-antiguedad').textContent = 'Se calcula solo con la fecha de ingreso.';
            document.getElementById('input-profesor-anios').value = '';
            document.getElementById('texto-antiguedad').className = 'text-xs text-slate-400 mt-1';
            document.getElementById('titulo-form-profesor').textContent = 'Agregar nuevo profesor';
            document.getElementById('input-profesor-submit').innerHTML = 'Guardar docente';
            document.getElementById('editando-profesor-aviso').classList.add('hidden');
            irAPaso(1);
        }

        // Al volver con errores del servidor: conserva lo cargado y abre el paso del primer error.
        document.addEventListener('DOMContentLoaded', function () {
            irAPaso(1);
            @if ($errors->any())
                const viejo = @json(old());
                if (viejo.id_localidad) {
                    const prov = Object.keys(localidadesPorProvincia).find(p => localidadesPorProvincia[p].some(l => String(l.id_localidad) === String(viejo.id_localidad)));
                    document.getElementById('input-profesor-provincia').value = prov ?? '';
                    cargarLocalidades(viejo.id_localidad);
                }
                for (const campo of ['dni', 'apellido', 'nombre', 'condicion', 'cuil', 'sexo', 'telefono', 'direccion']) {
                    if (viejo[campo] !== undefined) document.getElementById('input-profesor-' + campo).value = viejo[campo] ?? '';
                }
                (viejo.id_especialidad || []).forEach(id => { const c = document.querySelector(`#input-profesor-especialidad input[value="${id}"]`); if (c) c.checked = true; });
                (viejo.titulos || []).forEach(id => { const o = document.querySelector(`#input-profesor-titulo option[value="${id}"]`); if (o) agregarChipTitulo(id, o.textContent); });
                const errores = @json($errors->keys());
                const pasoDe = c => (['telefono', 'direccion', 'id_localidad'].includes(c) ? 2 : (['titulos', 'id_especialidad', 'fecha_ingreso'].some(x => c.startsWith(x)) ? 3 : 1));
                if (errores.length) irAPaso(Math.min(...errores.map(pasoDe)));
            @endif
        });
    </script>

    {{-- Las materias de cada profesor ahora se manejan en Designaciones --}}
    <a href="{{ route('admin.designaciones.index') }}" class="sg-card sg-revelar flex items-center gap-4 p-5 hover:shadow-lg transition-shadow">
        <span class="grid h-11 w-11 place-items-center rounded-xl bg-blue-50 text-[#0B3D91]"><x-sg.icono nombre="document-check" class="h-5 w-5" /></span>
        <span class="flex-1">
            <span class="block font-display font-bold text-slate-800">Designaciones</span>
            <span class="block text-sm text-slate-500">Qué materia dicta cada profesor, con sus horas cátedra, tipo y fechas.</span>
        </span>
        <x-sg.icono nombre="arrow-right" class="h-5 w-5 text-[#0B3D91]" />
    </a>

@endsection
