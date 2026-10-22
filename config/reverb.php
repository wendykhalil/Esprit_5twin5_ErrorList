<?php

return [

    'servers' => [
        'reverb' => [
            'host' => env('REVERB_SERVER_HOST', '0.0.0.0'),
            'port' => env('REVERB_SERVER_PORT', 8081),
            'hostname' => env('REVERB_HOST'),
            'options' => [
                'tls' => [],
            ],
            'max_request_size' => env('REVERB_MAX_REQUEST_SIZE', 10_000),
            'scaling' => [
                'enabled' => env('REVERB_SCALING_ENABLED', false),
                'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
                'server' => [
                    'url' => env('REDIS_URL'),
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'port' => env('REDIS_PORT', '6379'),
                    'username' => env('REDIS_USERNAME'),
                    'password' => env('REDIS_PASSWORD'),
                    'database' => env('REDIS_DB', '0'),
                    'timeout' => env('REDIS_TIMEOUT', 60),
                ],
            ],
            'pulse_ingest_interval' => env('REVERB_PULSE_INGEST_INTERVAL', 15),
            'telescope_ingest_interval' => env('REVERB_TELESCOPE_INGEST_INTERVAL', 15),
        ],
    ],

    'apps' => [

        'provider' => 'config',

        'apps' => [
            [
                'key' => env('REVERB_APP_KEY'),
                'secret' => env('REVERB_APP_SECRET'),
                'app_id' => env('REVERB_APP_ID'),
                'options' => [
                    'host' => env('REVERB_HOST', 'localhost'),
                    'port' => env('REVERB_PORT', 8081),
                    'scheme' => env('REVERB_SCHEME', 'http'),
                ],
                'allowed_origins' => ['*'],
                'ping_interval' => 30,
                'activity_timeout' => 30,
                'max_connections' => null,
                'max_message_size' => 10000,
                'accept_client_events_from' => 'all',
                'rate_limiting' => null,
            ],
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
