<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Data\AdminMockData;

class RentalController extends Controller
{
    public function index()
    {
        $rentals = AdminMockData::getRentals();
        $statuses = ['Tous', 'En attente', 'En cours', 'Terminée', 'Annulée'];
        
        // Apply filters
        $search = request('search', '');
        $selectedStatus = request('status', 'Tous');

        $filtered = array_filter($rentals, function ($r) use ($search, $selectedStatus) {
            $matchSearch = empty($search) || stripos($r['client'], $search) !== false || stripos($r['equipment'], $search) !== false;
            $matchStatus = $selectedStatus === 'Tous' || $r['status'] === $selectedStatus;
            return $matchSearch && $matchStatus;
        });

        return view('backend.rentals.index', [
            'rentals' => array_values($filtered),
            'statuses' => $statuses,
            'search' => $search,
            'selectedStatus' => $selectedStatus,
        ]);
    }
}
