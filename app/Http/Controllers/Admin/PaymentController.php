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
    public function index()
    {
        $payments = Payment::with('transactions')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('backend.payments.index', [
            'payments' => $payments,
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
}
