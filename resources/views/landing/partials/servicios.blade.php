{{--
    Información / Servicios. $servicios viene de LandingController@index
    (array editable: icono, titulo, texto). Los íconos están definidos acá
    como un pequeño set de SVG en línea, sin librerías externas.
--}}
<section id="servicios" data-landing-seccion class="landing-seccion">
    <div class="landing-seccion__blobs" aria-hidden="true">
        <span class="landing-burbuja" style="width:260px;height:260px;background:var(--landing-azul);opacity:.08;top:-40px;left:4%;animation-delay:-1s;"></span>
        <span class="landing-burbuja" style="width:200px;height:200px;background:var(--landing-amarillo);opacity:.14;bottom:0;right:5%;animation-delay:-6s;"></span>
        <svg class="landing-flotar-icono" style="top:10%;right:3%;--landing-rot:10deg;width:32px;height:32px;color:rgba(11,61,145,.14)" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11 2h2v9h9v2h-9v9h-2v-9H2v-2h9V2Z"/></svg>
    </div>

    <div class="landing-contenedor">
        <div class="landing-seccion__cabecera landing-seccion__cabecera--centro landing-reveal">
            <span class="landing-etiqueta"><span class="landing-etiqueta__punto"></span> Información</span>
            <h2 class="landing-titulo-h2">Todo lo que necesitás, <span class="landing-acento">en un solo lugar</span></h2>
            <p class="landing-bajada">Herramientas y acompañamiento para tu vida de estudiante.</p>
        </div>

        <div class="landing-grid landing-grid--3">
            @php
                $iconos = [
                    'campus' => 'M3.75 9.75h16.5M3.75 9.75v8.25A2.25 2.25 0 0 0 6 20.25h12a2.25 2.25 0 0 0 2.25-2.25V9.75M3.75 9.75V6A2.25 2.25 0 0 1 6 3.75h12A2.25 2.25 0 0 1 20.25 6v3.75M8.25 14.25h2.25',
                    'inscripcion' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                    'practicas' => 'M9.348 14.652a3.75 3.75 0 0 1 0-5.304l4.5-4.5a3.75 3.75 0 1 1 5.304 5.304l-.657.657M9.348 14.652 8 16a3.75 3.75 0 1 0 5.304 5.304l.828-.828',
                    'biblioteca' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
                    'tutorias' => 'M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z',
                    'examenes' => 'M9 12h6m-6 3h6m-7.5 6h9A2.25 2.25 0 0 0 18 18.75V5.25A2.25 2.25 0 0 0 15.75 3h-9A2.25 2.25 0 0 0 4.5 5.25v13.5A2.25 2.25 0 0 0 6.75 21Z',
                    'simulacion' => 'M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 20.25c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5',
                    'becas' => 'M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18A1.125 1.125 0 0 0 2.25 8.625v1.5c0 .621.504 1.125 1.125 1.125Z',
                ];
                $variantes = ['', 'landing-tarjeta__icono--b', 'landing-tarjeta__icono--c'];
            @endphp
            @foreach ($servicios as $i => $servicio)
                <div class="landing-tarjeta landing-reveal" style="--landing-retraso:{{ $i * 80 }}ms">
                    <span class="landing-tarjeta__icono {{ $variantes[$i % 3] }}" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconos[$servicio['icono']] ?? $iconos['campus'] }}" />
                        </svg>
                    </span>
                    <p class="landing-tarjeta__titulo">{{ $servicio['titulo'] }}</p>
                    <p class="landing-tarjeta__texto">{{ $servicio['texto'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
