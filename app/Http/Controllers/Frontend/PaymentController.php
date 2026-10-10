<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Show the payment form for a rental/equipment
     * 
     * Accepte deux workflows:
     * - Ancien (legacy): amount, description, equipment_id depuis equipment.show
     * - Nouveau (Option 2): reservation_id depuis reservation.show
     */
    public function create(Request $request)
    {
        $reservationId = $request->query('reservation_id');
        $amount = $request->query('amount', null);
        $description = $request->query('description', null);

        // Nouveau workflow: réservation d'abord, puis paiement
        if ($reservationId) {
            $reservation = Reservation::findOrFail($reservationId);
            
            // Vérifier que l'utilisateur possède cette réservation
            abort_unless($reservation->user_id === auth()->id(), 403);
            
            // Vérifier que la réservation est confirmée (règle métier: paiement uniquement si statut === 'confirmee')
            if ($reservation->statut !== 'confirmee') {
                $statusMessages = [
                    'en_attente' => 'Votre réservation est en attente de validation par l\'administration.',
                    'refusee' => 'Votre réservation a été refusée. Le paiement n\'est pas disponible.',
                    'annulee' => 'Cette réservation a été annulée. Le paiement n\'est pas possible.',
                    'terminee' => 'Cette réservation est terminée.',
                    'en_cours' => 'Cette réservation est en cours.',
                    'litige' => 'Cette réservation fait l\'objet d\'un litige. Le paiement n\'est pas disponible.',
                ];
                
                return redirect()
                    ->route('reservations.show', $reservation)
                    ->with('error', $statusMessages[$reservation->statut] ?? 'Le statut de cette réservation n\'autorise pas le paiement.');
            }
            
            // Vérifier qu'il n'y a pas déjà un paiement complété pour cette réservation
            $existingPayment = $reservation->payments()
                ->where('status', '!=', 'failed')
                ->where('status', '!=', 'refunded')
                ->first();
            
            if ($existingPayment) {
                return redirect()
                    ->route('reservations.show', $reservation)
                    ->with('warning', 'Un paiement existe déjà pour cette réservation.');
            }

            return view('frontend.payments.create', [
                'amount' => $reservation->prix_total,
                'description' => "Location de {$reservation->equipment_label} du {$reservation->date_debut->format('d/m/Y')} au {$reservation->date_fin->format('d/m/Y')}",
                'reservation_id' => $reservation->id,
                'reservation' => $reservation,
            ]);
        }

        // Legacy: workflow ancien (equipment.show)
        // Garder pour compatibilité mais rediriger vers réservation
        if (!$amount || !is_numeric($amount) || $amount <= 0) {
            return redirect()
                ->route('equipments.index')
                ->with('error', 'Montant de paiement invalide. Créez une réservation d\'abord.');
        }

        return view('frontend.payments.create', [
            'amount' => floatval($amount),
            'description' => $description ?? 'Paiement de location d\'équipement',
            'reservation_id' => null,
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
            'reservation_id' => 'nullable|exists:reservations,id|integer',
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
            'reservation_id.exists' => 'La réservation spécifiée n\'existe pas.',
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
            // Si une réservation est spécifiée, valider et utiliser son montant
            $reservation = null;
            if ($validated['reservation_id'] ?? null) {
                $reservation = Reservation::findOrFail($validated['reservation_id']);
                
                // Vérifier que l'utilisateur possède cette réservation
                abort_unless($reservation->user_id === auth()->id(), 403);
                
                // Vérifier que la réservation est confirmée (règle métier: paiement uniquement si statut === 'confirmee')
                if ($reservation->statut !== 'confirmee') {
                    $statusMessages = [
                        'en_attente' => 'Votre réservation est en attente de validation par l\'administration.',
                        'refusee' => 'Votre réservation a été refusée. Le paiement n\'est pas disponible.',
                        'annulee' => 'Cette réservation a été annulée. Le paiement n\'est pas possible.',
                        'terminee' => 'Cette réservation est terminée.',
                        'en_cours' => 'Cette réservation est en cours.',
                        'litige' => 'Cette réservation fait l\'objet d\'un litige. Le paiement n\'est pas disponible.',
                    ];
                    
                    return redirect()
                        ->route('reservations.show', $reservation)
                        ->with('error', $statusMessages[$reservation->statut] ?? 'Le statut de cette réservation n\'autorise pas le paiement.')
                        ->withInput();
                }
                
                // Vérifier qu'il n'y a pas déjà un paiement complété pour cette réservation
                $existingPayment = $reservation->payments()
                    ->where('status', '!=', 'failed')
                    ->where('status', '!=', 'refunded')
                    ->first();
                
                if ($existingPayment) {
                    return redirect()
                        ->back()
                        ->with('error', 'Un paiement existe déjà pour cette réservation.')
                        ->withInput();
                }
                
                // Forcer le montant depuis la réservation (sécurité : empêcher la manipulation)
                $validated['amount'] = $reservation->prix_total;
            }

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
                'reservation_id' => $validated['reservation_id'] ?? null,
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

            // Create contract if payment is confirmed (status='paid') and reservation exists
            // Create contract for both card (paid) and bank transfer (pending) payments
            if (in_array($paymentStatus, ['paid', 'pending']) && $reservation) {
                $this->createContractForReservation($reservation, $payment);
            }

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
        } catch (\Throwable $e) {
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

        $payment->load('transactions', 'reservation');

        // Get the contract associated with the payment's reservation (if it exists)
        $contract = null;
        if ($payment->reservation) {
            // Get the first contract for this reservation (typically there's only one)
            $contract = $payment->reservation->contract()->first();
            // Verify the contract belongs to the authenticated user
            if ($contract && $contract->user_id !== auth()->id()) {
                $contract = null; // Prevent unauthorized access
            }
        }

        return view('frontend.payments.show', [
            'payment' => $payment,
            'contract' => $contract,
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

    /**
     * Create a contract for the reservation after payment is confirmed.
     * Only creates if no contract exists yet (prevents duplicates).
     */
    private function createContractForReservation($reservation, $payment)
    {
        // Check if contract already exists for this reservation
        if ($reservation->contract()->exists()) {
            return;
        }

        // Create the contract
        Contract::create([
            'reservation_id' => $reservation->id,
            'user_id' => $reservation->user_id,
            'equipment_id' => $reservation->equipment_id,
            'contract_number' => Contract::generateContractNumber(),
            'start_date' => $reservation->date_debut,
            'end_date' => $reservation->date_fin,
            'amount' => $payment->amount,
            'status' => 'active',
            'terms_and_conditions' => $this->generateTermsAndConditions($reservation),
        ]);
    }

    /**
     * Generate standard terms and conditions for a contract.
     */
    private function generateTermsAndConditions($reservation)
    {
        return <<<EOT
TERMS AND CONDITIONS

1. Equipment Rental Period
The rental period begins on {$reservation->date_debut} and ends on {$reservation->date_fin}.

2. Equipment Condition
The renter agrees to use the equipment in accordance with all manufacturer specifications and instructions provided.

3. Liability
The renter assumes full responsibility for the equipment during the rental period and agrees to return it in the same condition as received.

4. Cancellation Policy
Cancellations must be made 48 hours in advance. Late cancellations will be charged in full.

5. Dispute Resolution
Any disputes arising from this rental agreement will be resolved through the SolarShare support system.

6. Applicable Law
This agreement is governed by the laws and regulations of the jurisdiction where the service is provided.
EOT;
    }
}

