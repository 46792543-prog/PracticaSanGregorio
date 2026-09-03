<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionInstitucion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    public function index(): View
    {
        return view('director.configuracion.index', [
            'configuracion' => ConfiguracionInstitucion::first(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_institucion' => ['required', 'string', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'nombre_director' => ['nullable', 'string', 'max:150'],
            'telefono_contacto' => ['nullable', 'string', 'max:20'],
            'email_contacto' => ['nullable', 'email', 'max:100'],
        ]);

        $configuracion = ConfiguracionInstitucion::first() ?? new ConfiguracionInstitucion();
        $configuracion->fill($data);
        $configuracion->fecha_ultima_modificacion = now();
        $configuracion->id_secretario_modifica = Auth::user()->id_persona;
        $configuracion->save();

        return redirect()->route('director.configuracion.index')
            ->with('status', 'Los datos institucionales se actualizaron correctamente.');
    }
}
