@extends('layouts.director')

@section('titulo', 'Nuevo Usuario')
@section('subtitulo', 'Dar de alta a un secretario o director')

@section('contenido')
    <div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-slate-100 border-t-4 border-[#1E4D8C] p-6">
        <h2 class="font-bold text-[#1E4D8C] flex items-center gap-2 mb-1">👤 Datos del usuario</h2>
        <p class="text-xs text-slate-400 mb-6">La clave inicial se genera automáticamente y se muestra una sola vez al confirmar — hay que entregársela a la persona.</p>

        <form method="POST" action="{{ route('director.usuarios.store') }}">
            @csrf
            <div class="grid sm:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">DNI *</label>
                    <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="8" maxlength="8" name="dni" value="{{ old('dni') }}" required placeholder="Ej: 38472190"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">APELLIDO *</label>
                    <input type="text" data-solo="letras" data-max-len="50" maxlength="50" name="apellido" value="{{ old('apellido') }}" required placeholder="Ej: González"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">NOMBRE *</label>
                    <input type="text" data-solo="letras" data-max-len="50" maxlength="50" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Lucas Andrés"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-500 mb-1">EMAIL *</label>
                <input type="email" maxlength="100" name="email" value="{{ old('email') }}" required placeholder="usuario@email.com"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
            </div>
            <div class="mb-6">
                <label class="block text-xs font-semibold text-slate-500 mb-1">ROL *</label>
                <div class="flex gap-2">
                    <select name="id_rol" id="input-rol" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                        <option value="">Seleccioná un rol...</option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id_rol }}" @selected(old('id_rol') == $rol->id_rol)>{{ $rol->nombre_rol }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="abrirModalRol()" title="Agregar, modificar o quitar un rol"
                            class="shrink-0 w-11 h-11 flex items-center justify-center rounded-lg bg-blue-100 text-[#1E4D8C] hover:bg-[#1E4D8C] hover:text-white transition-colors">
                        <x-sg.icono nombre="plus" class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('director.usuarios.index') }}" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] font-semibold text-sm px-6 py-2.5">← Volver</a>
                <button type="submit" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow-md hover:brightness-105 transition text-[#1E4D8C] font-bold text-sm px-6 py-2.5">
                    Registrar usuario y generar clave
                </button>
            </div>
        </form>
    </div>

    {{-- Modal: roles --}}
    <div id="modal-rol" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[70] items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200/60 w-full max-w-sm p-6 max-h-[85vh] overflow-y-auto">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-bold text-[#1E4D8C] flex items-center gap-2"><x-sg.icono nombre="plus" class="h-5 w-5" /> Roles</h3>
                <button type="button" onclick="cerrarModalRol()" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Cerrar"><x-sg.icono nombre="x" class="h-5 w-5" /></button>
            </div>
            <p class="text-xs text-slate-400 -mt-2 mb-4">Los roles marcados como "del sistema" (Secretario, Secretario Administrativo, Director) ya tienen un panel asignado y no se pueden tocar desde aquí. Un rol nuevo queda guardado, pero hasta que no se habilite por código no va a poder iniciar sesión en ningún panel.</p>

            <p id="rol-error" class="hidden text-xs text-red-500 mb-3"></p>

            <div id="lista-roles" class="space-y-2 mb-5">
                @foreach ($roles as $rol)
                    @if (in_array($rol->nombre_rol, $rolesProtegidos, true))
                        <div class="flex gap-2 items-center justify-between border border-slate-200 rounded-xl p-2 bg-slate-50">
                            <span class="text-sm text-slate-600 px-1">{{ $rol->nombre_rol }}</span>
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 shrink-0">Rol del sistema</span>
                        </div>
                    @else
                        <div class="flex gap-2 items-center border border-slate-200 rounded-xl p-2 hover:border-blue-200 transition-colors" data-id="{{ $rol->id_rol }}">
                            <form method="POST" action="{{ route('director.usuarios.roles.update', $rol) }}" class="form-rol-update flex flex-1 gap-2 items-center min-w-0">
                                @csrf @method('PUT')
                                <input type="text" name="nombre_rol" value="{{ $rol->nombre_rol }}" required maxlength="50"
                                       class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm min-w-0 flex-1 focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                                <button type="submit" title="Guardar cambios" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] text-xs font-semibold px-2.5 py-1.5 shrink-0">Guardar</button>
                            </form>
                            <form method="POST" action="{{ route('director.usuarios.roles.destroy', $rol) }}" class="form-rol-destroy" data-confirm-rol="{{ $rol->nombre_rol }}">
                                @csrf @method('DELETE')
                                <button type="submit" title="Eliminar rol" class="rounded-lg bg-red-50 text-red-600 px-2.5 py-1.5 shrink-0"><x-sg.icono nombre="trash" class="h-4 w-4" /></button>
                            </form>
                        </div>
                    @endif
                @endforeach
            </div>

            <p class="block text-xs font-semibold text-slate-500 mb-2">Agregar nuevo rol</p>
            <form id="form-rol-nuevo" method="POST" action="{{ route('director.usuarios.roles.store') }}" class="flex gap-2">
                @csrf
                <input type="text" name="nombre_rol" required autofocus maxlength="50" placeholder="Ej: Supervisor de Prácticas"
                       class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm min-w-0 flex-1 focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                <button type="submit" class="rounded-lg bg-[#D4A017] text-[#1E4D8C] text-xs font-bold px-3 py-1.5 shrink-0">Agregar</button>
            </form>
        </div>
    </div>

    <script>
        function abrirModalRol() {
            const modal = document.getElementById('modal-rol');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function cerrarModalRol() {
            const modal = document.getElementById('modal-rol');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Las 3 acciones del modal de roles (agregar, guardar, eliminar) se mandan
        // por fetch en vez de un submit normal, para que el modal no se cierre ni
        // la página recargue al usarlas.
        (function () {
            const csrfToken = @json(csrf_token());
            const lista = document.getElementById('lista-roles');
            const errorBox = document.getElementById('rol-error');
            const selectRol = document.getElementById('input-rol');

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

            function filaRol(id, nombre) {
                const nombreSeguro = escaparHtml(nombre);
                const div = document.createElement('div');
                div.className = 'flex gap-2 items-center border border-slate-200 rounded-xl p-2 hover:border-blue-200 transition-colors';
                div.dataset.id = id;
                div.innerHTML = `
                    <form method="POST" action="/director/usuarios/roles/${id}" class="form-rol-update flex flex-1 gap-2 items-center min-w-0">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="PUT">
                        <input type="text" name="nombre_rol" value="${nombreSeguro}" required maxlength="50" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm min-w-0 flex-1 focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                        <button type="submit" title="Guardar cambios" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] text-xs font-semibold px-2.5 py-1.5 shrink-0">Guardar</button>
                    </form>
                    <form method="POST" action="/director/usuarios/roles/${id}" class="form-rol-destroy" data-confirm-rol="${nombreSeguro}">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" title="Eliminar rol" class="rounded-lg bg-red-50 text-red-600 px-2.5 py-1.5 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        </button>
                    </form>
                `;

                return div;
            }

            function agregarOptionSelect(id, nombre) {
                const option = document.createElement('option');
                option.value = id;
                option.textContent = nombre;
                selectRol.appendChild(option);
            }

            lista.addEventListener('submit', function (evento) {
                const form = evento.target;

                if (form.classList.contains('form-rol-update')) {
                    evento.preventDefault();
                    ocultarError();

                    const input = form.querySelector('[name="nombre_rol"]');

                    fetch(form.action, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: new FormData(form),
                    })
                        .then(async (res) => {
                            const datos = await res.json();
                            if (! res.ok) throw datos;

                            const option = selectRol.querySelector(`option[value="${datos.rol.id_rol}"]`);
                            if (option) option.textContent = datos.rol.nombre_rol;

                            input.classList.add('ring-2', 'ring-emerald-300');
                            setTimeout(() => input.classList.remove('ring-2', 'ring-emerald-300'), 1200);
                        })
                        .catch((datos) => mostrarError(datos.message || Object.values(datos.errors ?? {}).flat().join(' ') || 'No se pudo guardar el rol.'));

                    return;
                }

                if (form.classList.contains('form-rol-destroy')) {
                    evento.preventDefault();

                    if (! confirm(`¿Eliminar el rol '${form.dataset.confirmRol}'?`)) return;

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
                            const option = selectRol.querySelector(`option[value="${id}"]`);
                            if (option) option.remove();
                        })
                        .catch((datos) => mostrarError(datos.message || 'No se pudo eliminar el rol.'));
                }
            });

            document.getElementById('form-rol-nuevo').addEventListener('submit', function (evento) {
                evento.preventDefault();
                ocultarError();

                const form = evento.target;
                const input = form.querySelector('[name="nombre_rol"]');

                fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: new FormData(form),
                })
                    .then(async (res) => {
                        const datos = await res.json();
                        if (! res.ok) throw datos;

                        lista.appendChild(filaRol(datos.rol.id_rol, datos.rol.nombre_rol));
                        agregarOptionSelect(datos.rol.id_rol, datos.rol.nombre_rol);
                        input.value = '';
                        input.focus();
                    })
                    .catch((datos) => mostrarError(datos.message || Object.values(datos.errors ?? {}).flat().join(' ') || 'No se pudo agregar el rol.'));
            });
        })();
    </script>
@endsection
