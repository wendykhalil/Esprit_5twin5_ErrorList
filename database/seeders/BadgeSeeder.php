<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\User;
use App\Services\BadgeAttributionService;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['nom' => 'Premier rayon', 'slug' => 'premier-rayon', 'description' => 'Votre premier geste solaire partagé.', 'image' => 'images/badges/premier-rayon.svg', 'critere' => 'kwh_total', 'seuil' => 10, 'niveau' => 'bronze'],
            ['nom' => 'Éclaireur', 'slug' => 'eclaireur', 'description' => 'Vous avez accumulé une belle réserve d’énergie.', 'image' => 'images/badges/eclaireur.svg', 'critere' => 'kwh_total', 'seuil' => 50, 'niveau' => 'bronze'],
            ['nom' => 'Éco-engagé', 'slug' => 'eco-engage', 'description' => 'Votre engagement se mesure dans la durée.', 'image' => 'images/badges/eco-engage.svg', 'critere' => 'reservations_terminees', 'seuil' => 3, 'niveau' => 'argent'],
            ['nom' => 'Sauveur de planète', 'slug' => 'sauveur-de-planete', 'description' => 'Une contribution CO2 qui compte.', 'image' => 'images/badges/sauveur-de-planete.svg', 'critere' => 'co2_total', 'seuil' => 25, 'niveau' => 'argent'],
            ['nom' => 'Locataire soigneux', 'slug' => 'locataire-soigneux', 'description' => 'Vos retours préservent les équipements.', 'image' => 'images/badges/locataire-soigneux.svg', 'critere' => 'retours_sans_dommage', 'seuil' => 3, 'niveau' => 'or'],
            ['nom' => 'Éco-champion', 'slug' => 'eco-champion', 'description' => 'Une énergie partagée avec constance.', 'image' => 'images/badges/eco-champion.svg', 'critere' => 'releves_declares', 'seuil' => 5, 'niveau' => 'or'],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['slug' => $badge['slug']], $badge + ['actif' => true]);
        }

        $service = app(BadgeAttributionService::class);
        User::query()->each(fn (User $user) => $service->evaluer($user));
    }
}
