<?php

namespace App\Providers;

use App\Support\Auditor;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Auditoría global: escucha el create/update/delete de CUALQUIER
        // modelo del sistema, sin que cada controlador tenga que registrar
        // el evento a mano (ver App\Support\Auditor).
        Event::listen('eloquent.created: *', fn (string $event, array $models) => Auditor::registrar('creado', $models[0]));
        Event::listen('eloquent.updated: *', fn (string $event, array $models) => Auditor::registrar('actualizado', $models[0]));
        Event::listen('eloquent.deleted: *', fn (string $event, array $models) => Auditor::registrar('eliminado', $models[0]));

        // Fuerza HTTPS en cualquier entorno que no sea local (una vez que haya
        // hosting con certificado SSL, todo el tráfico — incluidas las
        // contraseñas de los formularios — va a viajar cifrado). No afecta
        // nada corriendo en localhost.
        if (! $this->app->environment('local')) {
            URL::forceScheme('https');
        }
    }
}
