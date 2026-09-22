<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\ConfiguracionInstitucion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    public function index(): View
    {
        return view('director.configuracion.index', [
            'configuracion' => ConfiguracionInstitucion::first(),
            'aniosLectivos' => AnioLectivo::with('estadoAnio')->orderByDesc('anio')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_institucion' => ['required', 'string', 'max:40', 'regex:/^[\pL\s\'-]+$/u'],
            'direccion' => ['nullable', 'string', 'max:20', 'regex:/^[\pL0-9\s]+$/u'],
            'nombre_director' => ['nullable', 'string', 'max:20', 'regex:/^[\pL\s\'-]+$/u'],
            'telefono_contacto' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'email_contacto' => ['nullable', 'email', 'max:40'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $configuracion = ConfiguracionInstitucion::first() ?? new ConfiguracionInstitucion();
        $configuracion->fill(collect($data)->except('logo')->all());

        if ($request->hasFile('logo')) {
            if ($configuracion->logo_path) {
                Storage::disk('public')->delete($configuracion->logo_path);
            }
            $configuracion->logo_path = $request->file('logo')->store('institucion', 'public');
        }

        $configuracion->fecha_ultima_modificacion = now();
        $configuracion->id_secretario_modifica = Auth::user()->id_persona;
        $configuracion->save();

        return redirect()->route('director.configuracion.index')
            ->with('status', 'Los datos institucionales se actualizaron correctamente.');
    }

    public function destroy(): RedirectResponse
    {
        $configuracion = ConfiguracionInstitucion::first();
        if ($configuracion?->logo_path) {
            Storage::disk('public')->delete($configuracion->logo_path);
        }

        ConfiguracionInstitucion::query()->delete();

        return redirect()->route('director.configuracion.index')
            ->with('status', 'Se eliminaron los datos institucionales.');
    }
}
