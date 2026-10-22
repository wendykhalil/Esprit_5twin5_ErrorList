<?php

return [
    'co2_kg_par_kwh' => (float) env('SOLARSHARE_CO2_KG_PAR_KWH', 0.5),
    // Valeurs indicatives à remplacer par une source officielle.
    'ecology_equivalences' => [
        'kg_co2_par_arbre' => 22,
        'kg_co2_par_km' => 0.12,
    ],
    'weather' => [
        'cities' => [
            'Tunis' => ['latitude' => 36.8065, 'longitude' => 10.1815],
            'Sfax' => ['latitude' => 34.7406, 'longitude' => 10.7603],
            'Sousse' => ['latitude' => 35.8256, 'longitude' => 10.63699],
            'Nabeul' => ['latitude' => 36.4561, 'longitude' => 10.7376],
        ],
        'default_city' => env('SOLARSHARE_DEFAULT_CITY', 'Tunis'),
    ],
    // Mirror of config/ollama.php. Callers use that file through OllamaSettings.
    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', env('OLLAMA_URL', 'http://127.0.0.1:11434')),
        'model' => env('OLLAMA_MODEL', 'mistral:latest'),
        'timeout' => (int) env('OLLAMA_TIMEOUT', 180),
        'keep_alive' => env('OLLAMA_KEEP_ALIVE', '-1'),
        'num_ctx' => (int) env('OLLAMA_NUM_CTX', 1024),
        'num_predict' => (int) env('OLLAMA_NUM_PREDICT', 220),
    ],
];
