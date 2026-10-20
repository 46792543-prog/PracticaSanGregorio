<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mientras el usuario siga con la clave inicial que le generó la secretaría,
 * no puede usar el sistema: se lo manda a cambiarla (ver CambioClaveController).
 */
class EnsureClaveCambiada
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->debe_cambiar_clave) {
            // Se conserva la bandera del login (ver partials.session-guard)
            // para el panel al que se llega después de cambiar la clave.
            $request->session()->reflash();

            return redirect()->route('password.cambiar');
        }

        return $next($request);
    }
}
