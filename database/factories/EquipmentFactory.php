<?php

namespace Database\Factories;

use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    public function definition(): array
    {
        $items = [
            [
                'name' => 'Panneau solaire portable 200W',
                'brand' => 'EcoFlow',
                'power' => 200,
                'capacity' => null,
            ],
            [
                'name' => 'Batterie portable 1000Wh',
                'brand' => 'Bluetti',
                'power' => null,
                'capacity' => 1000,
            ],
            [
                'name' => 'Station électrique portable',
                'brand' => 'Jackery',
                'power' => 1000,
                'capacity' => 1000,
            ],
            [
                'name' => 'Panneau solaire pliable 120W',
                'brand' => 'Anker',
                'power' => 120,
                'capacity' => null,
            ],
            [
                'name' => 'Batterie solaire 500Wh',
                'brand' => 'EcoFlow',
                'power' => null,
                'capacity' => 500,
            ],
            [
                'name' => 'Chargeur solaire portable',
                'brand' => 'BigBlue',
                'power' => 28,
                'capacity' => null,
            ],
            [
                'name' => 'Mini éolienne portable',
                'brand' => 'WindPro',
                'power' => 400,
                'capacity' => null,
            ],
        ];

        $item = fake()->randomElement($items);

        return [
            // user_id and category_id will be assigned by EquipmentSeeder

            'name' => $item['name'],
            'description' => fake()->randomElement([
                'Équipement énergétique fiable et facile à transporter.',
                'Solution idéale pour les besoins énergétiques en déplacement.',
                'Équipement en très bon état, testé et prêt à être utilisé.',
                'Solution pratique pour produire ou stocker de l’énergie renouvelable.',
                'Matériel performant adapté aux particuliers et aux professionnels.',
            ]),

            'brand' => $item['brand'],
            'power' => $item['power'],
            'capacity' => $item['capacity'],

            'condition' => fake()->randomElement([
                'excellent',
                'good',
                'used',
            ]),

            'price_per_day' => fake()->randomFloat(2, 20, 150),

            'location' => fake()->randomElement([
                'Tunis',
                'Ariana',
                'Ben Arous',
                'Nabeul',
                'Sousse',
                'Monastir',
                'Sfax',
                'Médenine',
            ]),

            'availability' => fake()->boolean(80),

            // Factory equipment will use the placeholder in our interface.
            'image' => null,

            'status' => 'active',
        ];
    }
}