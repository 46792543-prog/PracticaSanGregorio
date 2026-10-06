<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $config?->nombre_institucion ?? 'Instituto Superior San Gregorio' }} — Tecnicatura Superior en Enfermería. Formación con compromiso, prácticas desde 1er año y título oficial.">
    <title>{{ $config?->nombre_institucion ?? 'Instituto Superior San Gregorio' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body class="landing-body">

    @include('landing.partials.header')

    <main>
        @include('landing.partials.hero', ['imagenesHero' => $imagenesHero])
        @include('landing.partials.carrusel', ['novedades' => $novedades])
        @include('landing.partials.nosotros')
        @include('landing.partials.servicios', ['servicios' => $servicios])
        @include('landing.partials.contadores', ['contadores' => $contadores])
        @include('landing.partials.carreras', ['carreras' => $carreras, 'planesPorCarrera' => $planesPorCarrera])
        @include('landing.partials.ubicacion')
    </main>

    @include('landing.partials.footer')

    <script src="{{ asset('js/landing.js') }}" defer></script>
</body>
</html>
