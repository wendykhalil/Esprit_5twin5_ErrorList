<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services
    | such as Mailgun, Postmark, AWS and more.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Mirror of config/ollama.php. OLLAMA_BASE_URL wins so the two URL variables stay identical.
    'ollama' => [
        'url' => env('OLLAMA_BASE_URL', env('OLLAMA_URL', 'http://127.0.0.1:11434')),
        'model' => env('OLLAMA_MODEL', 'mistral:latest'),
    ],

];