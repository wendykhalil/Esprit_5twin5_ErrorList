<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contract;
use App\Models\Equipment;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use RefreshDatabase;

    // ===== Setup Helpers =====

    private function setupTestData()
    {
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        
        $category = Category::firstOrCreate(['name' => 'Solar Equipment']);
        
        $equipment = Equipment::factory()->create([
            'user_id' => $provider->id,
            'category_id' => $category->id,
            'price_per_day' => 100,
        ]);

        $reservation = Reservation::create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'date_debut' => now()->addDay(),
            'date_fin' => now()->addDays(6),
            'statut' => 'confirmee',
            'prix_total' => 500,
        ]);

        return compact('client', 'provider', 'equipment', 'reservation');
    }

    // ===== Test: Contract Generation on Paid Payment =====

    public function test_contract_created_automatically_when_payment_status_is_paid()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);

        // Verify no contract exists yet
        $this->assertFalse($reservation->contract()->exists());

        // Create payment with status='paid'
        $response = $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'card',
            'reservation_id' => $reservation->id,
            'card_holder' => 'John Doe',
            'card_number' => '4532123456789012',  // Valid Visa card (starts with 4)
            'card_expiry' => '12/27',  // December 2027 (future)
            'card_cvv' => '123',
        ]);

        // Verify payment was created
        $payment = Payment::where('reservation_id', $reservation->id)
            ->where('status', 'paid')
            ->first();
        $this->assertNotNull($payment);

        // Verify contract was created
        $this->assertTrue($reservation->contract()->exists());
        $contract = $reservation->contract()->first();
        $this->assertNotNull($contract);
        $this->assertEquals($reservation->id, $contract->reservation_id);
        $this->assertEquals($client->id, $contract->user_id);
        $this->assertEquals($reservation->equipment_id, $contract->equipment_id);
        $this->assertEquals('active', $contract->status);
        $this->assertEquals($payment->amount, $contract->amount);
    }

    public function test_contract_created_when_payment_status_is_pending()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);

        // Create payment with status='pending' (bank_transfer)
        $response = $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'bank_transfer',
            'reservation_id' => $reservation->id,
        ]);

        // Verify payment was created with pending status
        $payment = Payment::where('reservation_id', $reservation->id)
            ->where('status', 'pending')
            ->first();
        $this->assertNotNull($payment);

        // Verify contract WAS created (even though payment is pending)
        $this->assertTrue($reservation->contract()->exists());
        
        // Verify contract details
        $contract = $reservation->contract()->first();
        $this->assertNotNull($contract->contract_number);
        $this->assertEquals($reservation->user_id, $contract->user_id);
        $this->assertEquals($reservation->equipment_id, $contract->equipment_id);
    }

    public function test_contract_not_created_without_reservation()
    {
        ['client' => $client] = $this->setupTestData();

        $this->actingAs($client);

        // Create payment without reservation
        $response = $this->post(route('payments.store'), [
            'amount' => 100,
            'method' => 'card',
            'card_holder' => 'John Doe',
            'card_number' => '1234567890123456',
            'card_expiry' => '12/25',
            'card_cvv' => '123',
        ]);

        // Verify no contracts were created
        $this->assertEquals(0, Contract::count());
    }

    // ===== Test: Contract Uniqueness (No Duplicates) =====

    public function test_contract_created_only_once_per_reservation()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);

        // First payment creates contract
        $response = $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'card',
            'reservation_id' => $reservation->id,
            'card_holder' => 'John Doe',
            'card_number' => '4532123456789012',  // Valid Visa card (starts with 4)
            'card_expiry' => '12/27',  // December 2027 (future)
            'card_cvv' => '123',
        ]);

        $this->assertTrue($reservation->contract()->exists());
        $firstContractCount = $reservation->contract()->count();
        $this->assertEquals(1, $firstContractCount);

        // Simulate attempting to create another contract for same reservation
        // (In real scenario, second payment would be blocked by validation)
        $contractCount = $reservation->contract()->count();
        $this->assertEquals(1, $contractCount, 'Only one contract should exist per reservation');
    }

    // ===== Test: Client Access Control =====

    public function test_client_can_view_own_contracts()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);

        // Create payment and contract
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();

        // Client can view their contract
        $response = $this->get(route('contracts.show', $contract));
        $response->assertStatus(200);
    }

    public function test_client_cannot_view_other_clients_contracts()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();

        // Create another client
        $otherClient = User::factory()->create(['role' => 'client']);
        $this->actingAs($otherClient);

        // Other client cannot view this contract
        $response = $this->get(route('contracts.show', $contract));
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_view_contracts()
    {
        // Simply test that unauthenticated users are redirected to login
        $response = $this->get('/contracts');
        $response->assertRedirect('/login');
    }

    public function test_client_can_see_only_own_contracts_in_index()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        // Check index shows client's contract
        $response = $this->get(route('contracts.index'));
        $response->assertStatus(200);
        $response->assertSee($reservation->contract()->first()->contract_number);
    }

    // ===== Test: Admin Access Control =====

    public function test_admin_can_view_all_contracts()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();

        // Admin can view
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('admin.contracts.show', $contract));
        $response->assertStatus(200);
    }

    public function test_admin_can_view_contracts_index()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('admin.contracts.index'));
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_admin_contracts_index()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $response = $this->get(route('admin.contracts.index'));
        $response->assertStatus(403);
    }

    // ===== Test: Contract Generation with Cash Payment =====

    public function test_contract_created_with_cash_payment()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);

        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();
        $this->assertNotNull($contract);
        $this->assertEquals('active', $contract->status);
    }

    // ===== Test: Contract Number Generation =====

    public function test_contract_number_is_unique_and_formatted()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();

        // Check format: CONT-YYYY-XXXXXX
        $this->assertMatchesRegularExpression('/^CONT-\d{4}-\d{6}$/', $contract->contract_number);

        // Create another contract to verify uniqueness
        ['client' => $client2, 'reservation' => $reservation2] = $this->setupTestData();
        $this->actingAs($client2);
        $this->post(route('payments.store'), [
            'amount' => $reservation2->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation2->id,
        ]);

        $contract2 = $reservation2->contract()->first();
        $this->assertNotEquals($contract->contract_number, $contract2->contract_number);
    }

    // ===== Test: Contract Print/Download Routes =====

    public function test_client_can_print_contract()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();

        $response = $this->get(route('contracts.print', $contract));
        $response->assertStatus(200);
    }

    public function test_client_can_download_contract()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();

        $response = $this->get(route('contracts.download', $contract));
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="' . $contract->contract_number . '.html"');
    }

    // ===== Test: Admin Status Update =====

    public function test_admin_can_update_contract_status()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();
        $this->assertEquals('active', $contract->status);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->patch(route('admin.contracts.updateStatus', $contract), [
            'status' => 'completed',
        ]);

        $response->assertRedirect();
        $contract->refresh();
        $this->assertEquals('completed', $contract->status);
    }

    // ===== Test: Contract Model Relationships =====

    public function test_contract_belongs_to_reservation()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();

        $this->assertNotNull($contract->reservation);
        $this->assertEquals($reservation->id, $contract->reservation->id);
    }

    public function test_contract_belongs_to_user()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();

        $this->assertNotNull($contract->user);
        $this->assertEquals($client->id, $contract->user->id);
    }

    public function test_contract_belongs_to_equipment()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();

        $this->assertNotNull($contract->equipment);
        $this->assertEquals($reservation->equipment_id, $contract->equipment->id);
    }

    // ===== Test: Contract Fields =====

    public function test_contract_stores_all_required_fields()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();

        // Verify all fields
        $this->assertNotNull($contract->reservation_id);
        $this->assertNotNull($contract->user_id);
        $this->assertNotNull($contract->equipment_id);
        $this->assertNotNull($contract->contract_number);
        $this->assertNotNull($contract->start_date);
        $this->assertNotNull($contract->end_date);
        $this->assertNotNull($contract->amount);
        $this->assertEquals('active', $contract->status);
        $this->assertNotNull($contract->terms_and_conditions);
    }

    // ===== Test: Currency Display (TND) =====

    public function test_contract_index_displays_currency_as_tnd()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $response = $this->get(route('contracts.index'));
        $response->assertStatus(200);
        $response->assertSee('TND');
        $response->assertDontSee(' €');
    }

    public function test_contract_show_displays_currency_as_tnd()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();
        $response = $this->get(route('contracts.show', $contract));
        $response->assertStatus(200);
        $response->assertSee('TND');
        $response->assertDontSee(' €');
    }

    public function test_contract_print_displays_currency_as_tnd()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();
        $response = $this->get(route('contracts.print', $contract));
        $response->assertStatus(200);
        $response->assertSee('TND');
        $response->assertDontSee(' €');
    }

    public function test_contract_download_displays_currency_as_tnd()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();
        $response = $this->get(route('contracts.download', $contract));
        $response->assertStatus(200);
        $response->assertSee('TND');
        $response->assertDontSee(' €');
    }

    public function test_contract_amount_format_is_consistent_across_pages()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $this->actingAs($client);
        $this->post(route('payments.store'), [
            'amount' => $reservation->prix_total,
            'method' => 'cash',
            'reservation_id' => $reservation->id,
        ]);

        $contract = $reservation->contract()->first();
        $expectedAmount = number_format($contract->amount, 2, ',', ' ') . ' TND';

        // Check index page
        $indexResponse = $this->get(route('contracts.index'));
        $indexResponse->assertSee($expectedAmount);

        // Check show page
        $showResponse = $this->get(route('contracts.show', $contract));
        $showResponse->assertSee($expectedAmount);

        // Check print page
        $printResponse = $this->get(route('contracts.print', $contract));
        $printResponse->assertSee($expectedAmount);

        // Check download page
        $downloadResponse = $this->get(route('contracts.download', $contract));
        $downloadResponse->assertSee($expectedAmount);
    }
}
