<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEquipmentRequest;
use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
{
    /**
     * Display all equipment.
     */
    public function index(Request $request)
    {
        $query = Equipment::with(['category', 'user']);

        // Search
        if ($request->filled('q')) {
            $search = $request->q;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Location
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // Category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Maximum price
        if ($request->filled('maxPrice')) {
            $query->where('price_per_day', '<=', $request->maxPrice);
        }

        // Available only
        if ($request->boolean('available')) {
            $query->where('availability', true);
        }

        // Sorting
        switch ($request->get('sort')) {

            case 'price_asc':
                $query->orderBy('price_per_day', 'asc');
                break;

            case 'price_desc':
                $query->orderBy('price_per_day', 'desc');
                break;

            default:
                $query->latest();
                break;
        }

        $equipments = $query
            ->paginate(6)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        $locations = Equipment::select('location')
            ->whereNotNull('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        return view(
            'frontend.equipments.index',
            compact(
                'equipments',
                'categories',
                'locations'
            )
        );
    }

    /**
     * Display one equipment.
     */
    public function show(Equipment $equipment)
    {
        $equipment->load([
            'category',
            'user'
        ]);

        $related = Equipment::with([
            'category',
            'user'
        ])
            ->where(
                'category_id',
                $equipment->category_id
            )
            ->where(
                'id',
                '!=',
                $equipment->id
            )
            ->where(
                'status',
                'active'
            )
            ->take(3)
            ->get();

        return view(
            'frontend.equipments.show',
            compact(
                'equipment',
                'related'
            )
        );
    }

    /**
     * Show edit form.
     */
    public function edit(Equipment $equipment)
    {
        // Only owner can edit
        if ($equipment->user_id !== auth()->id()) {
            abort(403);
        }

        $categories = Category::orderBy('name')->get();

        return view(
            'frontend.equipments.edit',
            compact(
                'equipment',
                'categories'
            )
        );
    }

    /**
     * Update equipment.
     */
    public function update(
        UpdateEquipmentRequest $request,
        Equipment $equipment
    ) {
        // Only owner can update
        if ($equipment->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validated();

        $validated['availability'] =
            $request->boolean('availability');

        // Replace image
        if ($request->hasFile('image')) {

            if ($equipment->image) {
                Storage::disk('public')
                    ->delete($equipment->image);
            }

            $validated['image'] = $request
                ->file('image')
                ->store('equipments', 'public');
        }

        $equipment->update($validated);

        return redirect()
            ->route('equipments.show', $equipment)
            ->with(
                'success',
                'Équipement modifié avec succès.'
            );
    }

    /**
     * Delete equipment.
     */
    public function destroy(Equipment $equipment)
    {
        // Only owner can delete
        if ($equipment->user_id !== auth()->id()) {
            abort(403);
        }

        if ($equipment->image) {
            Storage::disk('public')
                ->delete($equipment->image);
        }

        $equipment->delete();

        return redirect()
            ->route('equipments.index')
            ->with(
                'success',
                'Équipement supprimé avec succès.'
            );
    }
}