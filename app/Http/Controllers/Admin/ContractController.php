<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    /**
     * Display a listing of all contracts (admin view).
     */
    public function index(Request $request)
    {
        $query = Contract::with(['user', 'reservation', 'equipment']);

        // Filter by status if provided
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by user if provided
        if ($request->has('user_id') && $request->user_id !== '') {
            $query->where('user_id', $request->user_id);
        }

        // Search by contract number if provided
        if ($request->has('search') && $request->search !== '') {
            $query->where('contract_number', 'like', '%' . $request->search . '%');
        }

        $contracts = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('backend.contracts.index', compact('contracts'));
    }

    /**
     * Display a specific contract (admin view).
     */
    public function show(Contract $contract)
    {
        $contract->load(['user', 'reservation', 'equipment']);

        return view('backend.contracts.show', compact('contract'));
    }

    /**
     * Update contract status (admin only).
     */
    public function updateStatus(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,completed,cancelled',
        ]);

        $contract->update(['status' => $validated['status']]);

        return redirect()
            ->route('admin.contracts.show', $contract)
            ->with('success', 'Statut du contrat mis à jour.');
    }

    /**
     * Display print view for admin.
     */
    public function print(Contract $contract)
    {
        $contract->load(['user', 'reservation', 'equipment']);

        return view('backend.contracts.print', compact('contract'));
    }
}
