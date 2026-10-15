<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionInstitucion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login', ['config' => ConfiguracionInstitucion::first()]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:100'],
            'password' => ['required', 'string', 'max:100'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Las credenciales no coinciden con ningún registro.'])->onlyInput('email');
        }

        // El email y la contraseña pueden ser correctos aunque el usuario esté
        // dado de baja (ver AlumnoController::baja), así que se chequea el
        // estado recién acá, con un mensaje propio en vez del genérico de arriba.
        if (Auth::user()->estadoUsuario?->nombre_estado !== 'Activo') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'Tu cuenta está dada de baja. Consultá con la secretaría.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        // Bandera de un solo uso: le avisa a la primera página autenticada que
        // se acaba de iniciar sesión en ESTA pestaña, para que el guard de
        // sesión (ver partials.session-guard) no la cierre por error.
        $request->session()->flash('recien_logueado', true);

        // Siempre al panel propio: no se respeta la URL que se haya escrito
        // antes de loguearse (ej. /director/auditoria), para que no se pueda
        // entrar directo a una pantalla pegando su dirección.
        $request->session()->forget('url.intended');

        return redirect(Auth::user()->rutaInicio());
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // El guard de sesión (partials.session-guard) cierra la sesión solo cuando
        // se vuelve a entrar sin haber pasado por el login; se avisa por qué.
        if ($request->input('motivo') === 'reingreso') {
            return redirect()->route('login')->withErrors(['email' => 'Por seguridad se cerró tu sesión. Volvé a iniciar sesión para continuar.']);
        }

        return redirect()->route('login');
    }
}
