<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Mail\ClaveInicialMail;
use App\Models\EstadoUsuario;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    private const ROLES_STAFF = ['Secretario', 'Secretario Administrativo', 'Director'];

    public function index(): View
    {
        $usuarios = Usuario::with(['persona', 'rol', 'estadoUsuario'])
            ->whereHas('rol', fn ($q) => $q->whereIn('nombre_rol', self::ROLES_STAFF))
            ->get()
            ->sortBy([
                fn ($u) => $u->rol->nombre_rol,
                fn ($u) => $u->persona->apellido,
            ]);

        return view('director.usuarios.index', ['usuarios' => $usuarios]);
    }

    public function create(): View
    {
        $roles = Rol::whereIn('nombre_rol', self::ROLES_STAFF)->orderBy('nombre_rol')->get();

        return view('director.usuarios.create', ['roles' => $roles]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rolesPermitidos = Rol::whereIn('nombre_rol', self::ROLES_STAFF)->pluck('id_rol');

        $data = $request->validate([
            'dni' => ['required', 'digits:8', Rule::unique('persona', 'dni')],
            'apellido' => ['required', 'string', 'max:50', 'regex:/^[\pL\s\'-]+$/u'],
            'nombre' => ['required', 'string', 'max:50', 'regex:/^[\pL\s\'-]+$/u'],
            'email' => ['required', 'email', 'max:100', Rule::unique('usuario', 'email')],
            'id_rol' => ['required', Rule::in($rolesPermitidos)],
        ]);

        $persona = Persona::create([
            'dni' => $data['dni'],
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
        ]);

        $clave = $this->generarClave($data['dni']);

        Usuario::create([
            'id_persona' => $persona->id_persona,
            'email' => $data['email'],
            'password' => Hash::make($clave),
            'debe_cambiar_clave' => true,
            'id_rol' => $data['id_rol'],
            'id_estado' => EstadoUsuario::where('nombre_estado', 'Activo')->value('id_estado'),
        ]);

        // Si el correo falla (ej. servidor de mail caído) el usuario igual queda
        // registrado: dirección ve la clave en pantalla y se la entrega a mano.
        try {
            Mail::to($data['email'])->send(new ClaveInicialMail($persona->nombre, $data['email'], $clave));
            $aviso = "Se envió la clave inicial a {$data['email']}.";
        } catch (\Throwable $e) {
            report($e);
            $aviso = 'No se pudo enviar el mail: entregale la clave a la persona.';
        }

        return redirect()->route('director.usuarios.index')
            ->with('status', "Usuario registrado correctamente. Clave inicial: {$clave}. {$aviso}");
    }

    public function edit(Usuario $usuario): View
    {
        abort_unless($usuario->rol && in_array($usuario->rol->nombre_rol, self::ROLES_STAFF, true), 404);

        $roles = Rol::whereIn('nombre_rol', self::ROLES_STAFF)->orderBy('nombre_rol')->get();

        return view('director.usuarios.edit', ['usuario' => $usuario, 'roles' => $roles]);
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        $rolesPermitidos = Rol::whereIn('nombre_rol', self::ROLES_STAFF)->pluck('id_rol');

        $data = $request->validate([
            'dni' => ['required', 'digits:8', Rule::unique('persona', 'dni')->ignore($usuario->id_persona, 'id_persona')],
            'apellido' => ['required', 'string', 'max:50', 'regex:/^[\pL\s\'-]+$/u'],
            'nombre' => ['required', 'string', 'max:50', 'regex:/^[\pL\s\'-]+$/u'],
            'email' => ['required', 'email', 'max:100', Rule::unique('usuario', 'email')->ignore($usuario->id_usuario, 'id_usuario')],
            'id_rol' => ['required', Rule::in($rolesPermitidos)],
        ]);

        $usuario->persona->update([
            'dni' => $data['dni'],
            'apellido' => $data['apellido'],
            'nombre' => $data['nombre'],
        ]);

        $usuario->update([
            'email' => $data['email'],
            'id_rol' => $data['id_rol'],
        ]);

        return redirect()->route('director.usuarios.index')->with('status', 'Usuario actualizado correctamente.');
    }

    public function baja(Usuario $usuario): RedirectResponse
    {
        abort_if($usuario->id_usuario === Auth::user()->id_usuario, 403, 'No podés darte de baja a vos mismo.');

        $usuario->update(['id_estado' => EstadoUsuario::where('nombre_estado', 'Inactivo')->value('id_estado')]);

        return redirect()->route('director.usuarios.index')->with('status', 'Usuario dado de baja: ya no va a poder iniciar sesión.');
    }

    public function reactivar(Usuario $usuario): RedirectResponse
    {
        $usuario->update(['id_estado' => EstadoUsuario::where('nombre_estado', 'Activo')->value('id_estado')]);

        return redirect()->route('director.usuarios.index')->with('status', 'Usuario reactivado.');
    }

    public function reenviarClave(Usuario $usuario): RedirectResponse
    {
        $clave = $this->generarClave($usuario->dni);

        $usuario->update([
            'password' => Hash::make($clave),
            'debe_cambiar_clave' => true,
        ]);

        try {
            Mail::to($usuario->email)->send(new ClaveInicialMail($usuario->nombre, $usuario->email, $clave));
            $aviso = "Se envió la clave nueva a {$usuario->email}.";
        } catch (\Throwable $e) {
            report($e);
            $aviso = 'No se pudo enviar el mail: entregale la clave a la persona.';
        }

        return redirect()->route('director.usuarios.index')
            ->with('status', "Clave regenerada. Clave nueva: {$clave}. {$aviso}");
    }

    private function generarClave(string $dni): string
    {
        $ultimosCuatro = substr($dni, -4);
        $codigo = strtoupper(Str::random(8));

        return "ISG-{$ultimosCuatro}-{$codigo}";
    }
}
