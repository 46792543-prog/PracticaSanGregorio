{{--
    Ubicación y contacto en una sola sección: mapa embebido (sin API key),
    dirección, teléfono, correo y horarios. Todo sale de
    ConfiguracionInstitucion (Director > Configuración), con valores de
    respaldo mientras no se haya cargado nada.
--}}
@php
    $direccionMapa = $config?->direccion ?: 'Pedro Goyena 33, Y4500 San Pedro de Jujuy, Jujuy';
    $urlMapa = 'https://www.google.com/maps?q=' . urlencode(($config?->nombre_institucion ?? 'Instituto Superior San Gregorio') . ', ' . $direccionMapa) . '&output=embed';
    $datosUbicacion = [
        ['icono' => 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z|M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z', 'etq' => 'Dirección', 'valor' => $direccionMapa],
        ['icono' => 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z', 'etq' => 'Teléfono', 'valor' => $config?->telefono_contacto ?: '(388) 000-0000'],
        ['icono' => 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75', 'etq' => 'Correo', 'valor' => $config?->email_contacto ?: 'info@institutosangregorio.edu.ar'],
        ['icono' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'etq' => 'Horarios de atención', 'valor' => $config?->horario_atencion ?: 'Lunes a viernes, 14:00 a 22:00'],
    ];
@endphp
<section id="ubicacion" data-landing-seccion class="landing-seccion">
    <div class="landing-seccion__blobs" aria-hidden="true">
        <span class="landing-burbuja" style="width:240px;height:240px;background:var(--landing-celeste);opacity:.14;top:-50px;right:3%;animation-delay:-2s;"></span>
        <span class="landing-burbuja" style="width:190px;height:190px;background:var(--landing-amarillo);opacity:.12;bottom:-30px;left:5%;animation-delay:-7s;"></span>
    </div>

    <div class="landing-contenedor">
        <div class="landing-seccion__cabecera landing-reveal">
            <span class="landing-etiqueta"><span class="landing-etiqueta__punto"></span> Ubicación y contacto</span>
            <h2 class="landing-titulo-h2">Visitanos o escribinos</h2>
            <p class="landing-bajada">Te esperamos en nuestra sede, o contactanos por teléfono o correo.</p>
        </div>

        <div class="landing-grid landing-ubicacion__grid">
            <div class="landing-reveal" style="display:grid;gap:14px;align-content:start;">
                @php $variantesUbic = ['', 'landing-dato-contacto__icono--b', 'landing-dato-contacto__icono--c']; @endphp
                @foreach ($datosUbicacion as $i => $dato)
                    @php [$iconoA, $iconoB] = array_pad(explode('|', $dato['icono']), 2, null); @endphp
                    <div class="landing-dato-contacto">
                        <span class="landing-dato-contacto__icono {{ $variantesUbic[$i % 3] }}" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconoA }}"/>
                                @if ($iconoB)<path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconoB }}"/>@endif
                            </svg>
                        </span>
                        <div>
                            <p class="landing-dato-contacto__etq">{{ $dato['etq'] }}</p>
                            <p class="landing-dato-contacto__valor">{{ $dato['valor'] }}</p>
                        </div>
                    </div>
                @endforeach
                <a href="{{ 'https://www.google.com/maps/search/' . urlencode($direccionMapa) }}" target="_blank" rel="noopener" class="landing-btn landing-btn--outline-azul" style="justify-self:start;">
                    Cómo llegar →
                </a>
            </div>

            <div class="landing-mapa landing-reveal" style="--landing-retraso:120ms">
                <iframe
                    src="{{ $urlMapa }}"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Ubicación de {{ $config?->nombre_institucion ?? 'Instituto Superior San Gregorio' }} en el mapa">
                </iframe>
            </div>
        </div>
    </div>
</section>
