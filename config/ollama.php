<?php

/**
 * Single Ollama configuration for every AI feature.
 *
 * OLLAMA_MODEL is the only model name. When it is absent, the model is mistral:latest.
 * On Windows, 127.0.0.1 is the host. Inside Docker Compose the same variables are
 * set to http://ollama:11434, because 127.0.0.1 would be the container itself.
 */

$baseUrl = rtrim((string) env('OLLAMA_BASE_URL', env('OLLAMA_URL', 'http://127.0.0.1:11434')), '/');

return [
    'base_url' => $baseUrl,

    /**
     * Alias of base_url so OLLAMA_URL and OLLAMA_BASE_URL cannot point at two servers.
     */
    'url' => $baseUrl,

    'model' => env('OLLAMA_MODEL', 'mistral:latest'),

    'timeout' => (int) env('OLLAMA_TIMEOUT', 180),

    'keep_alive' => env('OLLAMA_KEEP_ALIVE', '-1'),

    'num_ctx' => (int) env('OLLAMA_NUM_CTX', 1024),

    'num_predict' => (int) env('OLLAMA_NUM_PREDICT', 220),

    'max_retries' => 2,

    'enabled' => env('OLLAMA_ENABLED', true),
];
