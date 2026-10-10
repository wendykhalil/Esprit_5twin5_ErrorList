<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Payment;
use App\Models\Contract;
use App\Models\Delivery;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Reservation13WorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Complete workflow - reservation → payment → contract → delivery
     * Simulates the actual reservation #13 workflow with database verification
     */
    public function test_complete_workflow_with_reservation_id_in_payment(): void
    {
        // Setup data
        $owner = User::factory()->create(['role' => 'client', 'email' => 'owner@test.com']);
        $client = User::factory()->create(['role' => 'client', 'email' => 'client@test.com']);
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@test.com']);

        $category = Category::create(['name' => 'Panneaux Solaires']);
        
        $equipment = Equipment::factory()->create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'name' => 'Panneau solaire portable 200W',
            'price_per_day' => 145.61,
        ]);

        // Step 1: Client creates reservation
        $this->actingAs($client);

        $reservationResponse = $this->post(route('reservations.store'), [
            'equipment_id' => $equipment->id,
            'date_debut' => '2026-11-03',
            'date_fin' => '2026-11-04',
        ]);

        $reservationResponse->assertRedirect();

        $reservation = Reservation::where('user_id', $client->id)->latest()->first();
        $this->assertNotNull($reservation);
        $this->assertEquals('en_attente', $reservation->statut);
        $this->assertEquals(145.61, $reservation->prix_total);

        echo "\n✅ Step 1: Reservation created (ID: {$reservation->id}, Status: {$reservation->statut})\n";

        // Step 2: Admin approves reservation
        $this->actingAs($admin);

        $approveResponse = $this->patch(route('admin.reservations.approve', $reservation));
        $approveResponse->assertRedirect();

        $reservation->refresh();
        $this->assertEquals('confirmee', $reservation->statut);

        // Verify delivery was auto-created
        $delivery = $reservation->delivery;
        $this->assertNotNull($delivery);
        $this->assertEquals('a_preparer', $delivery->status);

        echo "✅ Step 2: Reservation approved, delivery auto-created (ID: {$delivery->id})\n";

        // Step 3: Client pays with reservation_id
        $this->actingAs($client);

        // Access payment creation form
        $createResponse = $this->get(route('payments.create', ['reservation_id' => $reservation->id]));
        $createResponse->assertStatus(200);

        // Submit payment with reservation_id
        $paymentResponse = $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'card',
            'description' => 'Test payment for workflow',
            'card_holder' => 'Test Client',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/30',
            'card_cvv' => '123',
            'reservation_id' => $reservation->id,  // KEY: This must be passed
        ]);

        $paymentResponse->assertRedirect();

        // Verify payment was created WITH reservation_id
        $payment = Payment::where('reservation_id', $reservation->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('card', $payment->method);
        $this->assertEquals($reservation->id, $payment->reservation_id);  // CRITICAL

        echo "✅ Step 3: Payment created (ID: {$payment->id}, reservation_id: {$payment->reservation_id})\n";

        // Step 4: Verify contract was auto-created
        $contract = Contract::where('reservation_id', $reservation->id)->first();
        $this->assertNotNull($contract, 'Contract should be auto-created for paid payment');
        $this->assertNotNull($contract->contract_number);
        $this->assertEquals($client->id, $contract->user_id);
        $this->assertEquals($equipment->id, $contract->equipment_id);
        $this->assertEquals('active', $contract->status);

        echo "✅ Step 4: Contract auto-created (ID: {$contract->id}, Number: {$contract->contract_number})\n";

        // Step 5: Verify delivery still exists
        $deliveryAfterPayment = Delivery::where('reservation_id', $reservation->id)->first();
        $this->assertNotNull($deliveryAfterPayment);
        $this->assertEquals($delivery->id, $deliveryAfterPayment->id);  // Same delivery

        echo "✅ Step 5: Delivery confirmed (ID: {$deliveryAfterPayment->id})\n";

        // Final verification
        echo "\n📊 FINAL WORKFLOW STATE:\n";
        echo "   Reservation: #{$reservation->id} (status: {$reservation->statut})\n";
        echo "   Payment: #{$payment->id} (status: {$payment->status})\n";
        echo "   Contract: #{$contract->id} (number: {$contract->contract_number})\n";
        echo "   Delivery: #{$deliveryAfterPayment->id} (status: {$deliveryAfterPayment->status})\n";
    }

    /**
     * Test: Payment form includes reservation_id hidden field
     */
    public function test_payment_form_includes_reservation_id(): void
    {
        $owner = User::factory()->create(['role' => 'client']);
        $client = User::factory()->create(['role' => 'client']);
        $admin = User::factory()->create(['role' => 'admin']);

        $category = Category::create(['name' => 'Test']);
        $equipment = Equipment::factory()->create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'price_per_day' => 100,
        ]);

        $this->actingAs($client);

        // Create and approve reservation
        $reservation = Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'statut' => 'confirmee',
            'prix_total' => 100,
        ]);

        // Access payment form
        $response = $this->get(route('payments.create', ['reservation_id' => $reservation->id]));
        $response->assertStatus(200);

        // Verify hidden field is present
        $response->assertSee('reservation_id');
        $response->assertSee("value=\"{$reservation->id}\"", false);

        echo "\n✅ Payment form contains hidden reservation_id field\n";
    }
}
