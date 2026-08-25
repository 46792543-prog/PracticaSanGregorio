<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
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

        $request->session()->regenerate();

        // Bandera de un solo uso: le avisa a la primera página autenticada que
        // se acaba de iniciar sesión en ESTA pestaña, para que el guard de
        // sesión (ver partials.session-guard) no la cierre por error.
        $request->session()->flash('recien_logueado', true);

        $destino = match (true) {
            Auth::user()->esDirector() => route('director.panel.index'),
            Auth::user()->esStaff() => route('admin.panel.index'),
            default => route('panel.index'),
        };

        return redirect()->intended($destino);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
