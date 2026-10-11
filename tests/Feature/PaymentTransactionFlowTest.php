<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test du parcours complet: Paiement simulé → Transaction enregistrée → Confirmation
 *
 * Ce test vérifie le flux simple pour un projet universitaire sans Stripe.
 */
class PaymentTransactionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un utilisateur authentifié
        $this->user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        // Créer un propriétaire d'équipement
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        // Créer une catégorie en accédant directement la table
        $category = \App\Models\Category::create([
            'name' => 'Panneaux Solaires',
        ]);

        // Créer un équipement
        $this->equipment = Equipment::factory()->create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'name' => 'Panneau Solaire 200W',
            'price_per_day' => 50.00,
            'availability' => true,
        ]);
    }

    /**
     * Test: Accéder à la page de création de paiement avec paramètres valides
     */
    public function test_user_can_access_payment_creation_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('payments.create', [
                'amount' => 100.00,
                'description' => 'Location de Panneau Solaire',
                'equipment_id' => $this->equipment->id,
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.payments.create');
        $response->assertViewHas('amount', 100.00);
        $response->assertViewHas('description', 'Location de Panneau Solaire');
    }

    /**
     * Test: Créer un paiement par carte (simulé)
     */
    public function test_user_can_create_card_payment(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('payments.store'), [
            'amount' => 100.00,
            'method' => 'card',
            'description' => 'Location de Panneau Solaire',
            'card_holder' => 'John Doe',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/30',
            'card_cvv' => '123',
        ]);

        // Vérifier la redirection
        $response->assertRedirect();

        // Vérifier que le paiement a été créé
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'amount' => 100.00,
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
        $this->assertEquals(100.00, $transaction->amount);
    }

    /**
     * Test: Voir les détails du paiement avec ses transactions
     */
    public function test_user_can_view_payment_with_transactions(): void
    {
        // Créer un paiement et une transaction
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 150.00,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        $transaction = Transaction::factory()->completed()->create([
            'payment_id' => $payment->id,
            'type' => 'payment',
            'amount' => 150.00,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('payments.show', $payment));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.payments.show');
        $response->assertViewHas('payment', $payment);

        // Vérifier que les transactions sont affichées dans la vue
        $response->assertSee('Transactions associées');
        $response->assertSee($transaction->reference);
        $response->assertSee('150,00'); // Format avec virgule, pas point
    }

    /**
     * Test: Créer un paiement par virement (en attente)
     */
    public function test_user_can_create_bank_transfer_payment(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('payments.store'), [
            'amount' => 75.00,
            'method' => 'bank_transfer',
            'description' => 'Location - Virement bancaire',
        ]);

        $response->assertRedirect();

        // Vérifier que le paiement a été créé avec le statut "pending"
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'amount' => 75.00,
            'method' => 'bank_transfer',
            'status' => 'pending',
        ]);

        // Vérifier que la transaction est aussi en attente
        $payment = Payment::where('user_id', $this->user->id)->first();
        $transaction = $payment->transactions()->first();
        $this->assertEquals('pending', $transaction->status);
    }

    /**
     * Test: Créer un paiement en espèces
     */
    public function test_user_can_create_cash_payment(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('payments.store'), [
            'amount' => 50.00,
            'method' => 'cash',
            'description' => 'Location - Paiement en espèces',
        ]);

        $response->assertRedirect();

        // Vérifier que le paiement a été créé avec le statut "paid"
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'amount' => 50.00,
            'method' => 'cash',
            'status' => 'paid',
        ]);
    }

    /**
     * Test: L'utilisateur non authentifié ne peut pas accéder à la création de paiement
     */
    public function test_unauthenticated_user_cannot_create_payment(): void
    {
        $response = $this->post(route('payments.store'), [
            'amount' => 100.00,
            'method' => 'card',
            'description' => 'Location',
            'card_holder' => 'John Doe',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/30',
            'card_cvv' => '123',
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * Test: L'utilisateur ne peut voir que ses propres paiements
     */
    public function test_user_cannot_view_other_users_payment(): void
    {
        $otherUser = User::factory()->create();

        $payment = Payment::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('payments.show', $payment));

        $response->assertStatus(403);
    }

    /**
     * Test: Validation des données de paiement
     */
    public function test_payment_validation_fails_with_invalid_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('payments.store'), [
            'amount' => -50.00, // Montant négatif invalide
            'method' => 'card',
            'description' => 'Test',
            'card_holder' => 'John Doe',
            'card_number' => '1111111111111111', // Numéro invalide (commence pas par 4 ou 5)
            'card_expiry' => '12/30',
            'card_cvv' => '123',
        ]);

        $response->assertSessionHasErrors();
    }

    /**
     * Test: Consulter l'historique des paiements de l'utilisateur
     */
    public function test_user_can_view_payment_history(): void
    {
        // Créer plusieurs paiements avec le statut "paid" (pour avoir une payment_date)
        Payment::factory(3)->create([
            'user_id' => $this->user->id,
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('payments.history'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.payments.history');

        // Vérifier que 3 paiements sont affichés
        $payments = $response->viewData('payments');
        $this->assertEquals(3, $payments->total());
    }

    /**
     * Test: Chaque paiement a une transaction associée
     */
    public function test_each_payment_has_associated_transaction(): void
    {
        $this->actingAs($this->user);

        $this->post(route('payments.store'), [
            'amount' => 100.00,
            'method' => 'card',
            'description' => 'Test Transaction Association',
            'card_holder' => 'John Doe',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/30',
            'card_cvv' => '123',
        ]);

        $payment = Payment::where('user_id', $this->user->id)->latest()->first();

        // Vérifier que chaque paiement a exactement 1 transaction
        $this->assertEquals(1, $payment->transactions()->count());

        // Vérifier que la transaction contient les bonnes données
        $transaction = $payment->transactions()->first();
        $this->assertEquals($payment->amount, $transaction->amount);
        $this->assertNotEmpty($transaction->reference);
    }
}
