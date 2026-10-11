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
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * ContractAcceptanceTest
 *
 * Tests for the new workflow where clients accept contracts to trigger delivery creation.
 * 
 * Workflow:
 * 1. Admin approves reservation → Reservation confirmed, NO delivery created
 * 2. Client pays → Payment confirmed, Contract auto-created
 * 3. Client accepts contract → Contract.accepted_at set, Delivery created with status='prete'
 * 4. Delivery visible in "Mes livraisons" for client
 * 5. Admin manages delivery status transitions
 */
class ContractAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $client;
    private User $provider;
    private Equipment $equipment;
    private Reservation $reservation;
    private Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();

        // Create provider (equipment owner)
        $this->provider = User::factory()->create([
            'role' => 'provider',
        ]);

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Create client user
        $this->client = User::factory()->create([
            'role' => 'client',
        ]);

        // Create category for equipment
        $category = \App\Models\Category::create([
            'name' => 'Énergie Solaire',
            'description' => 'Équipements d\'énergie solaire',
        ]);

        // Create equipment (owned by provider, in a category)
        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->provider->id,
            'category_id' => $category->id,
            'price_per_day' => 100,
        ]);

        // Create and approve reservation
        $this->reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-11-01',
            'date_fin' => '2026-11-06',
            'statut' => 'en_attente',
            'prix_total' => 500,
        ]);

        // Admin approves the reservation
        $this->actingAs($this->admin);
        $this->patch(route('admin.reservations.approve', $this->reservation));
        $this->reservation->refresh();

        // Client pays for the reservation
        $this->actingAs($this->client);
        $this->post(route('payments.store'), [
            'amount' => $this->reservation->prix_total,
            'method' => 'card',
            'description' => 'Test payment',
            'card_holder' => 'Test Client',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/30',
            'card_cvv' => '123',
            'reservation_id' => $this->reservation->id,
        ]);

        // Get the contract created by payment
        $this->contract = Contract::where('reservation_id', $this->reservation->id)->first();
    }

    /**
     * Test: Client can accept contract
     */
    public function test_client_can_accept_contract(): void
    {
        // Verify contract exists but is not accepted yet
        $this->assertNotNull($this->contract);
        $this->assertFalse($this->contract->isAccepted());
        $this->assertNull($this->contract->accepted_at);

        // Client accepts the contract
        $response = $this->actingAs($this->client)
            ->post(route('contracts.accept', $this->contract));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Contrat accepté avec succès! Votre livraison est maintenant disponible dans "Mes livraisons".');

        // Verify contract is now accepted
        $this->contract->refresh();
        $this->assertTrue($this->contract->isAccepted());
        $this->assertNotNull($this->contract->accepted_at);
    }

    /**
     * Test: Contract not accepted by default
     */
    public function test_contract_not_accepted_by_default(): void
    {
        $this->assertFalse($this->contract->isAccepted());
        $this->assertNull($this->contract->accepted_at);
    }

    /**
     * Test: accepted_at timestamp is recorded
     */
    public function test_accepted_at_timestamp_recorded(): void
    {
        $before = now()->subSecond();

        $this->actingAs($this->client)
            ->post(route('contracts.accept', $this->contract));

        $this->contract->refresh();
        $after = now()->addSecond();

        $this->assertNotNull($this->contract->accepted_at);
        $this->assertTrue($this->contract->accepted_at >= $before);
        $this->assertTrue($this->contract->accepted_at <= $after);
    }

    /**
     * Test: Accepting contract creates delivery
     */
    public function test_accepting_contract_creates_delivery(): void
    {
        // No delivery before acceptance
        $this->assertFalse($this->reservation->delivery()->exists());

        // Accept contract
        $this->actingAs($this->client)
            ->post(route('contracts.accept', $this->contract));

        // Delivery should now exist
        $this->assertTrue($this->reservation->delivery()->exists());
        $delivery = $this->reservation->delivery;
        $this->assertNotNull($delivery);
    }

    /**
     * Test: Delivery created with status='prete' (Ready), not 'a_preparer'
     */
    public function test_delivery_created_with_status_prete(): void
    {
        $this->actingAs($this->client)
            ->post(route('contracts.accept', $this->contract));

        $delivery = $this->reservation->delivery;
        $this->assertEquals('prete', $delivery->status);
    }

    /**
     * Test: Delivery dates match reservation dates
     */
    public function test_delivery_dates_match_reservation(): void
    {
        $this->actingAs($this->client)
            ->post(route('contracts.accept', $this->contract));

        $delivery = $this->reservation->delivery;
        $this->assertEquals($this->reservation->date_debut, $delivery->planned_delivery_date);
        $this->assertEquals($this->reservation->date_fin, $delivery->planned_return_date);
    }

    /**
     * Test: Multiple acceptances are idempotent (no duplicate delivery)
     */
    public function test_multiple_acceptances_idempotent(): void
    {
        $this->actingAs($this->client);

        // First acceptance - creates delivery
        $response1 = $this->post(route('contracts.accept', $this->contract));
        $response1->assertRedirect();
        $this->contract->refresh();
        $delivery1 = $this->reservation->delivery;
        $delivery1Id = $delivery1->id;

        // Second acceptance - should return same delivery, show info message
        $response2 = $this->post(route('contracts.accept', $this->contract));
        $response2->assertRedirect();
        $this->contract->refresh();
        $delivery2 = $this->reservation->delivery;

        // Same delivery ID - idempotent
        $this->assertEquals($delivery1Id, $delivery2->id);

        // No extra deliveries created
        $deliveryCount = Delivery::where('reservation_id', $this->reservation->id)->count();
        $this->assertEquals(1, $deliveryCount);
    }

    /**
     * Test: Client cannot accept other users' contracts
     */
    public function test_client_cannot_accept_others_contracts(): void
    {
        $otherClient = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($otherClient)
            ->post(route('contracts.accept', $this->contract));

        $response->assertStatus(403);

        // Original client's contract should still not be accepted
        $this->contract->refresh();
        $this->assertFalse($this->contract->isAccepted());
    }

    /**
     * Test: Unauthenticated user cannot accept contract
     */
    public function test_unauthenticated_user_cannot_accept_contract(): void
    {
        // Ensure we're not authenticated
        auth()->logout();
        
        $response = $this->post(route('contracts.accept', $this->contract));

        $response->assertRedirect(route('login'));

        $this->contract->refresh();
        $this->assertFalse($this->contract->isAccepted());
    }

    /**
     * Test: Delivery visible to client in "Mes livraisons" after acceptance
     */
    public function test_delivery_visible_to_client_after_acceptance(): void
    {
        // Accept contract
        $this->actingAs($this->client)
            ->post(route('contracts.accept', $this->contract));

        // Client can see delivery in their list
        $response = $this->actingAs($this->client)
            ->get(route('deliveries.index'));

        $response->assertStatus(200);
        $response->assertViewHas('deliveries');
        
        $deliveries = $response->original->getData()['deliveries'];
        $delivery = $this->reservation->delivery;
        
        // Verify the delivery is in the list
        $this->assertTrue(
            $deliveries->contains('id', $delivery->id),
            'Delivery should be visible in client\'s delivery list'
        );
    }

    /**
     * Test: Delivery initial status allows only valid transitions
     */
    public function test_delivery_status_transitions_from_prete(): void
    {
        $this->actingAs($this->client)
            ->post(route('contracts.accept', $this->contract));

        $delivery = $this->reservation->delivery;

        // Verify initial status is 'prete'
        $this->assertEquals('prete', $delivery->status);

        // Verify valid transitions
        $validTransitions = $delivery->getValidTransitions();
        $this->assertContains('remise_au_client', $validTransitions);

        // Verify no 'a_preparer' in valid transitions (removed in new workflow)
        $this->assertNotContains('a_preparer', $validTransitions);
    }

    /**
     * Test: Admin can transition delivery from 'prete' to 'remise_au_client'
     */
    public function test_admin_can_transition_delivery_from_prete_to_remise_au_client(): void
    {
        // Client accepts contract
        $this->actingAs($this->client)
            ->post(route('contracts.accept', $this->contract));

        $delivery = $this->reservation->delivery;
        $this->assertEquals('prete', $delivery->status);

        // Admin transitions to 'remise_au_client'
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.deliveries.updateStatus', $delivery), [
                'status' => 'remise_au_client',
                'notes' => 'Equipment handed to client',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify status changed and date recorded
        $delivery->refresh();
        $this->assertEquals('remise_au_client', $delivery->status);
        $this->assertNotNull($delivery->actual_delivery_date);
    }

    /**
     * Test: Contract acceptance with complete workflow
     */
    public function test_complete_workflow_from_reservation_to_delivery(): void
    {
        // Initial state: Reservation confirmed, no delivery
        $this->assertEquals('confirmee', $this->reservation->statut);
        $this->assertFalse($this->reservation->delivery()->exists());

        // Contract exists and not accepted yet
        $this->assertNotNull($this->contract);
        $this->assertFalse($this->contract->isAccepted());

        // Client accepts contract
        $this->actingAs($this->client)
            ->post(route('contracts.accept', $this->contract));

        // Verify contract is accepted
        $this->contract->refresh();
        $this->assertTrue($this->contract->isAccepted());

        // Verify delivery created with correct status and dates
        $delivery = $this->reservation->delivery;
        $this->assertNotNull($delivery);
        $this->assertEquals('prete', $delivery->status);
        $this->assertEquals($this->reservation->date_debut, $delivery->planned_delivery_date);
        $this->assertEquals($this->reservation->date_fin, $delivery->planned_return_date);

        // Admin can now manage delivery
        $this->actingAs($this->admin)
            ->patch(route('admin.deliveries.updateStatus', $delivery), [
                'status' => 'remise_au_client',
            ]);

        $delivery->refresh();
        $this->assertEquals('remise_au_client', $delivery->status);
        $this->assertNotNull($delivery->actual_delivery_date);
    }
}
