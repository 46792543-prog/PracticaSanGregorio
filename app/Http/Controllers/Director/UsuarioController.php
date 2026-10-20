<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\EstadoUsuario;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'id_rol' => $data['id_rol'],
            'id_estado' => EstadoUsuario::where('nombre_estado', 'Activo')->value('id_estado'),
        ]);

        return redirect()->route('director.usuarios.index')
            ->with('status', "Usuario registrado correctamente. Clave inicial: {$clave}");
    }

    private function generarClave(string $dni): string
    {
        $ultimosCuatro = substr($dni, -4);
        $codigo = strtoupper(Str::random(8));

        return "ISG-{$ultimosCuatro}-{$codigo}";
    }
}
