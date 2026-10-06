<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FichaCampo;
use App\Models\FichaSeccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FichaInscripcionController extends Controller
{
    private const TIPOS = ['texto', 'texto_largo', 'numero', 'fecha', 'seleccion', 'si_no'];

    public function index(): View
    {
        $secciones = FichaSeccion::with('campos')->orderBy('orden')->get();

        return view('admin.ficha-inscripcion.index', [
            'secciones' => $secciones,
            'tipos' => self::TIPOS,
        ]);
    }

    public function storeSeccion(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('ficha_seccion', 'nombre')],
        ]);

        $siguienteOrden = (int) (FichaSeccion::max('orden') ?? 0) + 1;

        FichaSeccion::create(['nombre' => $data['nombre'], 'orden' => $siguienteOrden]);

        return back()->with('status', 'Se agregó la sección correctamente.');
    }

    public function updateSeccion(Request $request, FichaSeccion $seccion): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('ficha_seccion', 'nombre')->ignore($seccion->id_seccion, 'id_seccion')],
        ]);

        $seccion->update($data);

        return back()->with('status', 'Se actualizó la sección correctamente.');
    }

    public function destroySeccion(FichaSeccion $seccion): RedirectResponse
    {
        if ($seccion->campos()->exists()) {
            return back()->withErrors(['seccion' => 'No se puede eliminar una sección que todavía tiene campos. Eliminá o mové sus campos primero.']);
        }

        $seccion->delete();

        return back()->with('status', 'Se eliminó la sección correctamente.');
    }

    public function moverSeccion(Request $request, FichaSeccion $seccion): RedirectResponse
    {
        $data = $request->validate(['direccion' => ['required', 'in:arriba,abajo']]);

        $vecina = $data['direccion'] === 'arriba'
            ? FichaSeccion::where('orden', '<', $seccion->orden)->orderByDesc('orden')->first()
            : FichaSeccion::where('orden', '>', $seccion->orden)->orderBy('orden')->first();

        if ($vecina) {
            [$ordenSeccion, $ordenVecina] = [$seccion->orden, $vecina->orden];
            $seccion->update(['orden' => $ordenVecina]);
            $vecina->update(['orden' => $ordenSeccion]);
        }

        return back();
    }

    public function storeCampo(Request $request): RedirectResponse
    {
        $data = $this->validarCampo($request);

        $siguienteOrden = (int) (FichaCampo::where('id_seccion', $data['id_seccion'])->max('orden') ?? 0) + 1;

        FichaCampo::create([...$data, 'orden' => $siguienteOrden]);

        return back()->with('status', 'Se agregó el campo correctamente.');
    }

    public function updateCampo(Request $request, FichaCampo $campo): RedirectResponse
    {
        $campo->update($this->validarCampo($request));

        return back()->with('status', 'Se actualizó el campo correctamente.');
    }

    public function toggleCampo(FichaCampo $campo): RedirectResponse
    {
        $campo->update(['activo' => ! $campo->activo]);

        return back()->with('status', $campo->activo ? 'El campo vuelve a estar visible.' : 'Se ocultó el campo.');
    }

    public function destroyCampo(FichaCampo $campo): RedirectResponse
    {
        $campo->delete();

        return back()->with('status', 'Se eliminó el campo y las respuestas que tenía cargadas.');
    }

    public function moverCampo(Request $request, FichaCampo $campo): RedirectResponse
    {
        $data = $request->validate(['direccion' => ['required', 'in:arriba,abajo']]);

        $vecino = $data['direccion'] === 'arriba'
            ? FichaCampo::where('id_seccion', $campo->id_seccion)->where('orden', '<', $campo->orden)->orderByDesc('orden')->first()
            : FichaCampo::where('id_seccion', $campo->id_seccion)->where('orden', '>', $campo->orden)->orderBy('orden')->first();

        if ($vecino) {
            [$ordenCampo, $ordenVecino] = [$campo->orden, $vecino->orden];
            $campo->update(['orden' => $ordenVecino]);
            $vecino->update(['orden' => $ordenCampo]);
        }

        return back();
    }

    private function validarCampo(Request $request): array
    {
        $data = $request->validate([
            'id_seccion' => ['required', 'exists:ficha_seccion,id_seccion'],
            'etiqueta' => ['required', 'string', 'max:150'],
            'tipo' => ['required', Rule::in(self::TIPOS)],
            'opciones' => ['required_if:tipo,seleccion', 'nullable', 'string'],
            'obligatorio' => ['nullable', 'boolean'],
        ]);

        $data['obligatorio'] = $request->boolean('obligatorio');

        if ($data['tipo'] === 'seleccion') {
            $data['opciones'] = collect(explode("\n", $data['opciones']))
                ->map(fn ($l) => trim($l))
                ->filter()
                ->values()
                ->all();
        } else {
            $data['opciones'] = null;
        }

        return $data;
    }
}
