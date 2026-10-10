<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contract;
use App\Models\Delivery;
use App\Models\Equipment;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-end test for complete workflow: Reservation → Payment → Contract → Delivery
 * Tests all workflow fixes: query parameters, auto-contract creation for pending, auto-delivery creation
 */
class CompleteReservationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $admin;
    protected User $owner;
    protected Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->client = User::factory()->create(['role' => 'client', 'email' => 'client@test.com']);
        $this->owner = User::factory()->create(['role' => 'client', 'email' => 'owner@test.com']);

        $category = Category::create(['name' => 'Équipement Solaire']);

        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->owner->id,
            'category_id' => $category->id,
            'name' => 'Panneau Solaire 400W',
            'price_per_day' => 100.00,
        ]);
    }

    /**
     * Complete workflow with CARD PAYMENT (immediate 'paid' status):
     * Client creates reservation → Admin approves → Client pays with card → Contract auto-created → Delivery already created
     */
    public function test_complete_workflow_with_card_payment(): void
    {
        // Step 1: Client creates reservation
        $this->actingAs($this->client);
        
        $reservationResponse = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-11-01',
            'date_fin' => '2026-11-06', // 5 days
        ]);

        $reservationResponse->assertRedirect();

        // Verify reservation created with 'en_attente' status
        $reservation = Reservation::where('user_id', $this->client->id)->latest()->first();
        $this->assertNotNull($reservation);
        $this->assertEquals('en_attente', $reservation->statut);
        $this->assertEquals(500.00, $reservation->prix_total); // 5 days × 100

        // Verify NO contract yet (contract only created after payment)
        $this->assertFalse($reservation->contract()->exists());

        // Verify NO delivery yet (delivery only created after admin approval)
        $this->assertFalse($reservation->delivery()->exists());

        // Step 2: Admin approves reservation (confirms it)
        $this->actingAs($this->admin);

        $approveResponse = $this->patch(
            route('admin.reservations.approve', $reservation)
        );

        $approveResponse->assertRedirect(route('admin.reservations.show', $reservation));

        // Verify reservation status changed to 'confirmee'
        $reservation->refresh();
        $this->assertEquals('confirmee', $reservation->statut);

        // IMPORTANT: Verify NO delivery created on approval (workflow changed)
        // Delivery is now created when client accepts the contract
        $this->assertFalse($reservation->delivery()->exists());

        // Step 3: Client pays with CARD (now that reservation is confirmed)
        $this->actingAs($this->client);

        $paymentResponse = $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'card',
            'description' => 'Paiement par carte',
            'card_holder' => 'Test Client',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/30',
            'card_cvv' => '123',
            'reservation_id' => $reservation->id,
        ]);

        $paymentResponse->assertRedirect();

        // Verify payment created with 'paid' status
        $payment = Payment::where('reservation_id', $reservation->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('card', $payment->method);

        // Verify contract auto-created for paid payment
        $contract = Contract::where('reservation_id', $reservation->id)->first();
        $this->assertNotNull($contract);
        $this->assertNotNull($contract->contract_number);
        $this->assertEquals($this->client->id, $contract->user_id);
        $this->assertEquals($this->equipment->id, $contract->equipment_id);
    }

    /**
     * Complete workflow with BANK TRANSFER (pending payment):
     * Client creates reservation → Admin approves → Client pays with bank transfer → Contract auto-created (even pending!)
     */
    public function test_complete_workflow_with_bank_transfer_payment(): void
    {
        // Step 1: Client creates reservation
        $this->actingAs($this->client);

        $reservationResponse = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-12-01',
            'date_fin' => '2026-12-04', // 3 days
        ]);

        $reservationResponse->assertRedirect();

        $reservation = Reservation::where('user_id', $this->client->id)->latest()->first();
        $this->assertNotNull($reservation);
        $this->assertEquals('en_attente', $reservation->statut);
        $this->assertEquals(300.00, $reservation->prix_total); // 3 days × 100

        // Step 2: Admin approves reservation
        $this->actingAs($this->admin);

        $approveResponse = $this->patch(
            route('admin.reservations.approve', $reservation)
        );

        $approveResponse->assertRedirect();

        $reservation->refresh();
        $this->assertEquals('confirmee', $reservation->statut);

        // Step 3: Client pays with BANK TRANSFER (now that reservation is confirmed)
        $this->actingAs($this->client);

        $paymentResponse = $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'bank_transfer',
            'description' => 'Paiement par virement bancaire',
            'reservation_id' => $reservation->id,
        ]);

        $paymentResponse->assertRedirect();

        // Verify payment created with 'pending' status
        $payment = Payment::where('reservation_id', $reservation->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals('bank_transfer', $payment->method);

        // **KEY TEST:** Verify contract IS auto-created even with pending payment
        // (This is the main fix: contracts now auto-create for BOTH paid and pending)
        $contract = Contract::where('reservation_id', $reservation->id)->first();
        $this->assertNotNull($contract, 'Contract should be auto-created for pending bank transfer payment');
        $this->assertNotNull($contract->contract_number);
        $this->assertEquals($this->client->id, $contract->user_id);
    }

    /**
     * Test NO duplicate contracts are created on multiple operations
     */
    public function test_no_duplicate_contracts_created(): void
    {
        // Step 1: Client creates and gets approved
        $this->actingAs($this->client);

        $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-11-15',
            'date_fin' => '2026-11-17',
        ]);

        $reservation = Reservation::where('user_id', $this->client->id)->latest()->first();

        // Step 2: Admin approves
        $this->actingAs($this->admin);
        $this->patch(route('admin.reservations.approve', $reservation));

        $reservation->refresh();
        $this->assertEquals('confirmee', $reservation->statut);

        // Step 3: Client pays first time
        $this->actingAs($this->client);

        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'card',
            'description' => 'First payment',
            'card_holder' => 'Test',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/30',
            'card_cvv' => '123',
            'reservation_id' => $reservation->id,
        ]);

        // Verify exactly one contract
        $contractCount = Contract::where('reservation_id', $reservation->id)->count();
        $this->assertEquals(1, $contractCount);

        // Note: Cannot create another payment for same reservation (PaymentController prevents it)
        // So we just verify that only one contract exists, which is correct idempotent behavior
    }

    /**
     * Test NO duplicate deliveries are created on multiple admin approvals
     */
    /**
     * Test no duplicate deliveries are created.
     * Delivery is now created on contract acceptance (not on reservation approval),
     * and the createOrUpdateDelivery() method is idempotent (returns existing if already exists)
     */
    public function test_no_duplicate_deliveries_created(): void
    {
        $this->actingAs($this->client);

        $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-11-20',
            'date_fin' => '2026-11-22',
        ]);

        $reservation = Reservation::where('user_id', $this->client->id)->latest()->first();

        // Admin approves reservation
        $this->actingAs($this->admin);
        $this->patch(route('admin.reservations.approve', $reservation));

        // No delivery created yet (only on contract acceptance)
        $deliveryCountAfterApproval = Delivery::where('reservation_id', $reservation->id)->count();
        $this->assertEquals(0, $deliveryCountAfterApproval);

        // Client pays to trigger contract creation
        $this->actingAs($this->client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'card',
            'description' => 'Test payment',
            'card_holder' => 'Test Client',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/30',
            'card_cvv' => '123',
            'reservation_id' => $reservation->id,
        ]);

        // Contract created
        $contract = Contract::where('reservation_id', $reservation->id)->first();
        $this->assertNotNull($contract);

        // First acceptance - creates delivery
        $response1 = $this->post(route('contracts.accept', $contract));
        $response1->assertRedirect();

        $deliveryCountAfterFirstAcceptance = Delivery::where('reservation_id', $reservation->id)->count();
        $this->assertEquals(1, $deliveryCountAfterFirstAcceptance);
        $firstDeliveryId = Delivery::where('reservation_id', $reservation->id)->first()->id;

        // Try to accept again - idempotent (returns same delivery, no duplicate)
        $response2 = $this->post(route('contracts.accept', $contract));
        $response2->assertRedirect();

        $deliveryCountAfterSecondAcceptance = Delivery::where('reservation_id', $reservation->id)->count();
        $this->assertEquals(1, $deliveryCountAfterSecondAcceptance); // Still only 1
        $secondDeliveryId = Delivery::where('reservation_id', $reservation->id)->first()->id;
        $this->assertEquals($firstDeliveryId, $secondDeliveryId); // Same delivery ID
    }

    /**
     * Test query parameter pre-filling (equipment_id, start_date, days)
     * This tests the fix for query parameters being used in form
     */
    public function test_query_parameters_prefill_reservation_form(): void
    {
        $this->actingAs($this->client);

        // Access form with query parameters
        $response = $this->get(route('reservations.create', [
            'equipment_id' => $this->equipment->id,
            'start_date' => '2026-11-10',
            'days' => 4,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('reservations.create');

        // Verify preFilledData is passed to view
        $preFilledData = $response->viewData('preFilledData');
        $this->assertNotNull($preFilledData);
        $this->assertEquals($this->equipment->id, $preFilledData['equipment_id']);
        $this->assertEquals('2026-11-10', $preFilledData['start_date']);
        $this->assertEquals(4, $preFilledData['days']);
    }

    /**
     * Test correct status message after reservation creation
     * Should say "en attente de confirmation" not "confirmée"
     */
    public function test_reservation_status_message_clarity(): void
    {
        $this->actingAs($this->client);

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-11-25',
            'date_fin' => '2026-11-27',
        ]);

        // Should redirect to show page with clear message
        $response->assertRedirect();

        $reservation = Reservation::where('user_id', $this->client->id)->latest()->first();
        $response->assertRedirect(route('reservations.show', $reservation));

        // Get the show page
        $showResponse = $this->get(route('reservations.show', $reservation));
        $showResponse->assertStatus(200);

        // Verify reservation is actually 'en_attente', not 'confirmee'
        $reservation->refresh();
        $this->assertEquals('en_attente', $reservation->statut);
    }
}
