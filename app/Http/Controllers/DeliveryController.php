<?php

namespace App\Http\Controllers;

use App\Events\DeliveryStatusChanged;
use App\Http\Requests\ConfirmDeliveryReceiptRequest;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeliveryController
{
    /**
     * Display a listing of deliveries for the authenticated client.
     */
    public function index(): View
    {
        $deliveries = Delivery::where('user_id', auth()->id())
            ->with(['reservation', 'equipment'])
            ->orderBy('planned_delivery_date', 'desc')
            ->paginate(10);

        return view('deliveries.index', [
            'deliveries' => $deliveries,
            'statuses' => Delivery::STATUTS,
        ]);
    }

    /**
     * Display the specified delivery.
     */
    public function show(Delivery $delivery): View
    {
        // Authorization: Only the client who owns the delivery can view it
        abort_unless($delivery->user_id === auth()->id(), 403);

        $delivery->load(['reservation', 'equipment']);

        return view('deliveries.show', [
            'delivery' => $delivery,
            'statuses' => Delivery::STATUTS,
        ]);
    }

    /**
     * Client confirms receipt of equipment (transitions prete → remise_au_client).
     * Only the authenticated client who owns the delivery can confirm receipt.
     * The delivery must be in 'prete' status.
     */
    public function confirmReceipt(ConfirmDeliveryReceiptRequest $request, Delivery $delivery): RedirectResponse
    {
        // Authorization is checked in the form request
        
        // Validate state machine transition
        if ($delivery->status !== 'prete') {
            return back()
                ->with('error', 'Cette livraison n\'est pas prête pour confirmation. Statut actuel: ' . $delivery->status)
                ->withInput();
        }

        // Record the confirmation timestamp and update status
        $updates = [
            'status' => 'remise_au_client',
            'client_received_at' => now(),
            'actual_delivery_date' => now(), // Also set actual_delivery_date
        ];

        // Store condition notes if provided
        if (!empty($request->validated()['condition_notes'])) {
            $updates['delivery_notes'] = $request->validated()['condition_notes'];
        }

        $oldStatus = $delivery->status;
        $delivery->update($updates);

        // Dispatch event to notify admins and refresh real-time updates
        DeliveryStatusChanged::dispatch($delivery, $oldStatus);

        return redirect()
            ->route('deliveries.show', $delivery)
            ->with('success', 'Réception de l\'équipement confirmée avec succès.');
    }
}
