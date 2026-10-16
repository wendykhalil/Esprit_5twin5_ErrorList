<?php

return [
    'co2_kg_par_kwh' => (float) env('SOLARSHARE_CO2_KG_PAR_KWH', 0.5),
    // Valeurs indicatives à remplacer par une source officielle.
    'ecology_equivalences' => [
        'kg_co2_par_arbre' => 22,
        'kg_co2_par_km' => 0.12,
    ],
];
