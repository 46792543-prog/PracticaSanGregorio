<?php

/*
 * Router de `php artisan serve` (Laravel lo usa en lugar del suyo si existe este archivo).
 *
 * El servidor integrado de PHP manda los archivos de public/ sin ningún encabezado de caché,
 * así que el navegador volvía a descargar el CSS, el JS y las imágenes en CADA pantalla, uno
 * detrás del otro (ese servidor atiende un pedido a la vez). Acá se sirven con caché:
 *
 *  - Con ?v=... en la URL (asset() + filemtime en las vistas): se guardan por un año; cuando el
 *    archivo cambia, cambia el ?v= y el navegador baja la versión nueva.
 *  - Sin ?v= (fotos subidas, logos): el navegador pregunta si cambió y, si no, recibe un
 *    "304 sin cambios" vacío en lugar del archivo entero.
 *
 * En un hosting con Apache/Nginx este archivo no se usa.
 */

$publicPath = getcwd();

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

if ($uri !== '/' && file_exists($publicPath.$uri)) {
    $tipos = [
        'css' => 'text/css; charset=UTF-8',
        'js' => 'text/javascript; charset=UTF-8',
        'json' => 'application/json',
        'map' => 'application/json',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
    ];

    $archivo = realpath($publicPath.$uri);
    $extension = strtolower(pathinfo($uri, PATHINFO_EXTENSION));

    // Directorios, tipos no listados (PDF, videos, etc.) o rutas fuera de public/: como antes.
    if (! $archivo || ! is_file($archivo) || ! isset($tipos[$extension])
        || ! str_starts_with($archivo, realpath($publicPath).DIRECTORY_SEPARATOR)) {
        return false;
    }

    $modificado = filemtime($archivo);
    $etag = '"'.dechex($modificado).'-'.dechex(filesize($archivo)).'"';

    header('Content-Type: '.$tipos[$extension]);
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', $modificado).' GMT');
    header('ETag: '.$etag);
    header(isset($_GET['v'])
        ? 'Cache-Control: public, max-age=31536000, immutable'
        : 'Cache-Control: no-cache');

    if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? null) === $etag
        || (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $modificado)) {
        http_response_code(304);

        return true;
    }

    header('Content-Length: '.filesize($archivo));
    readfile($archivo);

    return true;
}

require_once $publicPath.'/index.php';
