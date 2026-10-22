<?php

namespace Tests\Feature;

use App\Events\ReservationCreated;
use App\Events\ReservationStatusChanged;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * RealTimeReservationFlowTest
 *
 * Tests the complete real-time reservation workflow:
 * 1. Client creates reservation → ReservationCreated event broadcast to admin
 * 2. Admin approves reservation → ReservationStatusChanged event broadcast to client
 *
 * This verifies that events are dispatched with correct channels and payloads.
 */
class RealTimeReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $provider;
    protected User $admin;
    protected Equipment $equipment;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create category
        $this->category = Category::create([
            'name' => 'Solar Equipment',
            'description' => 'Solar and renewable energy equipment'
        ]);

        // Create provider
        $this->provider = User::factory()->create(['role' => 'provider']);

        // Create equipment
        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->provider->id,
            'category_id' => $this->category->id,
            'price_per_day' => 100,
            'availability' => true,
            'status' => 'active'
        ]);

        // Create client and admin
        $this->client = User::factory()->create(['role' => 'client']);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Test: Client creates reservation → ReservationCreated event is dispatched
     */
    public function test_reservation_creation_dispatches_event_to_admin_channel(): void
    {
        Event::fake();

        $this->actingAs($this->client);

        $startDate = now()->addDay()->format('Y-m-d');
        $endDate = now()->addDays(2)->format('Y-m-d');

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $startDate,
            'date_fin' => $endDate,
        ]);

        // Verify the response is successful
        $this->assertTrue(
            $response->getStatusCode() === 200 || $response->getStatusCode() === 302,
            "Expected 200 or 302 status, got {$response->getStatusCode()}"
        );

        // Verify ReservationCreated event was dispatched
        Event::assertDispatched(ReservationCreated::class, function ($event) {
            return $event->reservation->user_id === $this->client->id &&
                   $event->reservation->equipment_id === $this->equipment->id;
        });

        $this->assertTrue(true);
    }

    /**
     * Test: ReservationCreated event broadcasts to correct admin channel with correct data
     */
    public function test_reservation_created_broadcasts_to_admin_channel(): void
    {
        Event::fake();

        $reservation = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'en_attente'
        ]);

        // Dispatch the event
        event(new ReservationCreated($reservation));

        // Verify event was dispatched
        Event::assertDispatched(ReservationCreated::class, function ($event) use ($reservation) {
            // Check channel is admin.reservations
            $channels = $event->broadcastOn();
            $channelNames = array_map(fn ($ch) => $ch->name, $channels);
            $hasAdminChannel = in_array('private-admin.reservations', $channelNames);

            // Check event name is 'reservation-created'
            $eventName = $event->broadcastAs();
            $correctEventName = $eventName === 'reservation-created';

            // Check broadcast data has required fields
            $broadcastData = $event->broadcastWith();
            $hasRequiredData = isset($broadcastData['id']) &&
                              isset($broadcastData['user_id']) &&
                              isset($broadcastData['equipment_id']) &&
                              isset($broadcastData['user']) &&
                              isset($broadcastData['equipment']);

            return $hasAdminChannel && $correctEventName && $hasRequiredData;
        });

        $this->assertTrue(true);
    }

    /**
     * Test: Admin approves reservation → ReservationStatusChanged event is dispatched to client
     */
    public function test_reservation_approval_dispatches_event_to_client_channel(): void
    {
        Event::fake();

        $reservation = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'en_attente'
        ]);

        $this->actingAs($this->admin);

        // Call the approve endpoint
        $response = $this->patch(route('admin.reservations.approve', $reservation));

        // Verify successful redirect
        $this->assertThat(
            $response->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(302),
                $this->equalTo(200)
            )
        );

        // Verify ReservationStatusChanged event was dispatched
        Event::assertDispatched(ReservationStatusChanged::class, function ($event) {
            return $event->newStatus === 'confirmee' &&
                   $event->oldStatus === 'en_attente';
        });

        $this->assertTrue(true);
    }

    /**
     * Test: ReservationStatusChanged broadcasts to client private channel with correct data
     */
    public function test_reservation_status_changed_broadcasts_to_user_channel(): void
    {
        Event::fake();

        $reservation = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'en_attente'
        ]);

        // Dispatch the status change event
        event(new ReservationStatusChanged($reservation, 'en_attente', 'confirmee'));

        // Verify event was dispatched
        Event::assertDispatched(ReservationStatusChanged::class, function ($event) {
            // Check channel is user.{id}.reservations
            $channels = $event->broadcastOn();
            $channelNames = array_map(fn ($ch) => $ch->name, $channels);
            $hasUserChannel = in_array('private-user.' . $this->client->id . '.reservations', $channelNames);

            // Check event name is 'reservation-status-changed'
            $eventName = $event->broadcastAs();
            $correctEventName = $eventName === 'reservation-status-changed';

            // Check broadcast data
            $broadcastData = $event->broadcastWith();
            $hasRequiredData = isset($broadcastData['id']) &&
                              isset($broadcastData['new_status']) &&
                              isset($broadcastData['old_status']) &&
                              $broadcastData['new_status'] === 'confirmee' &&
                              $broadcastData['old_status'] === 'en_attente';

            return $hasUserChannel && $correctEventName && $hasRequiredData;
        });

        $this->assertTrue(true);
    }

    /**
     * Test: ReservationStatusChanged rejection event
     */
    public function test_reservation_rejection_dispatches_event_with_refusee_status(): void
    {
        Event::fake();

        $reservation = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'statut' => 'en_attente'
        ]);

        $this->actingAs($this->admin);

        // Call the reject endpoint
        $response = $this->patch(route('admin.reservations.reject', $reservation));

        // Verify successful redirect
        $this->assertThat(
            $response->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(302),
                $this->equalTo(200)
            )
        );

        // Verify ReservationStatusChanged event was dispatched with refusee status
        Event::assertDispatched(ReservationStatusChanged::class, function ($event) {
            return $event->newStatus === 'refusee' &&
                   $event->oldStatus === 'en_attente';
        });

        $this->assertTrue(true);
    }

    /**
     * Test: Verify Broadcasting service is using Reverb driver
     */
    public function test_broadcasting_driver_is_reverb_in_production(): void
    {
        $driver = config('broadcasting.default');
        $this->assertEquals('reverb', $driver);
    }

    /**
     * Test: Verify Reverb connection details are correct
     */
    public function test_reverb_connection_config_is_correct(): void
    {
        $reverbConfig = config('broadcasting.connections.reverb');

        $this->assertEquals('reverb', $reverbConfig['driver']);
        $this->assertEquals('localhost', $reverbConfig['options']['host']);
        $this->assertEquals(8081, $reverbConfig['options']['port']);
    }
}
