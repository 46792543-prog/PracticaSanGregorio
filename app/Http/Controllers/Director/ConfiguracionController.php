<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\Carrera;
use App\Models\ConfiguracionInstitucion;
use App\Models\ImagenLanding;
use App\Models\InscripcionCarrera;
use App\Models\Profesor;
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
            'imagenesLanding' => ImagenLanding::orderBy('orden')->orderBy('id_imagen')->get(),
            'carrera' => Carrera::orderBy('id_carrera')->first(),
            'docentesActivos' => Profesor::whereNull('fecha_baja')->count(),
            'alumnosActivos' => InscripcionCarrera::whereHas('estadoInscripcion', fn ($q) => $q->where('nombre_estado', 'Activo'))
                ->distinct('id_persona_alumno')->count('id_persona_alumno'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_institucion' => ['required', 'string', 'max:40', 'regex:/^[\pL\s\'-]+$/u'],
            'direccion' => ['nullable', 'string', 'max:100', 'regex:/^[\pL0-9\s,.#\'-]+$/u'],
            'nombre_director' => ['nullable', 'string', 'max:20', 'regex:/^[\pL\s\'-]+$/u'],
            'telefono_contacto' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'email_contacto' => ['nullable', 'email', 'max:40'],
            'horario_atencion' => ['nullable', 'string', 'max:60'],
            'landing_anios_formacion' => ['nullable', 'integer', 'min:0', 'max:999'],
            'landing_docentes_cantidad' => ['nullable', 'integer', 'min:0', 'max:999'],
            'landing_alumnos_cantidad' => ['nullable', 'integer', 'min:0', 'max:999'],
            'landing_egresados_cantidad' => ['nullable', 'integer', 'min:0', 'max:999'],
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

    public function storeImagenLanding(Request $request): RedirectResponse
    {
        $request->validate([
            'imagenes' => ['required', 'array', 'min:1'],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $orden = (int) ImagenLanding::max('orden');

        foreach ($request->file('imagenes') as $archivo) {
            ImagenLanding::create([
                'path' => $archivo->store('landing', 'public'),
                'orden' => ++$orden,
            ]);
        }

        return redirect()->route('director.configuracion.index')
            ->with('status', 'Se agregaron las imágenes a la portada de la landing.');
    }

    public function destroyImagenLanding(ImagenLanding $imagen): RedirectResponse
    {
        Storage::disk('public')->delete($imagen->path);
        $imagen->delete();

        return redirect()->route('director.configuracion.index')
            ->with('status', 'Se eliminó la imagen de la portada.');
    }
}
