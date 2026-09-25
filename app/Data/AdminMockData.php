<?php

namespace App\Data;

class AdminMockData
{
    public static function getEquipments()
    {
        return [
            [
                'id' => 1,
                'name' => 'Panneau solaire portable 300W',
                'type' => 'Panneau solaire',
                'owner' => 'Ahmed Khalil',
                'price' => 45,
                'available' => true,
                'image' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?w=80&h=80&fit=crop&auto=format',
            ],
            [
                'id' => 2,
                'name' => 'Batterie solaire LiFePO4 200Ah',
                'type' => 'Batterie',
                'owner' => 'Mariem Trabelsi',
                'price' => 60,
                'available' => true,
                'image' => 'https://images.unsplash.com/photo-1620714223084-8fcacc2dbe4d?w=80&h=80&fit=crop&auto=format',
            ],
            [
                'id' => 3,
                'name' => 'Mini éolienne 400W',
                'type' => 'Éolienne',
                'owner' => 'Youssef Ben Ali',
                'price' => 80,
                'available' => false,
                'image' => 'https://images.unsplash.com/photo-1466611653911-95081537e5b7?w=80&h=80&fit=crop&auto=format',
            ],
            [
                'id' => 4,
                'name' => 'Chargeur solaire MPPT 60A',
                'type' => 'Chargeur',
                'owner' => 'Fatma Saidi',
                'price' => 35,
                'available' => true,
                'image' => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?w=80&h=80&fit=crop&auto=format',
            ],
            [
                'id' => 5,
                'name' => 'Panneau solaire flexible 150W',
                'type' => 'Panneau solaire',
                'owner' => 'Mehdi Bouaziz',
                'price' => 30,
                'available' => true,
                'image' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?w=80&h=80&fit=crop&auto=format',
            ],
            [
                'id' => 6,
                'name' => 'Onduleur hybride 3kW',
                'type' => 'Onduleur',
                'owner' => 'Sarra Hamdi',
                'price' => 70,
                'available' => false,
                'image' => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?w=80&h=80&fit=crop&auto=format',
            ],
        ];
    }

    public static function getUsers()
    {
        return [
            [
                'id' => 1,
                'name' => 'Ahmed Khalil',
                'email' => 'ahmed.khalil@email.com',
                'role' => 'Propriétaire',
                'joinDate' => '12 Jan 2024',
                'status' => 'Actif',
                'avatar' => 'AK',
            ],
            [
                'id' => 2,
                'name' => 'Mariem Trabelsi',
                'email' => 'mariem.t@email.com',
                'role' => 'Client',
                'joinDate' => '3 Fév 2024',
                'status' => 'Actif',
                'avatar' => 'MT',
            ],
            [
                'id' => 3,
                'name' => 'Youssef Ben Ali',
                'email' => 'youssef.ba@email.com',
                'role' => 'Propriétaire',
                'joinDate' => '18 Fév 2024',
                'status' => 'Actif',
                'avatar' => 'YB',
            ],
            [
                'id' => 4,
                'name' => 'Fatma Saidi',
                'email' => 'fatma.saidi@email.com',
                'role' => 'Client',
                'joinDate' => '5 Mar 2024',
                'status' => 'Inactif',
                'avatar' => 'FS',
            ],
            [
                'id' => 5,
                'name' => 'Mehdi Bouaziz',
                'email' => 'mehdi.b@email.com',
                'role' => 'Propriétaire',
                'joinDate' => '22 Mar 2024',
                'status' => 'Actif',
                'avatar' => 'MB',
            ],
            [
                'id' => 6,
                'name' => 'Sarra Hamdi',
                'email' => 'sarra.h@email.com',
                'role' => 'Client',
                'joinDate' => '1 Avr 2024',
                'status' => 'Actif',
                'avatar' => 'SH',
            ],
            [
                'id' => 7,
                'name' => 'Administrateur',
                'email' => 'admin@solarshare.tn',
                'role' => 'Administrateur',
                'joinDate' => '1 Jan 2024',
                'status' => 'Actif',
                'avatar' => 'AD',
            ],
        ];
    }

    public static function getRentals()
    {
        return [
            [
                'id' => 1,
                'client' => 'Mariem Trabelsi',
                'equipment' => 'Panneau solaire 300W',
                'startDate' => '10 Sep 2024',
                'endDate' => '17 Sep 2024',
                'price' => 315,
                'status' => 'Terminée',
            ],
            [
                'id' => 2,
                'client' => 'Fatma Saidi',
                'equipment' => 'Batterie solaire LiFePO4',
                'startDate' => '15 Sep 2024',
                'endDate' => '22 Sep 2024',
                'price' => 420,
                'status' => 'En cours',
            ],
            [
                'id' => 3,
                'client' => 'Sarra Hamdi',
                'equipment' => 'Mini éolienne 400W',
                'startDate' => '20 Sep 2024',
                'endDate' => '27 Sep 2024',
                'price' => 560,
                'status' => 'En attente',
            ],
            [
                'id' => 4,
                'client' => 'Ahmed Khalil',
                'equipment' => 'Chargeur MPPT 60A',
                'startDate' => '5 Sep 2024',
                'endDate' => '12 Sep 2024',
                'price' => 245,
                'status' => 'Terminée',
            ],
            [
                'id' => 5,
                'client' => 'Youssef Ben Ali',
                'equipment' => 'Panneau flexible 150W',
                'startDate' => '18 Sep 2024',
                'endDate' => '21 Sep 2024',
                'price' => 90,
                'status' => 'Annulée',
            ],
            [
                'id' => 6,
                'client' => 'Mehdi Bouaziz',
                'equipment' => 'Onduleur hybride 3kW',
                'startDate' => '22 Sep 2024',
                'endDate' => '29 Sep 2024',
                'price' => 490,
                'status' => 'En attente',
            ],
        ];
    }
}
