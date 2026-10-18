<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Http\Requests\ValidateEquipmentWizardStepRequest;
use App\Models\Category;
use App\Models\Equipment;
use App\Services\CloudinaryImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipment::with([
            'user',
            'category',
        ]);

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

        $categories = Category::orderBy('name')->get();

        return view(
            'backend.equipments.index',
            compact('equipments', 'categories')
        );
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view(
            'backend.equipments.create',
            compact('categories')
        );
    }

    public function validateWizardStep(
        ValidateEquipmentWizardStepRequest $request
    ) {
        return response()->json(['ok' => true]);
    }

    public function store(
        StoreEquipmentRequest $request,
        CloudinaryImageService $cloudinaryService
    ) {
        $validated = $request->validated();

        $validated['user_id'] = $request->user()->id;
        $validated['availability'] =
            $request->boolean('availability');

        $uploadedPublicId = null;

        try {
            if ($request->hasFile('image')) {
                $uploaded = $cloudinaryService->upload(
                    $request->file('image')
                );

                $uploadedPublicId = $uploaded['public_id'];

                $validated['image'] = $uploaded['secure_url'];
                $validated['image_public_id'] =
                    $uploadedPublicId;
            }

            Equipment::create($validated);

        } catch (Throwable $e) {
            Log::error('Equipment creation failed', [
                'error' => $e->getMessage(),
            ]);

            if ($uploadedPublicId) {
                try {
                    $cloudinaryService->delete(
                        $uploadedPublicId
                    );
                } catch (Throwable $cleanupError) {
                    Log::warning('Cloudinary cleanup failed', [
                        'error' => $cleanupError->getMessage(),
                    ]);
                }
            }

            return back()
                ->withInput($request->except('image'))
                ->withErrors([
                    'image' => 'Impossible d’enregistrer cet équipement. Veuillez réessayer.',
                ]);
        }

        return redirect()
            ->route('admin.equipments')
            ->with(
                'success',
                'Équipement ajouté avec succès.'
            );
    }

    public function edit(Equipment $equipment)
    {
        $categories = Category::orderBy('name')->get();

        return view(
            'backend.equipments.edit',
            compact('equipment', 'categories')
        );
    }

    public function update(
        UpdateEquipmentRequest $request,
        Equipment $equipment,
        CloudinaryImageService $cloudinaryService
    ) {
        $validated = $request->validated();

        $validated['availability'] =
            $request->boolean('availability');

        $oldImage = $equipment->image;
        $oldPublicId = $equipment->image_public_id;

        $newPublicId = null;

        try {
            if ($request->hasFile('image')) {
                $uploaded = $cloudinaryService->upload(
                    $request->file('image')
                );

                $newPublicId = $uploaded['public_id'];

                $validated['image'] = $uploaded['secure_url'];
                $validated['image_public_id'] =
                    $newPublicId;
            }

            $equipment->update($validated);

        } catch (Throwable $e) {
            Log::error('Equipment update failed', [
                'equipment_id' => $equipment->id,
                'error' => $e->getMessage(),
            ]);

            if ($newPublicId) {
                try {
                    $cloudinaryService->delete(
                        $newPublicId
                    );
                } catch (Throwable $cleanupError) {
                    Log::warning('Cloudinary cleanup failed', [
                        'error' => $cleanupError->getMessage(),
                    ]);
                }
            }

            return back()
                ->withInput($request->except('image'))
                ->withErrors([
                    'image' => 'Impossible de modifier cet équipement. Veuillez réessayer.',
                ]);
        }

        if ($newPublicId) {
            $this->deleteOldImage(
                $oldImage,
                $oldPublicId,
                $cloudinaryService
            );
        }

        return redirect()
            ->route('admin.equipments')
            ->with(
                'success',
                'Équipement modifié avec succès.'
            );
    }

    public function destroy(
        Equipment $equipment,
        CloudinaryImageService $cloudinaryService
    ) {
        $oldImage = $equipment->image;
        $oldPublicId = $equipment->image_public_id;
        $equipmentId = $equipment->id;

        try {
            $equipment->delete();
        } catch (Throwable $e) {
            Log::error('Equipment deletion failed', [
                'equipment_id' => $equipmentId,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'equipment' => 'Impossible de supprimer cet équipement.',
            ]);
        }

        $this->deleteOldImage(
            $oldImage,
            $oldPublicId,
            $cloudinaryService
        );

        return redirect()
            ->route('admin.equipments')
            ->with(
                'success',
                'Équipement supprimé avec succès.'
            );
    }

    public function show(Equipment $equipment)
    {
        $equipment->load(['category', 'user']);

        return view(
            'backend.equipments.show',
            compact('equipment')
        );
    }

    private function deleteOldImage(
        ?string $image,
        ?string $publicId,
        CloudinaryImageService $cloudinaryService
    ): void {
        try {
            if ($publicId) {
                $cloudinaryService->delete($publicId);
            } elseif (
                $image &&
                !str_starts_with($image, 'https://') &&
                !str_starts_with($image, 'http://')
            ) {
                Storage::disk('public')->delete($image);
            }
        } catch (Throwable $e) {
            Log::warning('Equipment image cleanup failed', [
                'image' => $image,
                'error' => $e->getMessage(),
            ]);
        }
    }
}