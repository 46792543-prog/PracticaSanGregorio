@extends('layouts.director')

@section('titulo', 'Configuración Institucional')
@section('subtitulo', 'Datos generales de la institución, usados en actas y documentación oficial')

@section('contenido')
    <x-sg.encabezado titulo="Configuración Institucional" subtitulo="Datos generales de la institución, usados en actas y documentación oficial" eyebrow="Ajustes" icono="settings" />

    <div class="sg-card sg-revelar overflow-hidden mb-8">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--azul"><x-sg.icono nombre="building" /></span>
                <div><h3>Datos de la institución</h3><p>Se usan en actas, certificados y en la página pública</p></div>
            </div>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('director.configuracion.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-6 flex items-center gap-5 rounded-2xl bg-gradient-to-r from-blue-50 to-white border border-blue-100 p-4">
                    <div id="logo-preview-wrap" class="h-20 w-20 shrink-0 rounded-2xl bg-gradient-to-br from-[#4FC3F7] to-[#0B3D91] shadow-lg ring-4 ring-white outline outline-2 outline-[#FFC107] overflow-hidden grid place-items-center">
                        <img id="logo-preview" src="{{ $configuracion?->logo_url }}" alt="Logo institucional" class="{{ $configuracion?->logo_url ? '' : 'hidden' }} h-full w-full object-cover">
                        <svg id="logo-preview-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.7" class="{{ $configuracion?->logo_url ? 'hidden' : '' }} h-8 w-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25 4.5 5.4v5.4c0 5.13 3.24 9.6 7.5 10.95 4.26-1.35 7.5-5.82 7.5-10.95V5.4L12 2.25Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2.25 2.25L15.5 9.5" />
                        </svg>
                    </div>
                    <div>
                        <label class="sg-label">LOGO DE LA INSTITUCIÓN</label>
                        <input type="file" id="logo-input" name="logo" accept="image/png,image/jpeg,image/webp"
                               class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-xl file:border-0 file:bg-[#0B3D91] file:text-white file:px-4 file:py-2 file:text-xs file:font-bold file:cursor-pointer hover:file:bg-[#082A66] file:transition-colors">
                        <p class="text-[11px] text-slate-400 mt-1">JPG, PNG o WEBP. Vas a poder recortarla antes de guardar.</p>
                        @error('logo')
                            <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div id="crop-modal" class="hidden fixed inset-0 z-[70] items-center justify-center bg-[#04153A]/60 backdrop-blur-sm px-4">
                    <div class="sg-modal bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200/60 w-full max-w-lg overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="font-display font-bold text-slate-800 flex items-center gap-3"><span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="photo" /></span> Recortar logo</h3>
                            <button type="button" id="crop-cancel" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Cerrar"><x-sg.icono nombre="x" /></button>
                        </div>
                        <div class="p-4">
                            <div class="max-h-[55vh] overflow-hidden bg-slate-100 rounded-xl">
                                <img id="crop-image" class="block max-w-full" alt="Imagen a recortar">
                            </div>
                            <p class="text-[11px] text-slate-400 mt-2">Arrastrá y ajustá el recuadro para elegir la parte de la imagen que se va a usar como logo.</p>
                        </div>
                        <div class="px-5 py-3.5 border-t border-slate-100 flex justify-end gap-2">
                            <button type="button" id="crop-cancel-2" class="sg-btn sg-btn--borde">Cancelar</button>
                            <button type="button" id="crop-confirm" class="sg-btn sg-btn--azul">Recortar y usar</button>
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div class="sm:col-span-2">
                        <label class="sg-label">NOMBRE DE LA INSTITUCIÓN *</label>
                        <input type="text" name="nombre_institucion" data-solo="letras" data-max-len="40" maxlength="40" required
                               value="{{ old('nombre_institucion', $configuracion->nombre_institucion ?? '') }}"
                               class="sg-input w-full">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="sg-label">DIRECCIÓN</label>
                        <input type="text" name="direccion" data-solo="direccion" data-max-len="100" maxlength="100"
                               value="{{ old('direccion', $configuracion->direccion ?? '') }}"
                               class="sg-input w-full">
                    </div>

                    <div>
                        <label class="sg-label">NOMBRE DEL/LA DIRECTOR/A</label>
                        <input type="text" name="nombre_director" data-solo="letras" data-max-len="20" maxlength="20"
                               value="{{ old('nombre_director', $configuracion->nombre_director ?? '') }}"
                               class="sg-input w-full">
                    </div>

                    <div>
                        <label class="sg-label">TELÉFONO DE CONTACTO</label>
                        <input type="text" name="telefono_contacto" data-solo="numeros" data-max-len="20" maxlength="20"
                               value="{{ old('telefono_contacto', $configuracion->telefono_contacto ?? '') }}"
                               class="sg-input w-full">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="sg-label">EMAIL DE CONTACTO</label>
                        <input type="email" name="email_contacto" maxlength="40"
                               value="{{ old('email_contacto', $configuracion->email_contacto ?? '') }}"
                               class="sg-input w-full">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="sg-label">HORARIOS DE ATENCIÓN</label>
                        <input type="text" name="horario_atencion" maxlength="60" placeholder="Ej: Lunes a viernes, 14:00 a 22:00"
                               value="{{ old('horario_atencion', $configuracion->horario_atencion ?? '') }}"
                               class="sg-input w-full">
                        <p class="text-[11px] text-slate-400 mt-1">Se muestra en la sección "Ubicación" de la página pública (landing).</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-amber-100 bg-gradient-to-r from-amber-50/70 to-white p-5 mt-6 mb-5">
                    <p class="font-display font-bold text-slate-800 flex items-center gap-2 mb-1"><x-sg.icono nombre="sparkles" class="h-5 w-5 text-[#F5B000]" /> Números destacados en la landing</p>
                    <p class="text-xs text-slate-500 mb-4">Si dejás un campo vacío, se muestra el dato real calculado por el sistema.</p>
                    <div class="grid sm:grid-cols-4 gap-4">
                        <div>
                            <label class="sg-label">AÑOS DE FORMACIÓN</label>
                            <input type="number" min="0" max="999" name="landing_anios_formacion"
                                   value="{{ old('landing_anios_formacion', $configuracion->landing_anios_formacion ?? '') }}"
                                   placeholder="{{ $carrera->duracion_anos ?? 3 }}"
                                   class="sg-input w-full">
                        </div>
                        <div>
                            <label class="sg-label">DOCENTES ACTIVOS</label>
                            <input type="number" min="0" max="999" name="landing_docentes_cantidad"
                                   value="{{ old('landing_docentes_cantidad', $configuracion->landing_docentes_cantidad ?? '') }}"
                                   placeholder="{{ $docentesActivos ?? 0 }}"
                                   class="sg-input w-full">
                        </div>
                        <div>
                            <label class="sg-label">ALUMNOS CURSANDO</label>
                            <input type="number" min="0" max="999" name="landing_alumnos_cantidad"
                                   value="{{ old('landing_alumnos_cantidad', $configuracion->landing_alumnos_cantidad ?? '') }}"
                                   placeholder="{{ $alumnosActivos ?? 0 }}"
                                   class="sg-input w-full">
                        </div>
                        <div>
                            <label class="sg-label">EGRESADOS</label>
                            <input type="number" min="0" max="999" name="landing_egresados_cantidad"
                                   value="{{ old('landing_egresados_cantidad', $configuracion->landing_egresados_cantidad ?? '') }}"
                                   placeholder="120"
                                   class="sg-input w-full">
                        </div>
                    </div>
                </div>

                @if ($configuracion?->fecha_ultima_modificacion)
                    <p class="text-xs text-slate-400 mb-4 flex items-center gap-1.5">
                        <x-sg.icono nombre="clock" class="h-3.5 w-3.5" /> Última modificación: {{ \App\Support\FechaEsp::corta($configuracion->fecha_ultima_modificacion) }}
                        @if ($configuracion->secretarioModifica)
                            por {{ $configuracion->secretarioModifica->apellido }}, {{ $configuracion->secretarioModifica->nombre }}
                        @endif
                    </p>
                @endif

                <div class="flex justify-end">
                    <button type="submit" class="sg-btn sg-btn--primario"><x-sg.icono nombre="check" /> Guardar cambios</button>
                </div>
            </form>

            @if ($configuracion)
                <form method="POST" action="{{ route('director.configuracion.destroy') }}"
                      onsubmit="return confirm('¿Eliminar los datos institucionales cargados? Esta acción no se puede deshacer.');"
                      class="mt-4 pt-4 border-t border-slate-100 flex justify-end">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="sg-btn sg-btn--peligro"><x-sg.icono nombre="trash" /> Eliminar datos</button>
                </form>
            @endif
        </div>
    </div>

    <div class="sg-card sg-revelar overflow-hidden mb-8">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--celeste"><x-sg.icono nombre="photo" /></span>
                <div><h3>Imágenes de portada (landing)</h3><p>Carrusel de fondo de la página pública</p></div>
            </div>
        </div>

        <div class="p-6">
            <p class="text-xs text-slate-500 mb-4">
                Se muestran como carrusel de fondo en la portada de la página pública. Si no cargás ninguna, se usa un fondo de color por defecto.
            </p>

            <form method="POST" action="{{ route('director.configuracion.imagenes.store') }}" enctype="multipart/form-data">
                @csrf
                <label id="zona-arrastrar" for="imagenes-input"
                       class="group flex flex-col items-center justify-center gap-2 rounded-3xl border-2 border-dashed border-slate-200 bg-gradient-to-b from-slate-50 to-white px-6 py-12 text-center cursor-pointer transition hover:border-[#4FC3F7] hover:bg-[#0B3D91]/5">
                    <span class="sg-vacio__icono !mb-2"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-8 w-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                    </svg></span>
                    <p class="text-sm font-semibold text-slate-600">Arrastrá imágenes acá, o hacé clic para elegirlas</p>
                    <p class="text-[11px] text-slate-400">JPG, PNG o WEBP. Podés seleccionar varias a la vez.</p>
                    <input id="imagenes-input" type="file" name="imagenes[]" accept="image/png,image/jpeg,image/webp" multiple class="hidden">
                </label>
                @error('imagenes.*')
                    <p class="text-[11px] text-red-500 mt-2">{{ $message }}</p>
                @enderror
                <div id="imagenes-seleccionadas" class="text-xs text-slate-500 mt-2"></div>
                <div class="flex justify-end mt-3">
                    <button type="submit" class="sg-btn sg-btn--azul"><x-sg.icono nombre="upload" /> Subir imágenes</button>
                </div>
            </form>

            @if ($imagenesLanding->isNotEmpty())
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6">
                    @foreach ($imagenesLanding as $imagen)
                        <div class="relative group rounded-2xl overflow-hidden ring-1 ring-slate-200 aspect-video shadow-sm hover:shadow-lg transition-shadow">
                            <img src="{{ $imagen->url }}" alt="" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            <form method="POST" action="{{ route('director.configuracion.imagenes.destroy', $imagen) }}"
                                  onsubmit="return confirm('¿Eliminar esta imagen de la portada?');"
                                  class="absolute top-1.5 right-1.5">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="h-8 w-8 grid place-items-center rounded-lg bg-white/90 text-red-600 shadow opacity-0 group-hover:opacity-100 transition" title="Eliminar"><x-sg.icono nombre="trash" class="h-4 w-4" /></button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400 mt-5 text-center">Todavía no cargaste imágenes de portada.</p>
            @endif
        </div>
    </div>

    <div class="sg-card sg-revelar overflow-hidden mb-8">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--amarillo"><x-sg.icono nombre="calendar" /></span>
                <div><h3>Años lectivos (cortes)</h3><p>Períodos de trabajo del sistema</p></div>
            </div>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('director.configuracion.cortes.store') }}" class="grid sm:grid-cols-[1fr_auto] gap-4 mb-6">
                @csrf
                <div>
                    <label class="sg-label">NUEVO CORTE (AÑO) *</label>
                    <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="4" maxlength="4" name="anio" placeholder="{{ now()->year + 1 }}" required
                           value="{{ old('anio') }}"
                           class="sg-input w-full">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="sg-btn sg-btn--azul"><x-sg.icono nombre="plus" /> Cargar corte</button>
                </div>
            </form>

            <div class="grid gap-3 sm:grid-cols-2" data-sg-escalonar>
                @forelse ($aniosLectivos as $anio)
                    <div class="flex items-center justify-between rounded-2xl border-[1.5px] px-4 py-3 transition-colors {{ $anio->estadoAnio?->nombre_estado === 'Activo' ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-100 bg-white' }}">
                        <div class="flex items-center gap-3">
                            <span class="grid h-11 w-11 place-items-center rounded-xl {{ $anio->estadoAnio?->nombre_estado === 'Activo' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }} font-display font-extrabold text-xs">{{ substr($anio->anio, -2) }}</span>
                            <span class="font-display font-bold text-slate-800">Corte {{ $anio->anio }}</span>
                            @if ($anio->estadoAnio?->nombre_estado === 'Activo')
                                <span class="sg-badge sg-badge--verde">Activo</span>
                            @else
                                <span class="sg-badge sg-badge--gris">Cerrado</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('director.configuracion.cortes.estado', $anio) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="sg-btn sg-btn--borde sg-btn--chico">
                                    {{ $anio->estadoAnio?->nombre_estado === 'Activo' ? 'Cerrar' : 'Reactivar' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('director.configuracion.cortes.destroy', $anio) }}"
                                  onsubmit="return confirm('¿Eliminar el corte {{ $anio->anio }}? Sólo se puede eliminar si no tiene datos cargados.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors" title="Eliminar corte"><x-sg.icono nombre="trash" class="h-4 w-4" /></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <x-sg.vacio icono="calendar" class="sm:col-span-2">Todavía no hay cortes cargados.</x-sg.vacio>
                @endforelse
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script>
        (function () {
            const logoInput = document.getElementById('logo-input');
            const modal = document.getElementById('crop-modal');
            const cropImage = document.getElementById('crop-image');
            const preview = document.getElementById('logo-preview');
            const previewIcon = document.getElementById('logo-preview-icon');
            let cropper = null;

            function abrirModal(dataUrl) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                cropImage.src = dataUrl;
                if (cropper) cropper.destroy();
                cropper = new Cropper(cropImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    background: false,
                    autoCropArea: 1,
                });
            }

            function cerrarModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                if (cropper) { cropper.destroy(); cropper = null; }
            }

            logoInput.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = e => abrirModal(e.target.result);
                reader.readAsDataURL(file);
            });

            document.getElementById('crop-cancel').addEventListener('click', () => { logoInput.value = ''; cerrarModal(); });
            document.getElementById('crop-cancel-2').addEventListener('click', () => { logoInput.value = ''; cerrarModal(); });

            document.getElementById('crop-confirm').addEventListener('click', function () {
                if (!cropper) return;
                cropper.getCroppedCanvas({ width: 500, height: 500, imageSmoothingQuality: 'high' }).toBlob(function (blob) {
                    const recortada = new File([blob], 'logo.png', { type: 'image/png' });
                    const dt = new DataTransfer();
                    dt.items.add(recortada);
                    logoInput.files = dt.files;

                    preview.src = URL.createObjectURL(blob);
                    preview.classList.remove('hidden');
                    if (previewIcon) previewIcon.classList.add('hidden');

                    cerrarModal();
                }, 'image/png');
            });
        })();

        (function () {
            const zona = document.getElementById('zona-arrastrar');
            const input = document.getElementById('imagenes-input');
            const resumen = document.getElementById('imagenes-seleccionadas');
            if (!zona || !input) return;

            function mostrarResumen() {
                const n = input.files.length;
                resumen.textContent = n ? `${n} imagen(es) lista(s) para subir.` : '';
            }

            input.addEventListener('change', mostrarResumen);

            ['dragenter', 'dragover'].forEach(evento => {
                zona.addEventListener(evento, e => {
                    e.preventDefault();
                    zona.classList.add('border-[#0B3D91]', 'bg-[#0B3D91]/5');
                });
            });
            ['dragleave', 'drop'].forEach(evento => {
                zona.addEventListener(evento, e => {
                    e.preventDefault();
                    zona.classList.remove('border-[#0B3D91]', 'bg-[#0B3D91]/5');
                });
            });
            zona.addEventListener('drop', e => {
                if (e.dataTransfer?.files?.length) {
                    input.files = e.dataTransfer.files;
                    mostrarResumen();
                }
            });
        })();
    </script>
@endsection
