<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Http\Requests\ValidateEquipmentWizardStepRequest;
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
     * Show admin create form.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view(
            'backend.equipments.create',
            compact('categories')
        );
    }

    /**
     * Validate wizard step fields (JSON) before advancing to the next step.
     */
    public function validateWizardStep(ValidateEquipmentWizardStepRequest $request)
    {
        return response()->json(['ok' => true]);
    }

    /**
     * Store equipment from admin.
     */
    public function store(StoreEquipmentRequest $request)
    {
        $validated = $request->validated();

        $validated['user_id'] = $request->user()->id;
        $validated['availability'] = $request->boolean('availability');

        if ($request->hasFile('image')) {
            $validated['image'] = $request
                ->file('image')
                ->store('equipments', 'public');
        }

        Equipment::create($validated);

        return redirect()
            ->route('admin.equipments')
            ->with('success', 'Équipement ajouté avec succès.');
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
        UpdateEquipmentRequest $request,
        Equipment $equipment
    ) {
        $validated = $request->validated();

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