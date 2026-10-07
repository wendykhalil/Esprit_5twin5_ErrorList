<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Payment::with('transactions');

        // Search by ID or description
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->input('status') !== '') {
            $query->where('status', $request->input('status'));
        }

        // Filter by method
        if ($request->filled('method') && $request->input('method') !== '') {
            $query->where('method', $request->input('method'));
        }

        $payments = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends(request()->query());

        return view('backend.payments.index', [
            'payments' => $payments,
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'method' => $request->input('method'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.payments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|gt:0',
            'method' => 'required|in:card,cash,bank_transfer',
            'status' => 'required|in:pending,paid,failed,refunded',
            'payment_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ], [
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.gt' => 'Le montant doit être supérieur à 0.',
            'method.required' => 'La méthode de paiement est obligatoire.',
            'method.in' => 'La méthode de paiement sélectionnée est invalide.',
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut sélectionné est invalide.',
            'payment_date.required' => 'La date de paiement est obligatoire.',
            'payment_date.date' => 'La date de paiement doit être une date valide.',
            'description.string' => 'La description doit être un texte.',
            'description.max' => 'La description ne doit pas dépasser 500 caractères.',
        ]);

        $payment = Payment::create($validated);

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Paiement créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        $payment->load('transactions');

        return view('backend.payments.show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        return view('backend.payments.edit', [
            'payment' => $payment,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|gt:0',
            'method' => 'required|in:card,cash,bank_transfer',
            'status' => 'required|in:pending,paid,failed,refunded',
            'payment_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ], [
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.gt' => 'Le montant doit être supérieur à 0.',
            'method.required' => 'La méthode de paiement est obligatoire.',
            'method.in' => 'La méthode de paiement sélectionnée est invalide.',
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut sélectionné est invalide.',
            'payment_date.required' => 'La date de paiement est obligatoire.',
            'payment_date.date' => 'La date de paiement doit être une date valide.',
            'description.string' => 'La description doit être un texte.',
            'description.max' => 'La description ne doit pas dépasser 500 caractères.',
        ]);

        // Validate refunded status: only allow if there are completed refund transactions
        if ($validated['status'] === 'refunded' && $payment->status !== 'refunded') {
            $payment->load('transactions');
            $refundTransactions = $payment->transactions()
                ->where('type', 'refund')
                ->where('status', 'completed')
                ->exists();

            if (!$refundTransactions) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Un paiement ne peut être marqué comme remboursé que s\'il existe une ou plusieurs transactions de remboursement.');
            }
        }

        $payment->update($validated);

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Paiement modifié avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Paiement supprimé avec succès.');
    }

    /**
     * Show the form for creating a refund transaction.
     */
    public function refundCreate(Payment $payment)
    {
        // Load transactions to calculate remaining refundable amount
        $payment->load('transactions');

        // Calculate total refunded amount
        $totalRefunded = $payment->transactions
            ->where('type', 'refund')
            ->where('status', 'completed')
            ->sum('amount');

        $remainingRefundable = $payment->amount - $totalRefunded;

        // Check if payment can be refunded
        if ($payment->status !== 'paid') {
            return redirect()
                ->route('admin.payments.show', $payment)
                ->with('error', 'Ce paiement ne peut pas être remboursé car son statut n\'est pas "Payé".');
        }

        if ($remainingRefundable <= 0) {
            return redirect()
                ->route('admin.payments.show', $payment)
                ->with('error', 'Ce paiement a déjà été complètement remboursé.');
        }

        return view('backend.payments.refund', [
            'payment' => $payment,
            'totalRefunded' => $totalRefunded,
            'remainingRefundable' => $remainingRefundable,
        ]);
    }

    /**
     * Store a newly created refund transaction.
     */
    public function refundStore(Request $request, Payment $payment)
    {
        // Load transactions to calculate remaining refundable amount
        $payment->load('transactions');

        // Verify payment can be refunded
        if ($payment->status !== 'paid') {
            return redirect()
                ->route('admin.payments.show', $payment)
                ->with('error', 'Ce paiement ne peut pas être remboursé car son statut n\'est pas "Payé".');
        }

        $totalRefunded = $payment->transactions
            ->where('type', 'refund')
            ->where('status', 'completed')
            ->sum('amount');

        $remainingRefundable = $payment->amount - $totalRefunded;

        if ($remainingRefundable <= 0) {
            return redirect()
                ->route('admin.payments.show', $payment)
                ->with('error', 'Ce paiement a déjà été complètement remboursé.');
        }

        $validated = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                function ($attribute, $value, $fail) use ($remainingRefundable) {
                    if ($value > $remainingRefundable) {
                        $fail("Le montant ne doit pas dépasser {$remainingRefundable} TND remboursable.");
                    }
                },
            ],
            'description' => 'nullable|string|max:500',
        ], [
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.gt' => 'Le montant doit être supérieur à 0.',
            'description.string' => 'La description doit être un texte.',
            'description.max' => 'La description ne doit pas dépasser 500 caractères.',
        ]);

        // Use transaction for atomic operations
        \DB::transaction(function () use ($payment, $validated) {
            // Create the refund transaction
            $payment->transactions()->create([
                'type' => 'refund',
                'amount' => $validated['amount'],
                'status' => 'completed',
                'transaction_date' => now(),
                'reference' => 'REF-' . strtoupper(\Str::random(8)) . '-' . time(),
                'description' => $validated['description'] ?? null,
            ]);

            // Recalculate total refunded after creation (refresh from DB)
            $totalRefunded = $payment->transactions()
                ->where('type', 'refund')
                ->where('status', 'completed')
                ->sum('amount');

            // Update payment status to 'refunded' only if fully refunded
            if ($totalRefunded >= $payment->amount) {
                $payment->update(['status' => 'refunded']);
            }
        });

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Remboursement créé avec succès.');
    }
}
