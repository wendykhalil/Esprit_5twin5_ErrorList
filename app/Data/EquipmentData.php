<?php

namespace App\Data;

class EquipmentData
{
    public static function getAll()
    {
        return [
            [
                'id' => 1,
                'name' => 'Panneau solaire portable 200W',
                'description' => 'Panneau monocristallin pliable, idéal pour camping et usage extérieur. Léger et facile à transporter.',
                'location' => 'Tunis',
                'price' => 25,
                'period' => 'jour',
                'rating' => 4.8,
                'reviews' => 32,
                'owner' => 'Ahmed B.',
                'ownerInitial' => 'A',
                'image' => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?w=400&h=300&fit=crop&auto=format',
                'category' => 'Panneaux solaires',
                'available' => true,
            ],
            [
                'id' => 2,
                'name' => 'Batterie solaire 1000Wh',
                'description' => 'Station d\'énergie portable avec sortie AC/DC, charge solaire et USB-C. Parfaite pour les coupures.',
                'location' => 'Ariana',
                'price' => 40,
                'period' => 'jour',
                'rating' => 4.9,
                'reviews' => 18,
                'owner' => 'Sonia K.',
                'ownerInitial' => 'S',
                'image' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?w=400&h=300&fit=crop&auto=format',
                'category' => 'Batteries',
                'available' => true,
            ],
            [
                'id' => 3,
                'name' => 'Station électrique portable 2000W',
                'description' => 'Jackery Explorer 2000 Pro. Charge tous vos appareils : réfrigérateur, climatiseur portable, outils.',
                'location' => 'Sousse',
                'price' => 50,
                'period' => 'jour',
                'rating' => 4.7,
                'reviews' => 24,
                'owner' => 'Karim M.',
                'ownerInitial' => 'K',
                'image' => 'https://images.unsplash.com/photo-1776131263960-56261224f009?w=400&h=300&fit=crop&auto=format',
                'category' => 'Stations électriques',
                'available' => true,
            ],
            [
                'id' => 4,
                'name' => 'Mini éolienne portable 400W',
                'description' => 'Éolienne compacte sur mât télescopique, connexion directe à batterie. Idéale pour zones ventées.',
                'location' => 'Hammamet',
                'price' => 35,
                'period' => 'jour',
                'rating' => 4.5,
                'reviews' => 11,
                'owner' => 'Lina T.',
                'ownerInitial' => 'L',
                'image' => 'https://images.unsplash.com/photo-1543378993-e0041dcb24cc?w=400&h=300&fit=crop&auto=format',
                'category' => 'Équipements éoliens',
                'available' => true,
            ],
            [
                'id' => 5,
                'name' => 'Panneau solaire 150W + régulateur',
                'description' => 'Kit complet avec régulateur MPPT 20A, câbles et support inclinable. Prêt à l\'emploi.',
                'location' => 'Sfax',
                'price' => 20,
                'period' => 'jour',
                'rating' => 4.6,
                'reviews' => 15,
                'owner' => 'Mehdi R.',
                'ownerInitial' => 'M',
                'image' => 'https://images.unsplash.com/photo-1613665813446-82a78c468a1d?w=400&h=300&fit=crop&auto=format',
                'category' => 'Panneaux solaires',
                'available' => false,
            ],
            [
                'id' => 6,
                'name' => 'Groupe électrogène solaire silencieux',
                'description' => 'Système hybride solaire/batterie 3kWh. Silencieux, zéro émission. Pour événements ou chantiers.',
                'location' => 'Tunis',
                'price' => 80,
                'period' => 'jour',
                'rating' => 4.9,
                'reviews' => 7,
                'owner' => 'Yasmine O.',
                'ownerInitial' => 'Y',
                'image' => 'https://images.unsplash.com/photo-1558449028-b53a39d100fc?w=400&h=300&fit=crop&auto=format',
                'category' => 'Stations électriques',
                'available' => true,
            ],
        ];
    }

    public static function getById($id)
    {
        $all = self::getAll();
        foreach ($all as $equipment) {
            if ($equipment['id'] == $id) {
                return $equipment;
            }
        }
        return null;
    }

    public static function getCategories()
    {
        return [
            ['label' => 'Panneaux solaires', 'icon' => '☀️', 'count' => 45],
            ['label' => 'Batteries', 'icon' => '🔋', 'count' => 28],
            ['label' => 'Stations électriques', 'icon' => '⚡', 'count' => 19],
            ['label' => 'Équipements éoliens', 'icon' => '💨', 'count' => 12],
            ['label' => 'Autres équipements', 'icon' => '🔌', 'count' => 8],
        ];
    }
}
