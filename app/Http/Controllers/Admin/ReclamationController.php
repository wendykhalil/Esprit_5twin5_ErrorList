<?php

namespace App\Http\Controllers\Admin;

use App\Events\ReclamationApproved;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveReclamationRequest;
use App\Http\Requests\RejectReclamationRequest;
use App\Http\Requests\ApplyCompensationCodeRequest;
use App\Models\Reclamation;
use App\Models\Incident;
use App\Models\Transaction;
use App\Models\CompensationCode;
use App\Services\ApplyCompensationCodeService;
use App\Services\ApproveReclamationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReclamationController extends Controller
{
    /**
     * Display a listing of reclamations.
     */
    public function index(Request $request)
    {
        $query = Reclamation::with(['user', 'reservation.equipment', 'incidents']);

        // Filtrer par statut si demandé
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filtrer par utilisateur si demandé
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $reclamations = $query->orderByDesc('created_at')->paginate(15);

        return view('backend.reclamations.index', compact('reclamations'));
    }

    /**
     * Display the specified reclamation.
     */
    public function show(Reclamation $reclamation)
    {
        $reclamation->load(['user', 'reservation.equipment', 'incidents.equipment']);

        $totalProposedRefund = $reclamation->calculateTotalRefund();

        return view('backend.reclamations.show', compact('reclamation', 'totalProposedRefund'));
    }

    /**
     * Show the form for approving a reclamation and assigning compensation code.
     */
    public function approveForm(Reclamation $reclamation)
    {
        $this->authorize('approve', $reclamation);

        $reclamation->load(['incidents.equipment', 'reservation', 'user', 'compensationCode']);

        // Check if reclamation can receive compensation
        if (!$reclamation->canReceiveCompensation()) {
            return redirect()->route('admin.reclamations.show', $reclamation->id)
                ->with('error', 'Cette réclamation ne peut pas recevoir de compensation.');
        }

        // For backward compatibility, still show total proposed refund
        $totalProposedRefund = $reclamation->calculateTotalRefund();

        return view('backend.reclamations.approve', compact('reclamation', 'totalProposedRefund'));
    }

    /**
     * Approve a reclamation by assigning a compensation code.
     * 
     * Atomic operation ensuring:
     * - Reclamation status is changed to 'approuvee'
     * - Compensation code is generated and assigned
     * - Code cannot be assigned twice for the same reclamation
     * - All-or-nothing transaction
     */
    public function approve(ApplyCompensationCodeRequest $request, Reclamation $reclamation)
    {
        $this->authorize('approve', $reclamation);

        $validated = $request->validated();

        try {
            if ($request->filled('type')) {
                $compensationCode = (new ApplyCompensationCodeService())->apply(
                    reclamation: $reclamation,
                    validated: $validated,
                    createdBy: Auth::id()
                );

                ReclamationApproved::dispatch($reclamation->refresh());

                return redirect()->route('admin.reclamations.show', $reclamation->id)
                    ->with('success', 'Réclamation approuvée. Code de compensation attribué : ' . $compensationCode->code);
            }

            $approved = (new ApproveReclamationService())->approve($reclamation, $validated);

            ReclamationApproved::dispatch($approved);

            return redirect()->route('admin.reclamations.show', $reclamation->id)
                ->with('success', 'Réclamation approuvée. Remboursement enregistré.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'approbation : ' . $e->getMessage());
        }
    }

    /**
     * Show the form for rejecting a reclamation.
     */
    public function rejectForm(Reclamation $reclamation)
    {
        $this->authorize('reject', $reclamation);

        $reclamation->load(['incidents.equipment', 'reservation.equipment', 'user']);

        return view('backend.reclamations.reject', compact('reclamation'));
    }

    /**
     * Reject a reclamation using the service.
     */
    public function reject(RejectReclamationRequest $request, Reclamation $reclamation)
    {
        $this->authorize('reject', $reclamation);

        $validated = $request->validated();
        $service = new ApplyCompensationCodeService();

        try {
            $service->reject(
                reclamation: $reclamation,
                rejectedReason: $validated['rejected_reason'],
                adminNotes: $validated['admin_notes'] ?? null
            );

            return redirect()->route('admin.reclamations.show', $reclamation->id)
                ->with('success', 'Réclamation rejetée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors du rejet : ' . $e->getMessage());
        }
    }
}
