<?php

namespace App\Http\Controllers;

use App\Data\EquipmentData;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipments = EquipmentData::getAll();
        $categories = EquipmentData::getCategories();
        
        // Apply filters
        $query = request('q', '');
        $location = request('location', '');
        $category = request('category', '');
        $maxPrice = (int)request('maxPrice', 200);
        $available = request('available', false) === 'on' || request('available') === '1';
        $sort = request('sort', 'popular');

        // Filter equipment
        $filtered = array_filter($equipments, function ($eq) use ($query, $location, $category, $maxPrice, $available) {
            if ($query && !stripos($eq['name'], $query) && !stripos($eq['description'], $query)) {
                return false;
            }
            if ($location && $eq['location'] !== $location) {
                return false;
            }
            if ($category && $eq['category'] !== $category) {
                return false;
            }
            if ($eq['price'] > $maxPrice) {
                return false;
            }
            if ($available && !$eq['available']) {
                return false;
            }
            return true;
        });

        // Sort
        $filtered = array_values($filtered);
        if ($sort === 'price_asc') {
            usort($filtered, fn($a, $b) => $a['price'] <=> $b['price']);
        } elseif ($sort === 'price_desc') {
            usort($filtered, fn($a, $b) => $b['price'] <=> $a['price']);
        } elseif ($sort === 'rating') {
            usort($filtered, fn($a, $b) => $b['rating'] <=> $a['rating']);
        }

        // Pagination
        $perPage = 6;
        $page = (int)request('page', 1);
        $totalPages = ceil(count($filtered) / $perPage);
        $paginated = array_slice($filtered, ($page - 1) * $perPage, $perPage);

        return view('frontend.equipments.index', [
            'equipments' => $paginated,
            'categories' => $categories,
            'filtered' => $filtered,
            'page' => $page,
            'totalPages' => $totalPages,
            'query' => $query,
            'location' => $location,
            'category' => $category,
            'maxPrice' => $maxPrice,
            'available' => $available,
            'sort' => $sort,
        ]);
    }

    public function show($id)
    {
        $equipment = EquipmentData::getById($id);
        
        if (!$equipment) {
            abort(404);
        }

        // Get related equipment
        $allEquipments = EquipmentData::getAll();
        $related = array_filter($allEquipments, function ($eq) use ($equipment) {
            return $eq['category'] === $equipment['category'] && $eq['id'] !== $equipment['id'];
        });
        $related = array_slice(array_values($related), 0, 3);

        return view('frontend.equipments.show', [
            'equipment' => $equipment,
            'related' => $related,
        ]);
    }
}
