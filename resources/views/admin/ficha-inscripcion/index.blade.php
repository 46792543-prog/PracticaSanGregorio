@extends('layouts.admin')

@section('titulo', 'Ficha de Inscripción')

@section('contenido')
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Configurar formulario de inscripción</h1>
            <p class="text-sm text-slate-400">Este es el formulario que completa el alumno desde su portal — agregá, editá, ocultá o reordená secciones y campos.</p>
        </div>
        <button type="button" onclick="abrirModalSeccion()" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow-md hover:brightness-105 transition text-white font-semibold text-sm px-5 py-2.5">
            + Nueva sección
        </button>
    </div>

    <div class="space-y-6">
        @forelse ($secciones as $seccion)
            @php
                $payloadSeccion = ['id' => $seccion->id_seccion, 'nombre' => $seccion->nombre];
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h2 class="font-bold text-slate-700">{{ $seccion->nombre }}</h2>
                    <div class="flex items-center gap-1.5 text-xs font-semibold">
                        <form method="POST" action="{{ route('admin.ficha-inscripcion.secciones.mover', $seccion) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="direccion" value="arriba">
                            <button class="h-7 w-7 rounded-lg hover:bg-slate-200 text-slate-500" title="Mover arriba">↑</button>
                        </form>
                        <form method="POST" action="{{ route('admin.ficha-inscripcion.secciones.mover', $seccion) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="direccion" value="abajo">
                            <button class="h-7 w-7 rounded-lg hover:bg-slate-200 text-slate-500" title="Mover abajo">↓</button>
                        </form>
                        <button type="button" onclick="editarSeccion({{ \Illuminate\Support\Js::from($payloadSeccion) }})" class="text-[#1E4D8C] hover:underline px-2">Editar</button>
                        <form method="POST" action="{{ route('admin.ficha-inscripcion.secciones.destroy', $seccion) }}" onsubmit="return confirm('¿Eliminar esta sección? Solo se puede si no tiene campos.');">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:underline px-2">Eliminar</button>
                        </form>
                        <button type="button" onclick="abrirModalCampo({{ $seccion->id_seccion }})" class="rounded-lg bg-[#1E4D8C] text-white px-3 py-1.5 ml-2">+ Campo</button>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($seccion->campos as $campo)
                        @php
                            $payloadCampo = [
                                'id' => $campo->id_campo,
                                'id_seccion' => $campo->id_seccion,
                                'etiqueta' => $campo->etiqueta,
                                'tipo' => $campo->tipo,
                                'opciones' => $campo->opciones ? implode("\n", $campo->opciones) : '',
                                'obligatorio' => $campo->obligatorio,
                            ];
                        @endphp
                        <div class="flex items-center justify-between gap-3 px-6 py-3 text-sm {{ $campo->activo ? '' : 'opacity-50' }}">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-700 truncate">{{ $campo->etiqueta }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ ucfirst(str_replace('_', ' ', $campo->tipo)) }}
                                    @if ($campo->obligatorio) · <span class="text-amber-600 font-semibold">Obligatorio</span> @endif
                                    @unless ($campo->activo) · <span class="text-red-500 font-semibold">Oculto</span> @endunless
                                </p>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs font-semibold shrink-0">
                                <form method="POST" action="{{ route('admin.ficha-inscripcion.campos.mover', $campo) }}">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="direccion" value="arriba">
                                    <button class="h-7 w-7 rounded-lg hover:bg-slate-100 text-slate-500" title="Mover arriba">↑</button>
                                </form>
                                <form method="POST" action="{{ route('admin.ficha-inscripcion.campos.mover', $campo) }}">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="direccion" value="abajo">
                                    <button class="h-7 w-7 rounded-lg hover:bg-slate-100 text-slate-500" title="Mover abajo">↓</button>
                                </form>
                                <button type="button" onclick="editarCampo({{ \Illuminate\Support\Js::from($payloadCampo) }})" class="text-[#1E4D8C] hover:underline px-1.5">Editar</button>
                                <form method="POST" action="{{ route('admin.ficha-inscripcion.campos.toggle', $campo) }}">
                                    @csrf @method('PUT')
                                    <button class="text-slate-500 hover:underline px-1.5">{{ $campo->activo ? 'Ocultar' : 'Mostrar' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.ficha-inscripcion.campos.destroy', $campo) }}" onsubmit="return confirm('¿Eliminar este campo definitivamente? Se van a perder las respuestas que los alumnos ya cargaron.');">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:underline px-1.5">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="px-6 py-6 text-sm text-slate-400">Esta sección todavía no tiene campos.</p>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-10 text-center text-slate-400 text-sm">
                Todavía no hay secciones cargadas. Usá "+ Nueva sección" para empezar a armar el formulario.
            </div>
        @endforelse
    </div>

    {{-- Modal: nueva / editar sección --}}
    <div id="modal-seccion" class="hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-bold text-slate-800" id="modal-seccion-titulo">➕ Nueva sección</h3>
                <button type="button" onclick="cerrarModalSeccion()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.ficha-inscripcion.secciones.store') }}" id="form-seccion">
                @csrf
                <input type="hidden" name="_method" id="input-seccion-method" value="">
                <label class="block text-xs font-semibold text-slate-500 mb-1">NOMBRE DE LA SECCIÓN</label>
                <input type="text" name="nombre" id="input-seccion-nombre" required autofocus maxlength="100" placeholder="Ej: Datos personales"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm mb-4">
                <div class="flex gap-3">
                    <button type="button" onclick="cerrarModalSeccion()" class="flex-1 rounded-lg border border-slate-300 text-slate-600 text-sm font-semibold py-2">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-lg bg-[#1E4D8C] hover:bg-[#173d70] transition text-white text-sm font-semibold py-2">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: nuevo / editar campo --}}
    <div id="modal-campo" class="hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-bold text-slate-800" id="modal-campo-titulo">➕ Nuevo campo</h3>
                <button type="button" onclick="cerrarModalCampo()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.ficha-inscripcion.campos.store') }}" id="form-campo">
                @csrf
                <input type="hidden" name="_method" id="input-campo-method" value="">
                <input type="hidden" name="id_seccion" id="input-campo-seccion">

                <label class="block text-xs font-semibold text-slate-500 mb-1">ETIQUETA (lo que ve el alumno)</label>
                <input type="text" name="etiqueta" id="input-campo-etiqueta" required maxlength="150" placeholder="Ej: Grupo sanguíneo"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm mb-4">

                <label class="block text-xs font-semibold text-slate-500 mb-1">TIPO DE CAMPO</label>
                <select name="tipo" id="input-campo-tipo" onchange="actualizarVisibilidadOpciones()" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm mb-4">
                    @foreach ($tipos as $tipo)
                        <option value="{{ $tipo }}">{{ ucfirst(str_replace('_', ' ', $tipo)) }}</option>
                    @endforeach
                </select>

                <div id="contenedor-opciones" class="hidden mb-4">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">OPCIONES (una por línea)</label>
                    <textarea name="opciones" id="input-campo-opciones" rows="4" placeholder="Opción 1&#10;Opción 2&#10;Opción 3"
                              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600 mb-6">
                    <input type="hidden" name="obligatorio" value="0">
                    <input type="checkbox" name="obligatorio" id="input-campo-obligatorio" value="1" class="h-4 w-4 rounded">
                    Campo obligatorio
                </label>

                <div class="flex gap-3">
                    <button type="button" onclick="cerrarModalCampo()" class="flex-1 rounded-lg border border-slate-300 text-slate-600 text-sm font-semibold py-2">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-lg bg-[#1E4D8C] hover:bg-[#173d70] transition text-white text-sm font-semibold py-2">Guardar campo</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const rutaSeccionesStore = @json(route('admin.ficha-inscripcion.secciones.store'));
        const rutaSeccionesBase = @json(url('/admin/ficha-inscripcion/secciones'));
        const rutaCamposStore = @json(route('admin.ficha-inscripcion.campos.store'));
        const rutaCamposBase = @json(url('/admin/ficha-inscripcion/campos'));

        function abrirModalSeccion() {
            document.getElementById('form-seccion').reset();
            document.getElementById('form-seccion').action = rutaSeccionesStore;
            document.getElementById('input-seccion-method').value = '';
            document.getElementById('modal-seccion-titulo').textContent = '➕ Nueva sección';
            const modal = document.getElementById('modal-seccion');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function cerrarModalSeccion() {
            const modal = document.getElementById('modal-seccion');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        function editarSeccion(seccion) {
            document.getElementById('form-seccion').action = rutaSeccionesBase + '/' + seccion.id;
            document.getElementById('input-seccion-method').value = 'PUT';
            document.getElementById('input-seccion-nombre').value = seccion.nombre;
            document.getElementById('modal-seccion-titulo').textContent = '✏️ Editar sección';
            const modal = document.getElementById('modal-seccion');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function actualizarVisibilidadOpciones() {
            const esSeleccion = document.getElementById('input-campo-tipo').value === 'seleccion';
            document.getElementById('contenedor-opciones').classList.toggle('hidden', ! esSeleccion);
        }

        function abrirModalCampo(idSeccion) {
            document.getElementById('form-campo').reset();
            document.getElementById('form-campo').action = rutaCamposStore;
            document.getElementById('input-campo-method').value = '';
            document.getElementById('input-campo-seccion').value = idSeccion;
            document.getElementById('modal-campo-titulo').textContent = '➕ Nuevo campo';
            actualizarVisibilidadOpciones();
            const modal = document.getElementById('modal-campo');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function cerrarModalCampo() {
            const modal = document.getElementById('modal-campo');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        function editarCampo(campo) {
            document.getElementById('form-campo').action = rutaCamposBase + '/' + campo.id;
            document.getElementById('input-campo-method').value = 'PUT';
            document.getElementById('input-campo-seccion').value = campo.id_seccion;
            document.getElementById('input-campo-etiqueta').value = campo.etiqueta;
            document.getElementById('input-campo-tipo').value = campo.tipo;
            document.getElementById('input-campo-opciones').value = campo.opciones;
            document.getElementById('input-campo-obligatorio').checked = campo.obligatorio;
            document.getElementById('modal-campo-titulo').textContent = '✏️ Editar campo';
            actualizarVisibilidadOpciones();
            const modal = document.getElementById('modal-campo');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    </script>
@endsection
