<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Payment;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Transaction::with('payment')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('backend.transactions.index', [
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $payments = Payment::all();

        return view('backend.transactions.create', [
            'payments' => $payments,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'reference' => 'required|string|max:255|unique:transactions,reference',
            'type' => 'required|in:payment,refund',
            'amount' => 'required|numeric|gt:0',
            'status' => 'required|in:pending,completed,failed,refunded',
            'transaction_date' => 'required|date',
        ], [
            'payment_id.required' => 'Le paiement est obligatoire.',
            'payment_id.exists' => 'Le paiement sélectionné n\'existe pas.',
            'reference.required' => 'La référence est obligatoire.',
            'reference.string' => 'La référence doit être un texte.',
            'reference.max' => 'La référence ne doit pas dépasser 255 caractères.',
            'reference.unique' => 'Cette référence existe déjà.',
            'type.required' => 'Le type de transaction est obligatoire.',
            'type.in' => 'Le type de transaction sélectionné est invalide.',
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.gt' => 'Le montant doit être supérieur à 0.',
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut sélectionné est invalide.',
            'transaction_date.required' => 'La date de transaction est obligatoire.',
            'transaction_date.date' => 'La date de transaction doit être une date valide.',
        ]);

        $transaction = Transaction::create($validated);

        return redirect()
            ->route('admin.transactions.show', $transaction)
            ->with('success', 'Transaction créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $transaction->load('payment');

        return view('backend.transactions.show', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        $payments = Payment::all();

        return view('backend.transactions.edit', [
            'transaction' => $transaction,
            'payments' => $payments,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'reference' => 'required|string|max:255|unique:transactions,reference,' . $transaction->id,
            'type' => 'required|in:payment,refund',
            'amount' => 'required|numeric|gt:0',
            'status' => 'required|in:pending,completed,failed,refunded',
            'transaction_date' => 'required|date',
        ], [
            'payment_id.required' => 'Le paiement est obligatoire.',
            'payment_id.exists' => 'Le paiement sélectionné n\'existe pas.',
            'reference.required' => 'La référence est obligatoire.',
            'reference.string' => 'La référence doit être un texte.',
            'reference.max' => 'La référence ne doit pas dépasser 255 caractères.',
            'reference.unique' => 'Cette référence existe déjà.',
            'type.required' => 'Le type de transaction est obligatoire.',
            'type.in' => 'Le type de transaction sélectionné est invalide.',
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.gt' => 'Le montant doit être supérieur à 0.',
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut sélectionné est invalide.',
            'transaction_date.required' => 'La date de transaction est obligatoire.',
            'transaction_date.date' => 'La date de transaction doit être une date valide.',
        ]);

        $transaction->update($validated);

        return redirect()
            ->route('admin.transactions.show', $transaction)
            ->with('success', 'Transaction modifiée avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()
            ->route('admin.transactions.index')
            ->with('success', 'Transaction supprimée avec succès.');
    }
}
