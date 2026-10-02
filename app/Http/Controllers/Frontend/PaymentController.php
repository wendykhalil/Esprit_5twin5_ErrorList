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
            'card_holder' => 'required_if:method,card|nullable|string|max:100',
            'card_number' => 'required_if:method,card|nullable|regex:/^\d{16}$/',
            'card_expiry' => 'required_if:method,card|nullable|regex:/^\d{2}\/\d{2}$/',
            'card_cvv' => 'required_if:method,card|nullable|regex:/^\d{3}$/',
        ], [
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.gt' => 'Le montant doit être supérieur à 0.',
            'method.required' => 'La méthode de paiement est obligatoire.',
            'method.in' => 'La méthode de paiement sélectionnée est invalide.',
            'description.string' => 'La description doit être un texte.',
            'description.max' => 'La description ne doit pas dépasser 500 caractères.',
            'card_holder.required_if' => 'Le nom du titulaire est obligatoire.',
            'card_holder.string' => 'Le nom du titulaire doit être un texte.',
            'card_holder.max' => 'Le nom du titulaire ne doit pas dépasser 100 caractères.',
            'card_number.required_if' => 'Le numéro de carte est obligatoire.',
            'card_number.regex' => 'Le numéro de carte doit contenir 16 chiffres.',
            'card_expiry.required_if' => 'La date d\'expiration est obligatoire.',
            'card_expiry.regex' => 'La date d\'expiration doit être au format MM/YY.',
            'card_cvv.required_if' => 'Le CVV est obligatoire.',
            'card_cvv.regex' => 'Le CVV doit contenir 3 chiffres.',
        ]);

        try {
            // Handle card payment validation
            if ($validated['method'] === 'card') {
                $this->validateCardPayment($validated);
            }

            // Determine payment status based on method
            $paymentStatus = 'paid';
            $transactionStatus = 'completed';

            if ($validated['method'] === 'bank_transfer') {
                $paymentStatus = 'pending';
                $transactionStatus = 'pending';
            }

            // Create Payment
            $payment = Payment::create([
                'user_id' => auth()->id(),
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'status' => $paymentStatus,
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
                'status' => $transactionStatus,
                'transaction_date' => now(),
            ]);

            // Success message based on method
            $successMessage = match ($validated['method']) {
                'card' => 'Simulation de paiement par carte réussie !',
                'bank_transfer' => 'Virement bancaire enregistré. En attente de confirmation.',
                'cash' => 'Paiement en espèces enregistré.',
                default => 'Paiement effectué avec succès !',
            };

            return redirect()
                ->route('payments.show', $payment)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage() ?: 'Une erreur est survenue lors du paiement. Veuillez réessayer.')
                ->withInput();
        }
    }

    /**
     * Validate card payment details (simulation only)
     */
    private function validateCardPayment(array $data)
    {
        // Validate card number format (Visa starts with 4, Mastercard with 5)
        $cardNumber = str_replace(' ', '', $data['card_number']);
        if (!preg_match('/^[45]\d{15}$/', $cardNumber)) {
            throw new \Exception('Le numéro de carte doit commencer par 4 (Visa) ou 5 (Mastercard) et contenir 16 chiffres.');
        }

        // Validate expiry date
        [$expiryMonth, $expiryYear] = explode('/', $data['card_expiry']);
        $expiryMonth = (int) $expiryMonth;
        $expiryYear = 2000 + (int) $expiryYear;
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');

        if ($expiryYear < $currentYear || ($expiryYear === $currentYear && $expiryMonth < $currentMonth)) {
            throw new \Exception('La carte a expiré. Veuillez vérifier la date d\'expiration.');
        }

        // Card holder validation (not empty)
        if (empty(trim($data['card_holder']))) {
            throw new \Exception('Le nom du titulaire est obligatoire.');
        }

        // Note: CVV is already validated by regex in the form validation
        // Data is NOT stored anywhere - validation only
    }

    /**
     * Display payment details
     */
    public function show(Payment $payment)
    {
        // Verify ownership - user can only see their own payments
        if ($payment->user_id !== auth()->id()) {
            abort(403, 'Vous n\'avez pas accès à ce paiement.');
        }

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
        // Get only the authenticated user's payments
        $payments = auth()->user()->payments()
            ->with('transactions')
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
        // Verify ownership - user can only see their own invoices
        if ($payment->user_id !== auth()->id()) {
            abort(403, 'Vous n\'avez pas accès à cette facture.');
        }

        // Load the first transaction (if exists)
        $payment->load('transactions');
        $transaction = $payment->transactions->first();

        return view('frontend.payments.invoice', [
            'payment' => $payment,
            'transaction' => $transaction,
        ]);
    }

    /**
     * Display all user invoices
     */
    public function invoices()
    {
        // Get only the authenticated user's payments (for invoices list)
        $payments = auth()->user()->payments()
            ->with('transactions')
            ->orderBy('payment_date', 'desc')
            ->paginate(10);

        return view('frontend.payments.invoices', [
            'payments' => $payments,
        ]);
    }
}
