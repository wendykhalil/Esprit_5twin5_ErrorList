<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Data\AdminMockData;

class DashboardController extends Controller
{
    public function index()
    {
        $rentals = AdminMockData::getRentals();
        $equipments = AdminMockData::getEquipments();

        return view('backend.dashboard', [
            'rentals' => array_slice($rentals, 0, 5),
            'equipments' => array_slice($equipments, 0, 5),
        ]);
    }
}
