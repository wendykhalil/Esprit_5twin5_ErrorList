<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Equipment;
use Carbon\Carbon;

class EquipmentAnalyticsController extends Controller
{
    public function index()
    {
        $totalEquipment = Equipment::count();
        $availableEquipment = Equipment::where('availability', true)->count();
        $unavailableEquipment = Equipment::where('availability', false)->count();
        $totalCategories = Category::count();

        $categoryStats = Category::withCount('equipment')
            ->orderBy('name')
            ->get()
            ->map(fn ($category) => [
                'name' => $category->name,
                'count' => $category->equipment_count,
            ]);

        $conditionStats = Equipment::selectRaw('`condition`, COUNT(*) as total')
            ->groupBy('condition')
            ->orderBy('condition')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->condition ?: 'Non renseigné',
                'count' => (int) $item->total,
            ]);

        $monthlyStats = collect(range(5, 0))
            ->map(function ($monthsAgo) {
                $date = Carbon::now()->startOfMonth()->subMonths($monthsAgo);

                return [
                    'name' => $date->format('M Y'),
                    'count' => Equipment::whereBetween('created_at', [
                        $date->copy()->startOfMonth(),
                        $date->copy()->endOfMonth(),
                    ])->count(),
                ];
            });

        return view('backend.equipments.analytics', compact(
            'totalEquipment',
            'availableEquipment',
            'unavailableEquipment',
            'totalCategories',
            'categoryStats',
            'conditionStats',
            'monthlyStats'
        ));
    }
}
