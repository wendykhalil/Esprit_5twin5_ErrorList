<?php

namespace App\Http\Controllers;

use App\Models\CompensationCode;
use Illuminate\Support\Facades\Auth;

class CompensationCodeController extends Controller
{
    /**
     * Display a listing of the user's compensation codes.
     */
    public function index()
    {
        $user = Auth::user();

        // Get all compensation codes for the current user
        $compensationCodes = CompensationCode::where('user_id', $user->id)
            ->with(['reclamation', 'reclamation.reservation.equipment'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('frontend.compensation-codes.index', compact('compensationCodes'));
    }

    /**
     * Display the specified compensation code.
     */
    public function show(CompensationCode $compensationCode)
    {
        // Only the code owner can view their own code
        $this->authorize('view', $compensationCode);

        $compensationCode->load(['reclamation', 'reclamation.reservation.equipment', 'createdBy']);

        return view('frontend.compensation-codes.show', compact('compensationCode'));
    }
}
