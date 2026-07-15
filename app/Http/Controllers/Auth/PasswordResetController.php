<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    private const CODIGO_VIGENCIA_MINUTOS = 15;

    public function solicitarForm(): View
    {
        return view('auth.recuperar-email');
    }

    public function solicitar(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $usuario = User::where('email', $request->email)->first();

        if (! $usuario) {
            return back()->withErrors(['email' => 'No encontramos ninguna cuenta con ese email institucional.']);
        }

        $codigo = (string) random_int(100000, 999999);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $usuario->email],
            ['token' => Hash::make($codigo), 'created_at' => now()]
        );

        // No hay servidor de correo configurado en este proyecto de práctica:
        // el código se registra en el log en vez de enviarse por email real.
        Log::info("Código de recuperación para {$usuario->email}: {$codigo}");

        session([
            'password_reset_email' => $usuario->email,
            'password_reset_codigo_demo' => $codigo,
        ]);

        return redirect()->route('password.verificar');
    }

    public function verificarForm(): View|RedirectResponse
    {
        if (! session('password_reset_email')) {
            return redirect()->route('password.solicitar');
        }

        return view('auth.recuperar-verificar', [
            'email' => session('password_reset_email'),
            'codigoDemo' => session('password_reset_codigo_demo'),
        ]);
    }

    public function verificar(Request $request): RedirectResponse
    {
        $request->validate(['codigo' => ['required', 'digits:6']]);

        $email = session('password_reset_email');
        abort_unless($email, 419);

        $registro = DB::table('password_resets')->where('email', $email)->first();

        $vencido = ! $registro || now()->diffInMinutes($registro->created_at) > self::CODIGO_VIGENCIA_MINUTOS;

        if ($vencido || ! Hash::check($request->codigo, $registro->token)) {
            return back()->withErrors(['codigo' => 'El código ingresado no es válido o expiró.']);
        }

        session(['password_reset_verificado' => true]);

        return redirect()->route('password.nueva');
    }

    public function nuevaForm(): View|RedirectResponse
    {
        if (! session('password_reset_verificado')) {
            return redirect()->route('password.solicitar');
        }

        return view('auth.recuperar-clave');
    }

    public function nueva(Request $request): RedirectResponse
    {
        abort_unless(session('password_reset_verificado'), 419);

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = session('password_reset_email');
        $usuario = User::where('email', $email)->firstOrFail();
        $usuario->update(['password' => Hash::make($request->password)]);

        DB::table('password_resets')->where('email', $email)->delete();
        session()->forget(['password_reset_email', 'password_reset_codigo_demo', 'password_reset_verificado']);

        return redirect()->route('login')->with('status', 'Tu contraseña se actualizó correctamente. Ya podés iniciar sesión.');
    }
}
