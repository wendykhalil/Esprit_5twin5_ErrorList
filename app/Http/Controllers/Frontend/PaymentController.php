<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Show the payment form for a rental/equipment
     */
    public function create(Request $request)
    {
        // Get amount and description from query parameters
        $amount = $request->query('amount', null);
        $description = $request->query('description', null);
        $equipment_id = $request->query('equipment_id', null);

        if (!$amount || !is_numeric($amount) || $amount <= 0) {
            return redirect()
                ->route('equipments.index')
                ->with('error', 'Montant de paiement invalide.');
        }

        return view('frontend.payments.create', [
            'amount' => floatval($amount),
            'description' => $description ?? 'Paiement de location d\'équipement',
            'equipment_id' => $equipment_id,
        ]);
    }

    /**
     * Store a new payment and transaction
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|gt:0',
            'method' => 'required|in:card,cash,bank_transfer',
            'description' => 'nullable|string|max:500',
        ], [
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.gt' => 'Le montant doit être supérieur à 0.',
            'method.required' => 'La méthode de paiement est obligatoire.',
            'method.in' => 'La méthode de paiement sélectionnée est invalide.',
            'description.string' => 'La description doit être un texte.',
            'description.max' => 'La description ne doit pas dépasser 500 caractères.',
        ]);

        try {
            // Create Payment
            $payment = Payment::create([
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'status' => 'paid', // For demo, mark as immediately paid
                'payment_date' => now(),
                'description' => $validated['description'] ?? 'Paiement de location d\'équipement',
            ]);

            // Create associated Transaction
            $reference = 'TXN-' . strtoupper(Str::random(8)) . '-' . $payment->id;
            
            Transaction::create([
                'payment_id' => $payment->id,
                'reference' => $reference,
                'type' => 'payment',
                'amount' => $validated['amount'],
                'status' => 'completed',
                'transaction_date' => now(),
            ]);

            return redirect()
                ->route('payments.show', $payment)
                ->with('success', 'Paiement effectué avec succès !');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Une erreur est survenue lors du paiement. Veuillez réessayer.')
                ->withInput();
        }
    }

    /**
     * Display payment details
     */
    public function show(Payment $payment)
    {
        $payment->load('transactions');

        return view('frontend.payments.show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Display user's payment history
     */
    public function history()
    {
        // For now, display all payments (in future, filter by auth()->id())
        $payments = Payment::with('transactions')
            ->orderBy('payment_date', 'desc')
            ->paginate(10);

        return view('frontend.payments.history', [
            'payments' => $payments,
        ]);
    }

    /**
     * Display payment invoice
     */
    public function invoice(Payment $payment)
    {
        // Load the first transaction (if exists)
        $payment->load('transactions');
        $transaction = $payment->transactions->first();

        return view('frontend.payments.invoice', [
            'payment' => $payment,
            'transaction' => $transaction,
        ]);
    }
}
