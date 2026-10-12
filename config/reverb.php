<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Reverb Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Reverb server. This configuration is used
    | when you run the `reverb:start` command to start the WebSocket server.
    |
    */

    'apps' => [
        [
            'key' => env('REVERB_APP_KEY', 'your-app-key'),
            'secret' => env('REVERB_APP_SECRET', 'your-app-secret'),
            'app_id' => env('REVERB_APP_ID', 'app-id'),
            'options' => [
                'host' => env('REVERB_HOST', 'localhost'),
                'port' => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),
            ],
            'capacity' => null,
            'encryption' => true,
        ],
    ],

    'ssl' => [
        'certPath' => env('REVERB_SSL_CERT_PATH', null),
        'keyPath' => env('REVERB_SSL_KEY_PATH', null),
        'passPhrase' => env('REVERB_SSL_PASSPHRASE', null),
    ],

    'masquerade' => [
        //
    ],

    'metrics' => [
        'enabled' => env('REVERB_METRICS_ENABLED', true),
    ],

];
