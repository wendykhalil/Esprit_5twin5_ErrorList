<?php

namespace App\Http\Controllers\Admin;

use App\Models\Delivery;
use App\Models\Reservation;
use App\Http\Requests\Admin\UpdateDeliveryStatusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeliveryController
{
    /**
     * Display a listing of deliveries.
     */
    public function index(): View
    {
        $deliveries = Delivery::with(['reservation', 'user', 'equipment'])
            ->orderBy('planned_delivery_date', 'asc')
            ->paginate(15);

        return view('backend.deliveries.index', [
            'deliveries' => $deliveries,
            'statuses' => Delivery::STATUTS,
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

        // Update status and timestamp if transitioning to delivery/return
        $updates = ['status' => $newStatus];

        if ($newStatus === 'remise_au_client') {
            $updates['actual_delivery_date'] = now();
        } elseif ($newStatus === 'retour_recu') {
            $updates['actual_return_date'] = now();
        } elseif ($newStatus === 'terminee') {
            // Only set return date if not already set
            if (!$delivery->actual_return_date) {
                $updates['actual_return_date'] = now();
            }
        }

        // Update notes if provided
        if (!empty($validated['notes'])) {
            if ($newStatus === 'remise_au_client') {
                $updates['delivery_notes'] = $validated['notes'];
            } elseif ($newStatus === 'retour_recu' || $newStatus === 'terminee') {
                $updates['return_notes'] = $validated['notes'];
            } else {
                $updates['notes'] = $validated['notes'];
            }
        }

        $delivery->update($updates);

        return redirect()
            ->route('admin.deliveries.show', $delivery)
            ->with('success', "Statut de la livraison mis à jour: {$newStatus}.");
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
            'status' => 'prete', // Changed from 'a_preparer' to 'prete'
        ]);
    }
}
