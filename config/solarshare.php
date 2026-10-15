<?php

return [
    'co2_kg_par_kwh' => (float) env('SOLARSHARE_CO2_KG_PAR_KWH', 0.5),
    // valeur indicative à remplacer par une source officielle.
    'badges' => [
        ['nom' => 'Éco-débutant', 'niveau' => 'bronze', 'seuil_kwh' => 10],
        ['nom' => 'Éco-engagé', 'niveau' => 'argent', 'seuil_kwh' => 50],
        ['nom' => 'Éco-champion', 'niveau' => 'or', 'seuil_kwh' => 100],
    ],
];
