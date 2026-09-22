@extends('layouts.director')

@section('titulo', 'Configuración Institucional')
@section('subtitulo', 'Datos generales de la institución, usados en actas y documentación oficial')

@section('contenido')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
        <div class="bg-[#1E4D8C] px-6 py-3.5">
            <h2 class="text-white font-bold text-sm flex items-center gap-2">🏫 Datos de la Institución</h2>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('director.configuracion.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-6 flex items-center gap-4">
                    <div id="logo-preview-wrap" class="h-16 w-16 shrink-0 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#a97b0e] shadow ring-1 ring-slate-200 overflow-hidden grid place-items-center">
                        <img id="logo-preview" src="{{ $configuracion?->logo_url }}" alt="Logo institucional" class="{{ $configuracion?->logo_url ? '' : 'hidden' }} h-full w-full object-cover">
                        <svg id="logo-preview-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#0e2242" stroke-width="1.7" class="{{ $configuracion?->logo_url ? 'hidden' : '' }} h-8 w-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25 4.5 5.4v5.4c0 5.13 3.24 9.6 7.5 10.95 4.26-1.35 7.5-5.82 7.5-10.95V5.4L12 2.25Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2.25 2.25L15.5 9.5" />
                        </svg>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">LOGO DE LA INSTITUCIÓN</label>
                        <input type="file" id="logo-input" name="logo" accept="image/png,image/jpeg,image/webp"
                               class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-[#1E4D8C]/10 file:text-[#1E4D8C] file:px-3 file:py-1.5 file:text-xs file:font-semibold hover:file:bg-[#1E4D8C]/20">
                        <p class="text-[11px] text-slate-400 mt-1">JPG, PNG o WEBP. Vas a poder recortarla antes de guardar.</p>
                        @error('logo')
                            <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div id="crop-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/60 px-4">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="font-bold text-sm text-slate-700">Recortar logo</h3>
                            <button type="button" id="crop-cancel" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="p-4">
                            <div class="max-h-[55vh] overflow-hidden bg-slate-100 rounded-xl">
                                <img id="crop-image" class="block max-w-full" alt="Imagen a recortar">
                            </div>
                            <p class="text-[11px] text-slate-400 mt-2">Arrastrá y ajustá el recuadro para elegir la parte de la imagen que se va a usar como logo.</p>
                        </div>
                        <div class="px-5 py-3.5 border-t border-slate-100 flex justify-end gap-2">
                            <button type="button" id="crop-cancel-2" class="rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm px-4 py-2 hover:bg-slate-50 transition">Cancelar</button>
                            <button type="button" id="crop-confirm" class="rounded-xl bg-[#1E4D8C] hover:shadow-md text-white font-semibold text-sm px-4 py-2 transition">Recortar y usar</button>
                        </div>
                    </div>
                </div>

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

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
        <div class="bg-[#1E4D8C] px-6 py-3.5">
            <h2 class="text-white font-bold text-sm flex items-center gap-2">📅 Años Lectivos (Cortes)</h2>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('director.configuracion.cortes.store') }}" class="grid sm:grid-cols-[1fr_auto] gap-4 mb-6">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">NUEVO CORTE (AÑO) *</label>
                    <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="4" maxlength="4" name="anio" placeholder="{{ now()->year + 1 }}" required
                           value="{{ old('anio') }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="rounded-xl bg-[#1E4D8C] hover:shadow-md text-white font-semibold text-sm px-6 py-2.5 transition">➕ Cargar corte</button>
                </div>
            </form>

            <div class="space-y-2">
                @forelse ($aniosLectivos as $anio)
                    <div class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <span class="font-bold text-slate-700 text-sm">Corte {{ $anio->anio }}</span>
                            @if ($anio->estadoAnio?->nombre_estado === 'Activo')
                                <span class="text-xs font-semibold rounded-full px-3 py-1 bg-emerald-100 text-emerald-700">Activo</span>
                            @else
                                <span class="text-xs font-semibold rounded-full px-3 py-1 bg-slate-100 text-slate-500">Cerrado</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('director.configuracion.cortes.estado', $anio) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-xs px-3 py-1.5 transition">
                                    {{ $anio->estadoAnio?->nombre_estado === 'Activo' ? 'Cerrar' : 'Reactivar' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('director.configuracion.cortes.destroy', $anio) }}"
                                  onsubmit="return confirm('¿Eliminar el corte {{ $anio->anio }}? Sólo se puede eliminar si no tiene datos cargados.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600" title="Eliminar corte">🗑️</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Todavía no hay cortes cargados.</p>
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
    </script>
@endsection
