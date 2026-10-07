<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Panneaux solaires',
                'description' => 'Panneaux solaires portables et photovoltaïques.',
            ],
            [
                'name' => 'Batteries',
                'description' => 'Batteries portables et solutions de stockage énergétique.',
            ],
            [
                'name' => 'Stations électriques',
                'description' => 'Stations électriques portables pour le stockage et la distribution d’énergie.',
            ],
            [
                'name' => 'Équipements éoliens',
                'description' => 'Petites éoliennes et équipements liés à l’énergie du vent.',
            ],
            [
                'name' => 'Chargeurs solaires',
                'description' => 'Chargeurs et accessoires fonctionnant à l’énergie solaire.',
            ],
            [
                'name' => 'Autres équipements',
                'description' => 'Autres équipements liés aux énergies renouvelables.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}