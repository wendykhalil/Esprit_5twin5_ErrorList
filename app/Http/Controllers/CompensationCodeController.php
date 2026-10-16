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

    /**
     * Validate and get discount information for a compensation code (AJAX endpoint).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function validate()
    {
        $user = Auth::user();
        $codeString = request()->input('code', '');

        if (!$codeString) {
            return response()->json([
                'valid' => false,
                'message' => 'Le code de compensation est vide.',
                'discount_amount' => 0,
                'discount_type' => null,
            ], 400);
        }

        // Find the code
        $code = CompensationCode::where('code', trim($codeString))->first();

        if (!$code) {
            return response()->json([
                'valid' => false,
                'message' => 'Le code de compensation n\'existe pas.',
                'discount_amount' => 0,
                'discount_type' => null,
            ], 404);
        }

        // Check ownership
        if ($code->user_id !== $user->id) {
            return response()->json([
                'valid' => false,
                'message' => 'Ce code de compensation ne vous appartient pas.',
                'discount_amount' => 0,
                'discount_type' => null,
            ], 403);
        }

        // Check if active
        if (!$code->is_active) {
            return response()->json([
                'valid' => false,
                'message' => 'Ce code de compensation a été désactivé.',
                'discount_amount' => 0,
                'discount_type' => null,
            ], 410);
        }

        // Check if started
        if (!$code->hasStarted()) {
            return response()->json([
                'valid' => false,
                'message' => 'Ce code de compensation n\'est pas encore valide (débute le ' . $code->valid_from->format('d/m/Y') . ').',
                'discount_amount' => 0,
                'discount_type' => null,
            ], 400);
        }

        // Check if expired
        if ($code->isExpired()) {
            return response()->json([
                'valid' => false,
                'message' => 'Ce code de compensation a expiré (le ' . $code->valid_until->format('d/m/Y') . ').',
                'discount_amount' => 0,
                'discount_type' => null,
            ], 410);
        }

        // Check uses remaining
        if (!$code->hasUsesRemaining()) {
            return response()->json([
                'valid' => false,
                'message' => 'Ce code de compensation a déjà été utilisé le nombre maximum de fois.',
                'discount_amount' => 0,
                'discount_type' => null,
            ], 410);
        }

        // Code is valid!
        return response()->json([
            'valid' => true,
            'message' => 'Code de compensation valide !',
            'discount_type' => $code->type,
            'discount_value' => (float) $code->value,
            'max_uses' => $code->max_uses,
            'uses_remaining' => $code->max_uses - $code->uses_count,
            'valid_until' => $code->valid_until->format('d/m/Y'),
        ], 200);
    }
}
