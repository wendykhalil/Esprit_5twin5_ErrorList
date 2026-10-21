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
            ['nom' => 'Premier rayon', 'slug' => 'premier-rayon', 'description' => 'Votre premier geste solaire partagé.', 'image' => 'images/badges/premier-rayon.svg', 'critere' => 'kwh_total', 'seuil' => 1, 'niveau' => 'bronze'],
            ['nom' => 'Éclaireur', 'slug' => 'eclaireur', 'description' => 'Vous avez produit cinq kWh.', 'image' => 'images/badges/eclaireur.svg', 'critere' => 'kwh_total', 'seuil' => 5, 'niveau' => 'bronze'],
            ['nom' => 'Cap solaire', 'slug' => 'cap-solaire', 'description' => 'Votre énergie produite atteint vingt kWh.', 'image' => 'images/badges/eco-engage.svg', 'critere' => 'kwh_total', 'seuil' => 20, 'niveau' => 'argent'],
            ['nom' => 'Énergie durable', 'slug' => 'energie-durable', 'description' => 'Cinquante kWh produits avec constance.', 'image' => 'images/badges/eco-champion.svg', 'critere' => 'kwh_total', 'seuil' => 50, 'niveau' => 'or'],
            ['nom' => 'Geste mesuré', 'slug' => 'geste-mesure', 'description' => 'Un demi-kilo de CO2 évité.', 'image' => 'images/badges/premier-rayon.svg', 'critere' => 'co2_total', 'seuil' => 0.5, 'niveau' => 'bronze'],
            ['nom' => 'Air préservé', 'slug' => 'air-preserve', 'description' => 'Deux kilos et demi de CO2 évités.', 'image' => 'images/badges/eclaireur.svg', 'critere' => 'co2_total', 'seuil' => 2.5, 'niveau' => 'argent'],
            ['nom' => 'Climat engagé', 'slug' => 'climat-engage', 'description' => 'Dix kilos de CO2 évités.', 'image' => 'images/badges/eco-engage.svg', 'critere' => 'co2_total', 'seuil' => 10, 'niveau' => 'argent'],
            ['nom' => 'Bouclier vert', 'slug' => 'bouclier-vert', 'description' => 'Vingt-cinq kilos de CO2 évités.', 'image' => 'images/badges/sauveur-de-planete.svg', 'critere' => 'co2_total', 'seuil' => 25, 'niveau' => 'or'],
            ['nom' => 'Première location', 'slug' => 'premiere-location', 'description' => 'Une location terminée.', 'image' => 'images/badges/premier-rayon.svg', 'critere' => 'reservations_terminees', 'seuil' => 1, 'niveau' => 'bronze'],
            ['nom' => 'Locataire régulier', 'slug' => 'locataire-regulier', 'description' => 'Trois locations terminées.', 'image' => 'images/badges/eco-engage.svg', 'critere' => 'reservations_terminees', 'seuil' => 3, 'niveau' => 'argent'],
            ['nom' => 'Ambassadeur', 'slug' => 'ambassadeur', 'description' => 'Dix locations terminées.', 'image' => 'images/badges/eco-champion.svg', 'critere' => 'reservations_terminees', 'seuil' => 10, 'niveau' => 'or'],
            ['nom' => 'Relevé engagé', 'slug' => 'releve-engage', 'description' => 'Déclarer un relevé d’énergie.', 'image' => 'images/badges/premier-rayon.svg', 'critere' => 'releves_declares', 'seuil' => 1, 'niveau' => 'bronze'],
            ['nom' => 'Retour responsable', 'slug' => 'retour-responsable', 'description' => 'Un retour sans dommage.', 'image' => 'images/badges/locataire-soigneux.svg', 'critere' => 'retours_sans_dommage', 'seuil' => 1, 'niveau' => 'bronze'],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['slug' => $badge['slug']], $badge + ['actif' => true]);
        }

        $service = app(BadgeAttributionService::class);
        User::query()->each(fn (User $user) => $service->evaluer($user));
    }
}
