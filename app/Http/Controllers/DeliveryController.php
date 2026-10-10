<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Illuminate\Http\Request;
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
}
