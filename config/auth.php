<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuraciones predeterminadas de autenticación
    |--------------------------------------------------------------------------
    || Esta opción controla el "guardia" de autenticación predeterminado y 
    las opciones de restablecimiento de contraseña de tu aplicación. 
    Puedes cambiar estos valores predeterminados según sea necesario,
    pero son un punto de partida perfecto para la mayoría de las aplicaciones.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Guardias de autenticación
    |--------------------------------------------------------------------------
    |
    | A continuación, puedes definir cada protector de autenticación para tu aplicación.
    | Por supuesto, se ha definido una configuración predeterminada excelente para ti
    | aquí que utiliza almacenamiento en sesión y el proveedor de usuarios Eloquent.
    |
    | Todos los controladores de autenticación tienen un proveedor de usuarios. Esto define cómo
    | se recuperan realmente los usuarios de tu base de datos u otros mecanismos de almacenamiento
    | utilizados por esta aplicación para conservar los datos de tus usuarios.
    |
    | Compatible: "sesión"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Todos los controladores de autenticación tienen un proveedor de usuarios.
     Esto define cómo se obtienen realmente los usuarios de tu base de datos u 
     otros mecanismos de almacenamiento que esta aplicación utiliza para guardar los datos de tus usuarios.

    Si tienes varias tablas o modelos de usuarios, 
    puedes configurar múltiples fuentes que representen cada
     modelo o tabla. Estas fuentes luego se pueden asignar a cualquier
      guardia de autenticación adicional que hayas definido.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\Usuario::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Restablecer contraseñas
    |--------------------------------------------------------------------------
    |
    | Puedes especificar varias configuraciones de restablecimiento de contraseña
    si tienes más de una tabla o modelo de usuario en la aplicación y quieres tener ajustes de restablecimiento de contraseña
    separados según los tipos específicos de usuarios. 

El tiempo de expiración es el número de minutos que cada token de restablecimiento será considerado válido. 
Esta función de seguridad mantiene los tokens de corta duración para que tengan menos tiempo de ser adivinados.
Puedes cambiar esto según sea necesario.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tiempo de espera de confirmación de contraseña
    |--------------------------------------------------------------------------
    |
    | Aquí puedes definir la cantidad de segundos antes de que la confirmación de contraseña caduque y se le pida al usuario que 
    vuelva a ingresar su contraseña en la pantalla de confirmación. 
    Por defecto, el tiempo de espera dura tres horas.
    */

    'password_timeout' => 10800,

];
