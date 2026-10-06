{{--
    Contadores animados. $contadores viene de LandingController@index
    (valor, sufijo, etiqueta) — editable sin tocar esta vista. Los años de
    formación y docentes/alumnos activos ya salen de datos reales del sistema.
--}}
<section class="landing-contadores landing-franja-viva landing-seccion" style="padding:64px 0;" aria-label="Datos de la institución">
    <div class="landing-seccion__blobs" aria-hidden="true">
        <span class="landing-burbuja" style="width:220px;height:220px;background:var(--landing-celeste);opacity:.18;top:-70px;left:10%;animation-delay:-3s;"></span>
        <span class="landing-burbuja" style="width:180px;height:180px;background:var(--landing-amarillo);opacity:.14;bottom:-60px;right:8%;animation-delay:-8s;"></span>
    </div>
    <div class="landing-contenedor landing-grid landing-grid--4">
        @foreach ($contadores as $i => $c)
            <div class="landing-contador landing-reveal" style="--landing-retraso:{{ $i * 90 }}ms">
                <p class="landing-contador__num">
                    <span data-landing-contar="{{ $c['valor'] }}" data-landing-sufijo="{{ $c['sufijo'] }}">0</span>
                </p>
                <p class="landing-contador__etq">{{ $c['etiqueta'] }}</p>
            </div>
        @endforeach
    </div>
</section>
