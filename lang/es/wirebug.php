<?php

return [
    'title'               => 'Informar de un problema',
    'modal_title'         => 'Informar de un problema',
    'tab_label'           => 'Feedback',
    'type_label'          => 'Tipo',
    'message_label'       => '¿Qué ha pasado?',
    'message_placeholder' => 'Cuéntanos el problema o tu sugerencia con el máximo detalle posible...',
    'steps_label'         => 'Pasos para reproducirlo (opcional)',
    'steps_placeholder'   => "1. Entra en...\n2. Pulsa...\n3. Aparece...",
    'screenshot_label'    => 'Captura de pantalla (opcional)',
    'screenshot_button'   => 'Adjuntar imagen',
    'screenshot_remove'   => 'Quitar imagen',
    'recording_label'     => 'Grabación de pantalla (opcional)',
    'record_button'       => 'Grabar pantalla',
    'recording_stop'      => 'Detener',
    'recording_discard'   => 'Descartar grabación',
    'recording_name'      => 'Grabación de pantalla',
    'recording_remove'    => 'Quitar grabación',
    'email_label'         => 'Email (opcional)',
    'email_placeholder'   => 'tu@email.com',
    'send'                => 'Enviar',
    'sending'             => 'Enviando...',
    'cancel'              => 'Cancelar',
    'close'               => 'Cerrar',
    'success_title'       => '¡Gracias!',
    'success_description' => 'Hemos recibido tu mensaje. Lo revisaremos lo antes posible.',
    'error'               => 'No se pudo enviar el mensaje. Inténtalo de nuevo en unos segundos.',

    /*
    | Label de cada tipo. Las claves coinciden con las de
    | `config('wirebug.types')`. Un proyecto puede sobrescribir el label
    | por tipo en el config; si no, se usan estos.
    */
    'types' => [
        'bug'   => 'Error',
        'idea'  => 'Sugerencia',
        'other' => 'Otro',
    ],
];
