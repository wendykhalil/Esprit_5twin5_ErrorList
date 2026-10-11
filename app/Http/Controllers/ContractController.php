<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    /**
     * Display a listing of the user's contracts.
     */
    public function index()
    {
        $contracts = Contract::forUser(auth()->id())
            ->with(['reservation', 'equipment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('contracts.index', compact('contracts'));
    }

    /**
     * Display a specific contract.
     */
    public function show(Contract $contract)
    {
        // Ensure user can only view their own contracts
        abort_unless($contract->user_id === auth()->id(), 403);

        $contract->load(['reservation', 'equipment', 'user']);

        return view('contracts.show', compact('contract'));
    }

    /**
     * Display the print/export view of a contract.
     */
    public function print(Contract $contract)
    {
        // Ensure user can only print their own contracts
        abort_unless($contract->user_id === auth()->id(), 403);

        $contract->load(['reservation', 'equipment', 'user']);

        return view('contracts.print', compact('contract'));
    }

    /**
     * Download contract as HTML (can be printed to PDF by browser).
     */
    public function download(Contract $contract)
    {
        // Ensure user can only download their own contracts
        abort_unless($contract->user_id === auth()->id(), 403);

        $contract->load(['reservation', 'equipment', 'user']);

        return response()
            ->view('contracts.print', compact('contract'))
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $contract->contract_number . '.html"');
    }

    /**
     * Accept the contract and trigger delivery creation.
     * 
     * Flow:
     * 1. Client reviews contract
     * 2. Client clicks "Accept Contract"
     * 3. Backend records acceptance timestamp
     * 4. Delivery created automatically with status='prete'
     * 5. Client redirected to delivery details
     * 6. Delivery becomes visible in "Mes livraisons"
     */
    public function accept(Request $request, Contract $contract)
    {
        // Authorization: User must own this contract
        abort_unless($contract->user_id === auth()->id(), 403);

        // Business rule: Contract not already accepted
        if ($contract->isAccepted()) {
            return redirect()->route('contracts.show', $contract)
                ->with('info', 'Ce contrat a déjà été accepté le ' . $contract->accepted_at->format('d/m/Y à H:i') . '.');
        }

        // Record contract acceptance
        $contract->accept();

        // Create or get delivery for this contract's reservation
        try {
            $delivery = $this->createOrUpdateDelivery($contract->reservation);

            return redirect()->route('deliveries.show', $delivery)
                ->with('success', 'Contrat accepté avec succès! Votre livraison est maintenant disponible dans "Mes livraisons".');
        } catch (\Exception $e) {
            \Log::error("Failed to create delivery for contract {$contract->id}: {$e->getMessage()}");

            return redirect()->route('contracts.show', $contract)
                ->with('warning', 'Contrat accepté, mais une erreur s\'est produite lors de la création de la livraison. Veuillez contacter le support.');
        }
    }

    /**
     * Create delivery for reservation on contract acceptance.
     * Idempotent: If delivery already exists, returns it instead of creating a duplicate.
     * 
     * @param Reservation $reservation
     * @return \App\Models\Delivery
     */
    private function createOrUpdateDelivery(Reservation $reservation)
    {
        // Check if delivery already exists (idempotent operation)
        if ($reservation->delivery) {
            return $reservation->delivery;
        }

        // Create new delivery with status='prete' (Ready)
        // Delivery is now marked as ready because equipment has been prepared after payment
        return \App\Models\Delivery::create([
            'reservation_id' => $reservation->id,
            'user_id' => $reservation->user_id,
            'equipment_id' => $reservation->equipment_id,
            'planned_delivery_date' => $reservation->date_debut,
            'planned_return_date' => $reservation->date_fin,
            'status' => 'prete', // Ready for delivery (new workflow)
        ]);
    }
}
