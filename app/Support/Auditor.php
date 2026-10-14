<?php

namespace App\Support;

use App\Models\Auditoria;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Escucha (vía eventos globales de Eloquent, ver AppServiceProvider) todo
 * create/update/delete de cualquier modelo del sistema y lo deja asentado
 * en la tabla auditoria — sin que cada controlador tenga que avisarle a
 * mano. Solo queda registrado lo que hacen secretaría/dirección: si quien
 * está logueado es un alumno, o no hay nadie logueado (seeders, consola),
 * no se audita.
 */
class Auditor
{
    private const CAMPOS_OCULTOS = ['password', 'remember_token'];
    private const CAMPOS_IGNORADOS = ['created_at', 'updated_at'];

    public static function registrar(string $accion, Model $model): void
    {
        if ($model instanceof Auditoria) {
            return;
        }

        $usuario = Auth::user();

        if (! $usuario instanceof Usuario || ! $usuario->esStaff()) {
            return;
        }

        $cambios = match ($accion) {
            'creado' => self::limpiar($model->getAttributes()),
            'actualizado' => self::diff($model),
            'eliminado' => self::limpiar($model->getOriginal()),
            default => [],
        };

        if ($accion === 'actualizado' && empty($cambios)) {
            return;
        }

        Auditoria::create([
            'id_usuario' => $usuario->id_usuario,
            'accion' => $accion,
            'tabla' => $model->getTable(),
            'id_registro' => $model->getKey(),
            'descripcion' => self::describir($accion, $model),
            'cambios' => $cambios,
        ]);
    }

    private static function diff(Model $model): array
    {
        $cambios = [];

        foreach ($model->getChanges() as $campo => $nuevo) {
            if (in_array($campo, self::CAMPOS_IGNORADOS, true)) {
                continue;
            }

            $oculto = in_array($campo, self::CAMPOS_OCULTOS, true);

            $cambios[$campo] = [
                'antes' => $oculto ? '[protegido]' : $model->getOriginal($campo),
                'despues' => $oculto ? '[protegido]' : $nuevo,
            ];
        }

        return $cambios;
    }

    private static function limpiar(array $atributos): array
    {
        foreach (self::CAMPOS_OCULTOS as $campo) {
            if (array_key_exists($campo, $atributos)) {
                $atributos[$campo] = '[protegido]';
            }
        }

        unset($atributos['created_at'], $atributos['updated_at']);

        return $atributos;
    }

    private static function describir(string $accion, Model $model): string
    {
        $verbo = [
            'creado' => 'Creó',
            'actualizado' => 'Actualizó',
            'eliminado' => 'Eliminó',
        ][$accion];

        return "{$verbo} un registro en \"{$model->getTable()}\" (#{$model->getKey()})";
    }
}
