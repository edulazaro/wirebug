<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ruta de envío
    |--------------------------------------------------------------------------
    | Endpoint POST que recibe los reportes. El middleware por defecto incluye
    | throttle para evitar abuso. Si tu widget solo vive detrás de login,
    | añade 'auth' al middleware.
    */
    'route' => [
        'enabled'    => true,
        'path'       => 'wirebug',
        'middleware' => ['web', 'throttle:10,1'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Posición del botón flotante
    |--------------------------------------------------------------------------
    | 'left' o 'right' (siempre abajo). Si conviven wirebug y wirecookies en
    | la misma página, pon cada uno en una esquina.
    */
    'position' => 'left',

    /*
    |--------------------------------------------------------------------------
    | Tipos de reporte
    |--------------------------------------------------------------------------
    | Cada entrada define una opción del selector. El TEXTO (label) sale de
    | las traducciones: lang/{locale}/wirebug.php → 'types.{clave}'. Puedes
    | sobrescribirlo añadiendo aquí 'label' (prioridad sobre la traducción).
    | El primero es el seleccionado por defecto.
    */
    'types' => [
        'bug'   => [],
        'idea'  => [],
        'other' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Email de contacto
    |--------------------------------------------------------------------------
    | Si el visitante NO está autenticado, se le muestra un campo de email
    | opcional para poder responderle. Con usuario autenticado el campo no
    | aparece (ya sabemos quién es). Pon false para no pedirlo nunca.
    */
    'ask_guest_email' => true,

    /*
    |--------------------------------------------------------------------------
    | Contexto capturado automáticamente
    |--------------------------------------------------------------------------
    | Además del mensaje, cada reporte guarda contexto técnico sin que el
    | usuario escriba nada: URL actual, user agent, viewport y locale.
    | Desactívalo si no lo quieres almacenar.
    */
    'capture_context' => true,

    /*
    |--------------------------------------------------------------------------
    | Tabla
    |--------------------------------------------------------------------------
    */
    'table' => 'wirebug_reports',
];
