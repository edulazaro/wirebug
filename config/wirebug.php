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
    | Captura de pantalla adjunta
    |--------------------------------------------------------------------------
    | La imagen se guarda vía Storage en el disco indicado (cualquiera de
    | config/filesystems.php de la app: 'local', 's3', un disco R2...). La
    | fila del reporte almacena solo la ruta. 'max_kb' limita el tamaño.
    */
    'uploads' => [
        'disk'   => 'local',
        'path'   => 'wirebug',
        'max_kb' => 5120,
    ],

    /*
    |--------------------------------------------------------------------------
    | Grabación de pantalla
    |--------------------------------------------------------------------------
    | Graba con getDisplayMedia + MediaRecorder (nativo del navegador, cero
    | coste hasta que el usuario pulsa grabar). El modal se cierra durante la
    | grabación y queda un overlay con contador y botón de detener; al parar,
    | el vídeo (WebM/MP4) se adjunta al reporte y va al mismo disco que la
    | captura. El botón solo aparece en navegadores compatibles (desktop).
    */
    'recording' => [
        'enabled'     => true,
        'max_seconds' => 90,
        'max_kb'      => 51200,
        // Bitrate de vídeo (bits/segundo) pasado al MediaRecorder. El vídeo
        // ya sale comprimido del navegador (VP9/WebM); esto capa su calidad:
        // 2 Mbps sobra para leer una UI y deja 90s en ~22MB como máximo.
        'bitrate'     => 2000000,
    ],

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
