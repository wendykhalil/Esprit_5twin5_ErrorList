<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEquipmentGuideRequest;
use App\Http\Requests\UpdateEquipmentGuideRequest;
use App\Models\Equipment;
use App\Models\EquipmentGuide;
use Illuminate\Http\Request;

class EquipmentGuideController extends Controller
{
    /**
     * List all guides for admin.
     */
    public function index(Request $request)
    {
        $query = EquipmentGuide::with('equipment');

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('usage_context', 'like', "%{$search}%")
                    ->orWhereHas('equipment', function ($equipmentQuery) use ($search) {
                        $equipmentQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $guides = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'backend.equipment-guides.index',
            compact('guides')
        );
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $equipments = Equipment::orderBy('name')->get();

        return view(
            'backend.equipment-guides.create',
            compact('equipments')
        );
    }

    /**
     * Save a new guide.
     */
    public function store(StoreEquipmentGuideRequest $request)
    {
        EquipmentGuide::create($request->validated());

        return redirect()
            ->route('admin.equipment-guides.index')
            ->with('success', 'Guide created successfully.');
    }

    /**
     * Show guide details.
     */
    public function show(EquipmentGuide $equipmentGuide)
    {
        $equipmentGuide->load('equipment');

        return view(
            'backend.equipment-guides.show',
            compact('equipmentGuide')
        );
    }

    /**
     * Show edit form.
     */
    public function edit(EquipmentGuide $equipmentGuide)
    {
        $equipments = Equipment::orderBy('name')->get();

        return view(
            'backend.equipment-guides.edit',
            compact('equipmentGuide', 'equipments')
        );
    }

    /**
     * Update an existing guide.
     */
    public function update(
        UpdateEquipmentGuideRequest $request,
        EquipmentGuide $equipmentGuide
    ) {
        $equipmentGuide->update($request->validated());

        return redirect()
            ->route('admin.equipment-guides.index')
            ->with('success', 'Guide updated successfully.');
    }

    /**
     * Delete a guide.
     */
    public function destroy(EquipmentGuide $equipmentGuide)
    {
        $equipmentGuide->delete();

        return redirect()
            ->route('admin.equipment-guides.index')
            ->with('success', 'Guide deleted successfully.');
    }
}
