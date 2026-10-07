<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
{
    /**
     * Display equipment list in admin.
     */
    public function index(Request $request)
    {
        $query = Equipment::with([
            'user',
            'category'
        ]);

        // Search
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {

                        $userQuery->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );

                    });

            });
        }

        // Category filter
        if ($request->filled('category')) {

            $query->where(
                'category_id',
                $request->category
            );
        }

        $equipments = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')
            ->get();

        return view(
            'backend.equipments.index',
            compact(
                'equipments',
                'categories'
            )
        );
    }


    /**
     * Show admin edit form.
     */
    public function edit(Equipment $equipment)
    {
        $categories = Category::orderBy('name')
            ->get();

        return view(
            'backend.equipments.edit',
            compact(
                'equipment',
                'categories'
            )
        );
    }


    /**
     * Update equipment from admin.
     */
    public function update(
        Request $request,
        Equipment $equipment
    ) {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',

            'name' => 'required|string|min:3|max:255',

            'description' => 'required|string|min:10',

            'brand' => 'nullable|string|max:255',

            'power' => 'nullable|numeric|min:0',

            'capacity' => 'nullable|numeric|min:0',

            'condition' => 'required|in:excellent,good,used',

            'price_per_day' => 'required|numeric|min:0',

            'location' => 'required|string|max:255',

            'availability' => 'nullable|boolean',

            'status' => 'required|in:active,inactive',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $validated['availability'] =
            $request->boolean('availability');

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
            ->route('admin.equipments')
            ->with(
                'success',
                'Équipement modifié avec succès.'
            );
    }


    /**
     * Delete equipment from admin.
     */
    public function destroy(Equipment $equipment)
    {
        if ($equipment->image) {

            Storage::disk('public')
                ->delete($equipment->image);

        }

        $equipment->delete();

        return redirect()
            ->route('admin.equipments')
            ->with(
                'success',
                'Équipement supprimé avec succès.'
            );
    }
}