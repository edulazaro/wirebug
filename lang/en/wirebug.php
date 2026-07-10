<?php

return [
    'title'               => 'Report a problem',
    'modal_title'         => 'Report a problem',
    'tab_label'           => 'Feedback',
    'type_label'          => 'Type',
    'message_label'       => 'What happened?',
    'message_placeholder' => 'Tell us about the problem or your suggestion with as much detail as possible...',
    'steps_label'         => 'Steps to reproduce (optional)',
    'steps_placeholder'   => "1. Go to...\n2. Click...\n3. Then...",
    'screenshot_label'    => 'Screenshot (optional)',
    'screenshot_button'   => 'Attach image',
    'screenshot_remove'   => 'Remove image',
    'recording_label'     => 'Screen recording (optional)',
    'record_button'       => 'Record screen',
    'recording_stop'      => 'Stop',
    'recording_discard'   => 'Discard recording',
    'recording_name'      => 'Screen recording',
    'recording_remove'    => 'Remove recording',
    'email_label'         => 'Email (optional)',
    'email_placeholder'   => 'you@email.com',
    'send'                => 'Send',
    'sending'             => 'Sending...',
    'cancel'              => 'Cancel',
    'close'               => 'Close',
    'success_title'       => 'Thank you!',
    'success_description' => 'We received your message. We will review it as soon as possible.',
    'error'               => 'The message could not be sent. Please try again in a few seconds.',

    /*
    | Label for each type. Keys match those in `config('wirebug.types')`.
    | A project can override a type label in the config; otherwise these
    | are used.
    */
    'types' => [
        'bug'   => 'Bug',
        'idea'  => 'Suggestion',
        'other' => 'Other',
    ],
];
