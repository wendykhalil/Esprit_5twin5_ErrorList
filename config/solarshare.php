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
    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.2:3b'),
        'timeout' => (int) env('OLLAMA_TIMEOUT', 20),
    ],
];
