<?php

namespace App\Http\Controllers\Admin;

use App\Events\CompensationCodeCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompensationCodeRequest;
use App\Http\Requests\UpdateCompensationCodeRequest;
use App\Models\CompensationCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompensationCodeController extends Controller
{
    /**
     * Display a listing of compensation codes.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CompensationCode::class);

        $query = CompensationCode::with(['user', 'reclamation', 'createdBy']);

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Search by code or user email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        $compensationCodes = $query->orderByDesc('created_at')->paginate(15);

        return view('backend.compensation-codes.index', compact('compensationCodes'));
    }

    /**
     * Show the form for creating a new compensation code.
     */
    public function create()
    {
        $this->authorize('create', CompensationCode::class);

        $users = User::where('role', 'client')->get();

        return view('backend.compensation-codes.create', compact('users'));
    }

    /**
     * Store a newly created compensation code in storage.
     */
    public function store(StoreCompensationCodeRequest $request)
    {
        $this->authorize('create', CompensationCode::class);

        $validated = $request->validated();
        $validated['created_by'] = Auth::id();
        $validated['code'] = CompensationCode::generateCode();

        $compensationCode = CompensationCode::create($validated);

        // Dispatch event after successful creation
        CompensationCodeCreated::dispatch($compensationCode);

        return redirect()->route('admin.compensation-codes.show', $compensationCode->id)
            ->with('success', 'Code de compensation créé avec succès.');
    }

    /**
     * Display the specified compensation code.
     */
    public function show(CompensationCode $compensationCode)
    {
        $this->authorize('view', $compensationCode);

        $compensationCode->load(['user', 'reclamation', 'createdBy']);

        return view('backend.compensation-codes.show', compact('compensationCode'));
    }

    /**
     * Show the form for editing the specified compensation code.
     */
    public function edit(CompensationCode $compensationCode)
    {
        $this->authorize('update', $compensationCode);

        $users = User::where('role', 'client')->get();

        return view('backend.compensation-codes.edit', compact('compensationCode', 'users'));
    }

    /**
     * Update the specified compensation code in storage.
     *
     * Note: Can only update unused codes (uses_count = 0)
     */
    public function update(UpdateCompensationCodeRequest $request, CompensationCode $compensationCode)
    {
        $this->authorize('update', $compensationCode);

        if ($compensationCode->uses_count > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de modifier un code déjà utilisé.');
        }

        $validated = $request->validated();
        $compensationCode->update($validated);

        return redirect()->route('admin.compensation-codes.show', $compensationCode->id)
            ->with('success', 'Code de compensation mis à jour avec succès.');
    }

    /**
     * Delete the specified compensation code.
     *
     * Note: Can only delete unused codes (uses_count = 0)
     */
    public function destroy(CompensationCode $compensationCode)
    {
        $this->authorize('delete', $compensationCode);

        if ($compensationCode->uses_count > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer un code déjà utilisé.');
        }

        $code = $compensationCode->code;
        $compensationCode->delete();

        return redirect()->route('admin.compensation-codes.index')
            ->with('success', "Code de compensation {$code} supprimé avec succès.");
    }

    /**
     * Toggle the active status of a compensation code.
     */
    public function toggleActive(CompensationCode $compensationCode)
    {
        $this->authorize('update', $compensationCode);

        $compensationCode->update([
            'is_active' => !$compensationCode->is_active,
        ]);

        $status = $compensationCode->is_active ? 'activé' : 'désactivé';

        return redirect()->back()
            ->with('success', "Code de compensation {$status} avec succès.");
    }
}
