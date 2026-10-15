<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Las páginas con sesión iniciada no se guardan en la caché del navegador:
 * así, después de cerrar sesión, el botón "atrás" no vuelve a mostrar
 * pantallas del sistema (el navegador tiene que pedirlas de nuevo y el
 * servidor lo manda al login).
 */
class SinCacheAutenticado
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
