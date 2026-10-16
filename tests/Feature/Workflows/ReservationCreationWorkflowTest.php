<?php

namespace Tests\Feature\Workflows;

use App\Events\ReservationCreated;
use App\Models\Reservation;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ReservationCreationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $admin;
    protected Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->client = User::factory()->create(['role' => 'client']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        // Create test equipment (owned by someone else)
        $provider = User::factory()->create(['role' => 'provider']);
        $this->equipment = Equipment::factory()->create(['user_id' => $provider->id]);
    }

    /**
     * Test that ReservationCreated event is dispatched when client creates reservation
     */
    public function test_reservation_created_event_is_dispatched()
    {
        Event::fake();

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->addDays(5)->format('Y-m-d'),
                'date_fin' => now()->addDays(10)->format('Y-m-d'),
            ]);

        // Verify event was dispatched
        Event::assertDispatched(ReservationCreated::class);
    }

    /**
     * Test that ReservationCreated event has correct payload
     */
    public function test_reservation_created_event_has_correct_payload()
    {
        Event::fake();

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->addDays(5)->format('Y-m-d'),
                'date_fin' => now()->addDays(10)->format('Y-m-d'),
            ]);

        Event::assertDispatched(ReservationCreated::class, function ($event) {
            $this->assertNotNull($event->reservation);
            $this->assertEquals($this->client->id, $event->reservation->user_id);
            $this->assertEquals($this->equipment->id, $event->reservation->equipment_id);
            return true;
        });
    }

    /**
     * Test that reservation is persisted in database with correct status
     */
    public function test_reservation_is_persisted_with_correct_status()
    {
        Event::fake();

        $response = $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->addDays(5)->format('Y-m-d'),
                'date_fin' => now()->addDays(10)->format('Y-m-d'),
            ]);

        // Verify reservation was created with correct status
        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'en_attente',
        ]);
    }

    /**
     * Test that price is correctly calculated
     */
    public function test_reservation_price_is_correctly_calculated()
    {
        Event::fake();

        $startDate = now()->addDays(5);
        $endDate = now()->addDays(10); // 5 days
        $pricePerDay = $this->equipment->price_per_day ?? 100;

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => $startDate->format('Y-m-d'),
                'date_fin' => $endDate->format('Y-m-d'),
            ]);

        $reservation = Reservation::where('user_id', $this->client->id)->latest()->first();
        $expectedPrice = 5 * $pricePerDay; // 5 days

        $this->assertEquals($expectedPrice, $reservation->prix_total);
    }

    /**
     * Test that event broadcasts to admin.reservations channel
     */
    public function test_event_broadcasts_to_admin_reservations_channel()
    {
        // This test verifies the event object contains correct broadcast channel
        $reservation = Reservation::factory()->create(['user_id' => $this->client->id]);
        $event = new ReservationCreated($reservation);

        $channels = $event->broadcastOn();
        $channelNames = array_map(fn($c) => $c->name, $channels);

        $this->assertContains('admin.reservations', $channelNames);
    }

    /**
     * Test that event name is correct for frontend listener
     */
    public function test_event_broadcast_name_is_correct()
    {
        $reservation = Reservation::factory()->create(['user_id' => $this->client->id]);
        $event = new ReservationCreated($reservation);

        $this->assertEquals('reservation-created', $event->broadcastAs());
    }

    /**
     * Test that broadcast data includes required fields
     */
    public function test_broadcast_data_includes_required_fields()
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
        ]);
        $reservation->load('user', 'equipment');

        $event = new ReservationCreated($reservation);
        $data = $event->broadcastWith();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('equipment', $data);
        $this->assertArrayHasKey('prix_total', $data);
        $this->assertArrayHasKey('date_debut', $data);
        $this->assertArrayHasKey('date_fin', $data);
        $this->assertEquals($reservation->id, $data['id']);
    }

    /**
     * Test that multiple reservations can be created without conflicts
     */
    public function test_multiple_reservations_do_not_conflict()
    {
        Event::fake();

        // Create multiple reservations for different clients
        $client2 = User::factory()->create(['role' => 'client']);

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->addDays(5)->format('Y-m-d'),
                'date_fin' => now()->addDays(10)->format('Y-m-d'),
            ]);

        $this->actingAs($client2)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->addDays(15)->format('Y-m-d'),
                'date_fin' => now()->addDays(20)->format('Y-m-d'),
            ]);

        // Verify both were created
        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
        ]);

        $this->assertDatabaseHas('reservations', [
            'user_id' => $client2->id,
            'equipment_id' => $this->equipment->id,
        ]);
    }

    /**
     * Test that compensation code is applied if provided
     */
    public function test_compensation_code_is_applied_if_provided()
    {
        Event::fake();

        // Create a compensation code for the client
        $code = \App\Models\CompensationCode::factory()->create([
            'user_id' => $this->client->id,
            'discount_percentage' => 10,
        ]);

        $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => now()->addDays(5)->format('Y-m-d'),
                'date_fin' => now()->addDays(10)->format('Y-m-d'),
                'compensation_code' => $code->code,
            ]);

        $reservation = Reservation::where('user_id', $this->client->id)->latest()->first();
        $this->assertNotNull($reservation->compensation_code_id);
    }
}
