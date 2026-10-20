<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionInstitucion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Cambio obligatorio de la clave inicial en el primer ingreso (ver EnsureClaveCambiada). */
class CambioClaveController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        if (! $request->user()->debe_cambiar_clave) {
            return redirect($request->user()->rutaInicio());
        }

        return view('auth.cambiar-clave', ['config' => ConfiguracionInstitucion::first()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        $request->validate([
            'password_actual' => ['required', 'string', 'current_password'],
            'password' => ['required', 'confirmed', 'different:password_actual', Password::min(8)->letters()->numbers()],
        ], [
            'password_actual.current_password' => 'La clave actual no es correcta.',
            'password.different' => 'La contraseña nueva tiene que ser distinta de la clave actual.',
        ]);

        $usuario->update([
            'password' => Hash::make($request->password),
            'debe_cambiar_clave' => false,
        ]);

        $request->session()->flash('recien_logueado', true);

        return redirect($usuario->rutaInicio())->with('status', 'Tu contraseña se actualizó correctamente.');
    }
}
