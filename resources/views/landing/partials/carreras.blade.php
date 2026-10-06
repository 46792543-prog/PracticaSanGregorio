{{--
    Carreras y plan de estudios — 100% dinámico. $carreras y
    $planesPorCarrera (App\Models\Materia, agrupadas por año) se arman en
    LandingController a partir de lo que ya está cargado en Admin > Carreras.
    Si se agrega una carrera nueva ahí, aparece acá sola, sin tocar esta
    vista ni Configuración. Con una sola carrera no se muestran pestañas;
    con dos o más, sí.
--}}
<section id="carreras" data-landing-seccion class="landing-seccion landing-seccion--celeste">
    <div class="landing-seccion__blobs" aria-hidden="true">
        <span class="landing-burbuja" style="width:230px;height:230px;background:var(--landing-amarillo);top:-50px;right:4%;animation-delay:-5s;"></span>
        <span class="landing-burbuja" style="width:190px;height:190px;background:var(--landing-azul);opacity:.1;bottom:-30px;left:2%;animation-delay:-1s;"></span>
        <svg class="landing-flotar-icono landing-flotar-icono--lento" style="top:12%;left:3%;--landing-rot:-8deg;width:30px;height:30px;color:rgba(11,61,145,.14)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.04A9 9 0 0 0 6 3.75c-1.05 0-2.06.18-3 .51v14.25A9 9 0 0 1 6 18c2.3 0 4.4.87 6 2.29m0-14.25a9 9 0 0 1 6-2.29c1.05 0 2.06.18 3 .51v14.25A9 9 0 0 0 18 18a9 9 0 0 0-6 2.29m0-14.25v14.25" />
        </svg>
    </div>

    <div class="landing-contenedor">
        <div class="landing-seccion__cabecera landing-reveal">
            <span class="landing-etiqueta"><span class="landing-etiqueta__punto"></span> Carreras</span>
            <h2 class="landing-titulo-h2">
                @if ($carreras->count() === 1)
                    {{ $carreras->first()->nombre_carrera }}
                @else
                    Nuestras carreras
                @endif
            </h2>
            <p class="landing-bajada">Formación con título oficial de nivel superior.</p>
        </div>

        @if ($carreras->isEmpty())
            <p class="landing-reveal" style="color:var(--landing-texto-suave);">
                Todavía no hay carreras cargadas. Muy pronto vas a poder verlas acá.
            </p>
        @else
            @if ($carreras->count() > 1)
                <div class="landing-tabs-carrera landing-reveal" role="tablist" aria-label="Elegir carrera">
                    @foreach ($carreras as $i => $c)
                        <button type="button" class="landing-tab-carrera {{ $i === 0 ? 'activo' : '' }}"
                                data-landing-tab-carrera="{{ $c->id_carrera }}" role="tab" aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                            {{ $c->nombre_carrera }}
                        </button>
                    @endforeach
                </div>
            @endif

            @foreach ($carreras as $i => $c)
                @php $plan = $planesPorCarrera[$c->id_carrera] ?? collect(); @endphp
                <div class="landing-panel-carrera {{ $i === 0 ? 'activo' : '' }}" data-landing-panel-carrera="{{ $c->id_carrera }}">
                    <p class="landing-bajada" style="margin-bottom:24px;">
                        {{ $c->duracion_anos }} años de estudio con título oficial de nivel superior.
                    </p>

                    @if ($plan->isNotEmpty())
                        <div class="landing-grid landing-grid--3">
                            @php $variantes = ['', 'landing-tarjeta--b', 'landing-tarjeta--c']; $variantesIcono = ['', 'landing-tarjeta__icono--b', 'landing-tarjeta__icono--c']; @endphp
                            @foreach ($plan as $nombreAnio => $materias)
                                <div class="landing-tarjeta landing-tarjeta--borde-superior {{ $variantes[$loop->index % 3] }} landing-reveal" style="--landing-retraso:{{ $loop->index * 110 }}ms">
                                    <span class="landing-tarjeta__icono {{ $variantesIcono[$loop->index % 3] }}" style="font-family:var(--landing-fuente-titulo);font-weight:800;" aria-hidden="true">{{ $loop->iteration }}°</span>
                                    <p class="landing-tarjeta__titulo">{{ $nombreAnio }}</p>
                                    <ul style="display:grid;gap:9px;margin-top:6px;">
                                        @foreach ($materias as $materia)
                                            <li style="display:flex;gap:9px;align-items:flex-start;font-size:.88rem;color:var(--landing-texto-suave);">
                                                <span style="width:6px;height:6px;border-radius:50%;background:var(--landing-amarillo);margin-top:7px;flex-shrink:0;"></span>
                                                {{ $materia->nombre }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="color:var(--landing-texto-suave);">El plan de estudios de esta carrera se está cargando.</p>
                    @endif
                </div>
            @endforeach
        @endif

        <div class="landing-reveal landing-franja-viva" style="margin-top:36px;background:linear-gradient(120deg,var(--landing-azul),var(--landing-azul-oscuro));border-radius:var(--landing-radio);padding:36px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:20px;color:#fff;">
            <div>
                <p style="font-weight:800;font-size:1.25rem;">¿Listo para empezar?</p>
                <p style="color:rgba(255,255,255,.8);margin-top:4px;">Inscribite hoy y recibí asesoramiento personalizado.</p>
            </div>
            <a href="#ubicacion" class="landing-btn landing-btn--primario">Quiero inscribirme →</a>
        </div>
    </div>
</section>
