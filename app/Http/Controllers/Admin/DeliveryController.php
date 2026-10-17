<?php

namespace App\Http\Controllers\Admin;

use App\Events\DeliveryStatusChanged;
use App\Models\Delivery;
use App\Models\Reservation;
use App\Http\Requests\Admin\UpdateDeliveryStatusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeliveryController
{
    /**
     * Display a listing of deliveries with filtering and search.
     * 
     * Supports:
     * - Filter by equipment_id (equipment dropdown)
     * - Filter by status (status dropdown)
     * - Search by delivery ID, client name, or equipment name
     * - Pagination with query string preservation (10 per page)
     * 
     * Sorted by created_at DESC (newest first) as the primary sort,
     * and by id DESC as a deterministic secondary sort for records
     * created at the same timestamp.
     */
    public function index(): View
    {
        $query = Delivery::with(['reservation', 'user', 'equipment']);

        // Filter by equipment
        if (request()->filled('equipment_id')) {
            $equipmentId = request()->input('equipment_id');
            $query->where('equipment_id', $equipmentId);
        }

        // Filter by status
        if (request()->filled('status')) {
            $status = request()->input('status');
            if (array_key_exists($status, Delivery::STATUTS)) {
                $query->where('status', $status);
            }
        }

        // Search by delivery ID, client name, or equipment name
        if (request()->filled('search')) {
            $search = request()->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', '=', $search)
                    ->orWhereHas('user', fn($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('equipment', fn($equipQuery) => $equipQuery->where('name', 'like', "%{$search}%"));
            });
        }

        // Apply sorting and pagination
        $deliveries = $query
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Get all equipment for the equipment filter dropdown
        $equipmentList = \App\Models\Equipment::orderBy('name')->get();

        return view('backend.deliveries.index', [
            'deliveries' => $deliveries,
            'statuses' => Delivery::STATUTS,
            'equipmentList' => $equipmentList,
            'activeFilters' => [
                'equipment_id' => request()->input('equipment_id'),
                'status' => request()->input('status'),
                'search' => request()->input('search'),
            ],
        ]);
    }

    /**
     * Display the specified delivery.
     */
    public function show(Delivery $delivery): View
    {
        $delivery->load(['reservation', 'user', 'equipment']);

        return view('backend.deliveries.show', [
            'delivery' => $delivery,
            'statuses' => Delivery::STATUTS,
            'validTransitions' => $delivery->getValidTransitions(),
        ]);
    }

    /**
     * Update the delivery status.
     * Enforces the 5-step workflow state machine with proper timestamp recording.
     */
    public function updateStatus(UpdateDeliveryStatusRequest $request, Delivery $delivery): RedirectResponse
    {
        $validated = $request->validated();
        $newStatus = $validated['status'];

        // Validate the transition is allowed
        if (!$delivery->canTransitionTo($newStatus)) {
            return back()
                ->with('error', "Impossible de passer de '{$delivery->status}' à '{$newStatus}'.")
                ->withInput();
        }

        // Update status and record appropriate timestamps based on workflow stage
        $updates = ['status' => $newStatus];

        // Record timestamps for each workflow stage
        match ($newStatus) {
            'prete' => $updates['ready_at'] = now(),
            'remise_au_client' => $updates['actual_delivery_date'] = now(),
            'retour_recu' => $updates['actual_return_date'] = now(),
            'terminee' => $updates['updated_at'] = now(), // Completion tracked via updated_at
            default => null,
        };

        // Update notes if provided (stored in appropriate field based on stage)
        if (!empty($validated['notes'])) {
            if ($newStatus === 'remise_au_client') {
                $updates['delivery_notes'] = $validated['notes'];
            } elseif ($newStatus === 'retour_recu' || $newStatus === 'terminee') {
                $updates['return_notes'] = $validated['notes'];
            } else {
                $updates['notes'] = $validated['notes'];
            }
        }

        $oldStatus = $delivery->status;
        $delivery->update($updates);

        // Dispatch event after successful update
        DeliveryStatusChanged::dispatch($delivery, $oldStatus);

        $statusLabel = Delivery::STATUTS[$newStatus] ?? ucfirst(str_replace('_', ' ', $newStatus));

        return redirect()
            ->route('admin.deliveries.show', $delivery)
            ->with('success', "Statut de la livraison mis à jour: {$statusLabel}.");
    }

    /**
     * Create a delivery for a confirmed reservation (called from admin workflow).
     * This is a helper method that could be triggered when admin confirms reservation
     * or manually when needed.
     * 
     * @deprecated Use ContractController::accept() instead. Delivery is now created
     * when the client accepts the contract, not when the reservation is approved.
     */
    public static function createDeliveryForReservation(Reservation $reservation): Delivery
    {
        // Check if delivery already exists
        if ($reservation->delivery) {
            return $reservation->delivery;
        }

        // Business rule: Only create deliveries for confirmed or later reservations
        if (!in_array($reservation->statut, ['confirmee', 'en_cours', 'terminee'])) {
            throw new \Exception(
                "Cannot create delivery for reservation with status '{$reservation->statut}'. " .
                "Reservation must be confirmed first."
            );
        }

        return Delivery::create([
            'reservation_id' => $reservation->id,
            'user_id' => $reservation->user_id,
            'equipment_id' => $reservation->equipment_id,
            'planned_delivery_date' => $reservation->date_debut,
            'planned_return_date' => $reservation->date_fin,
            'status' => 'a_preparer', // Start with "À préparer" status
        ]);
    }
}
