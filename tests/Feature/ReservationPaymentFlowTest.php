<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test du parcours Option 2: Créer Réservation d'abord, puis Payer depuis reservation.show
 *
 * Ce test vérifie que:
 * 1. Payment est lié à Reservation (reservation_id)
 * 2. Le montant du paiement est validé côté serveur (= Reservation.prix_total)
 * 3. Les doubles paiements sont prévenus (UNIQUE constraint + vérification logique)
 * 4. Transaction est auto-créée pour chaque Payment
 * 5. Seul le propriétaire de la Reservation peut payer
 */
class ReservationPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $owner;
    protected Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un utilisateur locataire (celui qui loue)
        $this->user = User::factory()->create([
            'email' => 'renter@example.com',
        ]);

        // Créer un propriétaire d'équipement
        $this->owner = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        // Créer une catégorie
        $category = \App\Models\Category::create([
            'name' => 'Panneaux Solaires',
        ]);

        // Créer un équipement avec prix par jour
        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->owner->id,
            'category_id' => $category->id,
            'name' => 'Panneau Solaire 200W',
            'price_per_day' => 50.00,
            'availability' => true,
        ]);
    }

    /**
     * Test: Créer une réservation, puis accéder à la page de paiement avec reservation_id
     */
    public function test_user_can_create_payment_from_reservation(): void
    {
        // Créer une réservation
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 250.00, // 5 jours × 50.00 TND/jour
            'statut' => 'confirmee',
        ]);

        // Accéder à la page de création de paiement avec reservation_id
        $response = $this->actingAs($this->user)
            ->get(route('payments.create', [
                'reservation_id' => $reservation->id,
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.payments.create');
        $response->assertViewHas('reservation', $reservation);
        // Vérifier que le montant est pré-rempli depuis prix_total
        $response->assertViewHas('amount', 250.00);
    }

    /**
     * Test: Paiement valide avec reservation_id valide
     */
    public function test_user_can_create_payment_with_reservation_id(): void
    {
        // Créer une réservation
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 150.00,
            'statut' => 'confirmee',
        ]);

        $this->actingAs($this->user);

        // Créer le paiement via POST avec reservation_id
        $response = $this->post(route('payments.store'), [
            'reservation_id' => $reservation->id,
            'amount' => 150.00,
            'method' => 'card',
            'description' => 'Paiement pour réservation',
            'card_holder' => 'John Doe',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/30',
            'card_cvv' => '123',
        ]);

        $response->assertRedirect();

        // Vérifier que le paiement a été créé avec reservation_id
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'reservation_id' => $reservation->id,
            'amount' => 150.00,
            'method' => 'card',
            'status' => 'paid',
        ]);

        // Vérifier que la transaction associée a été créée
        $payment = Payment::where('user_id', $this->user->id)->first();
        $this->assertNotNull($payment);
        $this->assertTrue($payment->transactions()->exists());

        $transaction = $payment->transactions()->first();
        $this->assertEquals('payment', $transaction->type);
        $this->assertEquals('completed', $transaction->status);
        $this->assertEquals(150.00, $transaction->amount);
    }

    /**
     * Test: Le montant du paiement est forcé du côté serveur (prix_total de Reservation)
     * Un client qui essaie de modifier le montant ne peut pas tricher
     */
    public function test_payment_amount_is_forced_from_reservation_prix_total(): void
    {
        // Créer une réservation avec prix_total = 200.00
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 200.00,
            'statut' => 'confirmee',
        ]);

        $this->actingAs($this->user);

        // Tenter de créer un paiement avec un montant différent (manipulation côté client)
        // Le serveur DOIT ignorer ce montant et utiliser prix_total de la réservation
        $response = $this->post(route('payments.store'), [
            'reservation_id' => $reservation->id,
            'amount' => 50.00, // Essayer de payer moins
            'method' => 'card',
            'description' => 'Tentative de triche',
            'card_holder' => 'John Doe',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/30',
            'card_cvv' => '123',
        ]);

        $response->assertRedirect();

        // Vérifier que le paiement a été créé avec le montant CORRECT (200.00, pas 50.00)
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'reservation_id' => $reservation->id,
            'amount' => 200.00, // Le vrai montant de la réservation
            'method' => 'card',
            'status' => 'paid',
        ]);

        // Vérifier qu'un paiement de 50.00 N'existe PAS
        $this->assertDatabaseMissing('payments', [
            'user_id' => $this->user->id,
            'reservation_id' => $reservation->id,
            'amount' => 50.00,
        ]);
    }

    /**
     * Test: Prévention des doubles paiements - pas deux paiements pour la même réservation
     */
    public function test_cannot_create_duplicate_payment_for_same_reservation(): void
    {
        // Créer une réservation
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 100.00,
            'statut' => 'confirmee',
        ]);

        $this->actingAs($this->user);

        // Premier paiement (succès)
        $response1 = $this->post(route('payments.store'), [
            'reservation_id' => $reservation->id,
            'amount' => 100.00,
            'method' => 'card',
            'description' => 'Premier paiement',
            'card_holder' => 'John Doe',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/30',
            'card_cvv' => '123',
        ]);

        $response1->assertRedirect();

        // Vérifier que le premier paiement existe
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'reservation_id' => $reservation->id,
            'status' => 'paid',
        ]);

        $paymentCount = Payment::where(
            'reservation_id',
            $reservation->id
        )->count();
        $this->assertEquals(1, $paymentCount);

        // Deuxième paiement (doit être rejeté ou afficher une erreur)
        $response2 = $this->post(route('payments.store'), [
            'reservation_id' => $reservation->id,
            'amount' => 100.00,
            'method' => 'card',
            'description' => 'Tentative de double paiement',
            'card_holder' => 'John Doe',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/30',
            'card_cvv' => '123',
        ]);

        // Devrait avoir une redirection (avec message d'erreur) en cas de paiement existant
        $response2->assertRedirect();
        $response2->assertSessionHas('error');

        // Vérifier qu'il n'y a toujours qu'UN SEUL paiement
        $paymentCountAfter = Payment::where(
            'reservation_id',
            $reservation->id
        )->count();
        $this->assertEquals(1, $paymentCountAfter);
    }

    /**
     * Test: Seul le propriétaire de la réservation peut payer
     */
    public function test_user_cannot_pay_for_reservation_of_another_user(): void
    {
        // Créer une réservation pour un autre utilisateur
        $anotherUser = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $anotherUser->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 100.00,
            'statut' => 'en_attente',
        ]);

        // Tentative de paiement par $this->user (pas le propriétaire)
        $response = $this->actingAs($this->user)
            ->post(route('payments.store'), [
                'reservation_id' => $reservation->id,
                'amount' => 100.00,
                'method' => 'card',
                'description' => 'Tentative de paiement pour une autre réservation',
                'card_holder' => 'John Doe',
                'card_number' => '4242424242424242',
                'card_expiry' => '12/30',
                'card_cvv' => '123',
            ]);

        // abort_unless() throws a 403 which Laravel might handle as a redirect in tests
        // Accept either 403 or a redirect back
        $this->assertTrue(
            $response->status() === 403 || 
            ($response->status() === 302 && $response->headers->has('Location')),
            "Expected 403 or 302 redirect, got {$response->status()}"
        );

        // Vérifier qu'aucun paiement n'a été créé
        $this->assertDatabaseMissing('payments', [
            'user_id' => $this->user->id,
            'reservation_id' => $reservation->id,
        ]);
    }

    /**
     * Test: Paiement par virement avec réservation
     */
    public function test_user_can_create_bank_transfer_payment_with_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 75.00,
            'statut' => 'confirmee',
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('payments.store'), [
            'reservation_id' => $reservation->id,
            'amount' => 75.00,
            'method' => 'bank_transfer',
            'description' => 'Paiement par virement',
        ]);

        $response->assertRedirect();

        // Vérifier que le paiement a été créé avec statut "pending"
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'reservation_id' => $reservation->id,
            'amount' => 75.00,
            'method' => 'bank_transfer',
            'status' => 'pending',
        ]);

        $payment = Payment::where('user_id', $this->user->id)->first();
        $transaction = $payment->transactions()->first();
        $this->assertEquals('pending', $transaction->status);
    }

    /**
     * Test: Paiement en espèces avec réservation
     */
    public function test_user_can_create_cash_payment_with_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 80.00,
            'statut' => 'confirmee',
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('payments.store'), [
            'reservation_id' => $reservation->id,
            'amount' => 80.00,
            'method' => 'cash',
            'description' => 'Paiement en espèces',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'reservation_id' => $reservation->id,
            'amount' => 80.00,
            'method' => 'cash',
            'status' => 'paid',
        ]);
    }

    /**
     * Test: Utilisateur non authentifié ne peut pas créer de paiement avec réservation
     */
    public function test_unauthenticated_user_cannot_create_payment_with_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 100.00,
            'statut' => 'en_attente',
        ]);

        $response = $this->post(route('payments.store'), [
            'reservation_id' => $reservation->id,
            'amount' => 100.00,
            'method' => 'card',
            'description' => 'Test',
            'card_holder' => 'John Doe',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/30',
            'card_cvv' => '123',
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * Test: Réservation avec paiement - vérifier la relation et les données
     */
    public function test_reservation_has_payment_relationship(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 120.00,
            'statut' => 'en_attente',
        ]);

        // Créer un paiement associé à la réservation
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'reservation_id' => $reservation->id,
            'amount' => 120.00,
            'method' => 'card',
            'status' => 'paid',
        ]);

        // Vérifier que la réservation peut accéder au paiement
        $this->assertTrue($reservation->payments()->exists());
        $this->assertEquals(1, $reservation->payments()->count());

        $retrievedPayment = $reservation->payments()->first();
        $this->assertEquals($payment->id, $retrievedPayment->id);
        $this->assertEquals(120.00, $retrievedPayment->amount);
    }

    /**
     * Test: Paiement avec reservation_id - vérifier la relation inverse
     */
    public function test_payment_has_reservation_relationship(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 90.00,
            'statut' => 'en_attente',
        ]);

        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'reservation_id' => $reservation->id,
            'amount' => 90.00,
            'status' => 'paid',
        ]);

        // Vérifier que le paiement peut accéder à la réservation
        $this->assertNotNull($payment->reservation);
        $this->assertEquals($reservation->id, $payment->reservation->id);
        $this->assertEquals('Panneau Solaire 200W', $payment->reservation->equipment->name);
    }

    /**
     * Test: Transaction est auto-créée avec chaque paiement (Reservation + Payment)
     */
    public function test_transaction_is_auto_created_for_payment_with_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 110.00,
            'statut' => 'confirmee',
        ]);

        $this->actingAs($this->user);

        $this->post(route('payments.store'), [
            'reservation_id' => $reservation->id,
            'amount' => 110.00,
            'method' => 'card',
            'description' => 'Test Transaction',
            'card_holder' => 'John Doe',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/30',
            'card_cvv' => '123',
        ]);

        $payment = Payment::where('user_id', $this->user->id)->latest()->first();

        // Vérifier qu'exactement 1 transaction a été créée
        $this->assertEquals(1, $payment->transactions()->count());

        $transaction = $payment->transactions()->first();
        $this->assertEquals('payment', $transaction->type);
        $this->assertEquals('completed', $transaction->status);
        $this->assertEquals(110.00, $transaction->amount);
        $this->assertNotEmpty($transaction->reference);
    }

    /**
     * Test: Réservation invalide ne peut pas être payée
     */
    public function test_cannot_create_payment_for_nonexistent_reservation(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('payments.store'), [
            'reservation_id' => 99999, // Réservation inexistante
            'amount' => 100.00,
            'method' => 'card',
            'description' => 'Test',
            'card_holder' => 'John Doe',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/30',
            'card_cvv' => '123',
        ]);

        // Doit être rejeté avec erreur de validation ou exception
        $response->assertSessionHasErrors('reservation_id');

        // Vérifier qu'aucun paiement n'a été créé
        $this->assertDatabaseMissing('payments', [
            'user_id' => $this->user->id,
            'reservation_id' => 99999,
        ]);
    }
}
