{{-- Historia, misión, visión y valores. --}}
<section id="nosotros" data-landing-seccion class="landing-seccion landing-seccion--celeste">
    <div class="landing-seccion__blobs" aria-hidden="true">
        <span class="landing-burbuja" style="width:240px;height:240px;background:var(--landing-celeste);top:-60px;right:6%;animation-delay:-3s;"></span>
        <span class="landing-burbuja" style="width:180px;height:180px;background:var(--landing-amarillo);bottom:-20px;left:4%;animation-delay:-8s;"></span>
        <svg class="landing-flotar-icono" style="top:8%;left:2%;--landing-rot:-8deg;width:30px;height:30px;color:rgba(11,61,145,.15)" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11 2h2v9h9v2h-9v9h-2v-9H2v-2h9V2Z"/></svg>
        <svg class="landing-flotar-icono landing-flotar-icono--lento" style="bottom:10%;right:3%;--landing-rot:10deg;width:34px;height:34px;color:rgba(11,61,145,.12);animation-delay:-4s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 3v6a3.5 3.5 0 0 0 7 0V3M8 3v2.2M4.5 3v2.2M11.5 11.5V13a5.5 5.5 0 0 0 11 0v-1.2" />
            <circle cx="20" cy="9" r="2.3" />
        </svg>
    </div>

    <div class="landing-contenedor">
        <div class="landing-seccion__cabecera landing-reveal">
            <span class="landing-etiqueta"><span class="landing-etiqueta__punto"></span> Nuestra institución</span>
            <h2 class="landing-titulo-h2">Una comunidad que forma profesionales</h2>
            <p class="landing-bajada">
                {{ $config?->nombre_institucion ?? 'El Instituto Superior San Gregorio' }} es una institución de nivel terciario
                del área de la salud. Formamos técnicos y técnicas en enfermería con una mirada humana, rigor académico
                y prácticas desde el primer año en hospitales y centros de salud de la región.
            </p>
        </div>

        <div class="landing-grid landing-grid--3">
            @php $variantes = ['', 'landing-tarjeta--b', 'landing-tarjeta--c']; $variantesIcono = ['', 'landing-tarjeta__icono--b', 'landing-tarjeta__icono--c']; @endphp
            @foreach ([
                ['icono' => 'mision', 'titulo' => 'Misión', 'texto' => 'Formar profesionales de la salud competentes, éticos y solidarios, comprometidos con su comunidad.'],
                ['icono' => 'vision', 'titulo' => 'Visión', 'texto' => 'Ser una institución de referencia en la formación en enfermería, innovadora y cercana a las personas.'],
                ['icono' => 'valores', 'titulo' => 'Valores', 'texto' => 'Respeto, responsabilidad, empatía y compromiso con la vida y con el cuidado de los demás.'],
            ] as $i => $item)
                <div class="landing-tarjeta landing-tarjeta--borde-superior {{ $variantes[$i % 3] }} landing-reveal" style="--landing-retraso:{{ $i * 110 }}ms">
                    <span class="landing-tarjeta__icono {{ $variantesIcono[$i % 3] }}" aria-hidden="true">
                        @switch($item['icono'])
                            @case('mision')
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                @break
                            @case('vision')
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                @break
                            @default
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                        @endswitch
                    </span>
                    <p class="landing-tarjeta__titulo">{{ $item['titulo'] }}</p>
                    <p class="landing-tarjeta__texto">{{ $item['texto'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
