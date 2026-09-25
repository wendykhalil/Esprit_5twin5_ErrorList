<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Data\AdminMockData;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipments = AdminMockData::getEquipments();
        $types = ['Tous', 'Panneau solaire', 'Batterie', 'Éolienne', 'Chargeur', 'Onduleur'];
        
        // Apply filters
        $search = request('search', '');
        $selectedType = request('type', 'Tous');

        $filtered = array_filter($equipments, function ($eq) use ($search, $selectedType) {
            $matchSearch = empty($search) || stripos($eq['name'], $search) !== false || stripos($eq['owner'], $search) !== false;
            $matchType = $selectedType === 'Tous' || $eq['type'] === $selectedType;
            return $matchSearch && $matchType;
        });

        return view('backend.equipments.index', [
            'equipments' => array_values($filtered),
            'types' => $types,
            'search' => $search,
            'selectedType' => $selectedType,
        ]);
    }
}
