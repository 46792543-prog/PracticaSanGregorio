{{--
    Carrusel de novedades institucionales. Autoplay + flechas + indicadores
    + swipe táctil + barra de progreso, todo manejado por landing.js (ver
    data-landing-carrusel). $novedades se define en LandingController@index
    — editable sin tocar esta vista.
--}}
<section data-landing-seccion class="landing-seccion" aria-label="Novedades institucionales">
    <div class="landing-seccion__blobs" aria-hidden="true">
        <span class="landing-burbuja" style="width:220px;height:220px;background:var(--landing-celeste);opacity:.1;top:-50px;left:6%;animation-delay:-4s;"></span>
        <span class="landing-burbuja" style="width:170px;height:170px;background:var(--landing-amarillo);opacity:.12;bottom:-30px;right:5%;animation-delay:-9s;"></span>
    </div>

    <div class="landing-contenedor">
        <div class="landing-seccion__cabecera landing-seccion__cabecera--centro landing-reveal">
            <span class="landing-etiqueta"><span class="landing-etiqueta__punto"></span> Novedades</span>
            <h2 class="landing-titulo-h2">Lo último en <span class="landing-acento">San Gregorio</span></h2>
            <p class="landing-bajada">Un vistazo a la vida institucional, las prácticas y los logros de nuestra comunidad educativa.</p>
        </div>

        <div class="landing-carrusel landing-reveal" data-landing-carrusel style="--landing-retraso:120ms">
            <div class="landing-carrusel__viewport">
                <div class="landing-carrusel__pista">
                    @php
                        $iconosNovedad = [
                            'practica' => 'M4.5 3v6a3.5 3.5 0 0 0 7 0V3M8 3v2.2M4.5 3v2.2M11.5 11.5V13a5.5 5.5 0 0 0 11 0v-1.2|circle:20,9,2.3',
                            'comunidad' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z',
                            'institucional' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 21.53a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z',
                        ];
                    @endphp
                    @foreach ($novedades as $i => $novedad)
                        @php [$pathIcono, $circuloIcono] = array_pad(explode('|circle:', $iconosNovedad[$novedad['icono']] ?? $iconosNovedad['institucional']), 2, null); @endphp
                        <article class="landing-carrusel__slide landing-franja-viva">
                            <span class="landing-carrusel__num">{{ sprintf('%02d', $i + 1) }} / {{ sprintf('%02d', count($novedades)) }}</span>
                            <span class="landing-carrusel__icono" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $pathIcono }}" />
                                    @if ($circuloIcono)
                                        @php [$cx, $cy, $r] = explode(',', $circuloIcono); @endphp
                                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" />
                                    @endif
                                </svg>
                            </span>
                            <span class="landing-etiqueta" style="background:rgba(255,255,255,.15);color:#fff;">{{ $novedad['etiqueta'] }}</span>
                            <h3 style="color:#fff;font-size:1.5rem;margin-top:10px;">{{ $novedad['titulo'] }}</h3>
                            <p style="color:rgba(255,255,255,.85);margin-top:8px;max-width:620px;">{{ $novedad['texto'] }}</p>
                        </article>
                    @endforeach
                </div>
                <div class="landing-carrusel__progreso" aria-hidden="true"><span data-landing-progreso></span></div>
            </div>
            <div class="landing-carrusel__controles">
                <button type="button" class="landing-carrusel__flecha" data-landing-anterior aria-label="Novedad anterior">‹</button>
                <div class="landing-carrusel__puntos" role="tablist" aria-label="Seleccionar novedad"></div>
                <button type="button" class="landing-carrusel__flecha" data-landing-siguiente aria-label="Novedad siguiente">›</button>
            </div>
        </div>
    </div>
</section>
