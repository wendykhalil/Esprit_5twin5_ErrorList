<?php

/**
 * Ollama AI Configuration
 * 
 * Local Qwen2.5 3B model for incident description generation
 */

return [
    /**
     * Base URL for Ollama API
     * Default: http://127.0.0.1:11434 (local Ollama server)
     */
    'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),

    /**
     * Model name to use for generation
     * Default: qwen2.5:3b
     */
    'model' => env('OLLAMA_MODEL', 'qwen2.5:3b'),

    /**
     * Request timeout in seconds
     * Default: 60 seconds
     */
    'timeout' => (int) env('OLLAMA_TIMEOUT', 60),

    /**
     * Maximum number of retries for failed requests
     */
    'max_retries' => 2,

    /**
     * Enable/disable Ollama feature
     */
    'enabled' => env('OLLAMA_ENABLED', true),
];
