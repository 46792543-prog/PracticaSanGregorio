<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUsuarioActivo
{
    /**
     * Corta en caliente la sesión de un usuario que quedó dado de baja
     * mientras ya estaba logueado (ver AlumnoController::baja y
     * LoginController::login, que hacen el mismo chequeo al momento de entrar).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->estadoUsuario?->nombre_estado !== 'Activo') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta está dada de baja. Consultá con la secretaría.']);
        }

        return $next($request);
    }
}
