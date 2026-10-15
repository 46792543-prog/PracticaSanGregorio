<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDirector
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (! $usuario?->esDirector()) {
            abort_unless($usuario && $request->isMethod('GET'), 403);

            // Entró escribiendo la URL de un panel que no le corresponde:
            // se lo devuelve al suyo en lugar de mostrar la pantalla de error.
            return redirect($usuario->rutaInicio())
                ->withErrors(['acceso' => 'No tenés permiso para entrar a esa sección.']);
        }

        return $next($request);
    }
}
