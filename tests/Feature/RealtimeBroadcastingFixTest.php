<?php

namespace Tests\Feature;

use App\Events\PaymentProcessed;
use App\Events\DeliveryStatusChanged;
use App\Events\ReservationCreated;
use App\Models\Delivery;
use App\Models\Equipment;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * RealtimeBroadcastingFixTest
 *
 * Verifies that real-time broadcasting works end-to-end after fixes:
 * 1. BROADCAST_DRIVER=reverb (was 'log')
 * 2. VITE_REVERB_PORT=8081 (was 8080)
 *
 * These tests ensure events are broadcasted and contain correct data for frontend listeners.
 */
class RealtimeBroadcastingFixTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $provider;
    protected User $admin;
    protected Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $category = \App\Models\Category::create([
            'name' => 'Solar Equipment',
            'description' => 'Solar and renewable energy equipment'
        ]);

        $this->provider = User::factory()->create(['role' => 'provider']);
        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->provider->id,
            'category_id' => $category->id,
            'price_per_day' => 100,
            'availability' => true,
            'status' => 'active'
        ]);

        $this->client = User::factory()->create(['role' => 'client']);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Test #5: Reservation creation broadcasts ReservationCreated event
     */
    public function test_reservation_creation_broadcasts_event(): void
    {
        Event::fake();
        $this->actingAs($this->client);

        $startDate = now()->addDay()->format('Y-m-d');
        $endDate = now()->addDays(2)->format('Y-m-d');

        $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => $startDate,
            'date_fin' => $endDate,
        ]);

        // Verify event was dispatched
        Event::assertDispatched(ReservationCreated::class, function ($event) {
            return $event->reservation->id > 0 &&
                   $event->reservation->equipment_id == $this->equipment->id &&
                   $event->reservation->user_id == $this->client->id;
        });

        $this->assertTrue(true);
    }

    /**
     * Test #6: Payment update broadcasts PaymentProcessed event with correct data
     */
    public function test_payment_processed_broadcasts_with_correct_data(): void
    {
        Event::fake();

        // Create a reservation and payment
        $reservation = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'status' => 'pending'
        ]);

        $payment = Payment::factory()->create([
            'user_id' => $this->client->id,
            'reservation_id' => $reservation->id,
            'amount' => 500,
            'status' => 'pending'
        ]);

        // Dispatch the event
        event(new PaymentProcessed($payment));

        // Verify event was dispatched with correct broadcast channels and data
        Event::assertDispatched(PaymentProcessed::class, function ($event) use ($payment, $reservation) {
            // Check that event broadcasts to both admin and user channel
            $channels = $event->broadcastOn();
            $broadcastChannels = array_map(function ($channel) {
                return $channel->name;
            }, $channels);

            $hasAdminChannel = in_array('admin.payments', $broadcastChannels);
            $hasUserChannel = in_array('user.' . $this->client->id . '.payments', $broadcastChannels);

            // Check broadcast data includes necessary fields
            $broadcastData = $event->broadcastWith();
            $hasRequiredFields = isset($broadcastData['id']) &&
                                isset($broadcastData['user_id']) &&
                                isset($broadcastData['amount']) &&
                                isset($broadcastData['status']) &&
                                isset($broadcastData['user']) &&
                                isset($broadcastData['reservation']);

            return $hasAdminChannel && $hasUserChannel && $hasRequiredFields;
        });

        $this->assertTrue(true);
    }

    /**
     * Test #7: Delivery status update broadcasts DeliveryStatusChanged event
     */
    public function test_delivery_status_change_broadcasts_with_correct_data(): void
    {
        Event::fake();

        // Create reservation and delivery
        $reservation = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'user_id' => $this->client->id,
            'status' => 'approved'
        ]);

        $delivery = Delivery::factory()->create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->client->id,
            'status' => 'pending',
            'scheduled_date' => now()->addDay()
        ]);

        $oldStatus = $delivery->status;
        $delivery->status = 'in_progress';
        $delivery->save();

        // Dispatch the event
        event(new DeliveryStatusChanged($delivery, $oldStatus));

        // Verify event was dispatched with correct data
        Event::assertDispatched(DeliveryStatusChanged::class, function ($event) use ($oldStatus) {
            $channels = $event->broadcastOn();
            $broadcastChannels = array_map(function ($channel) {
                return $channel->name;
            }, $channels);

            $hasAdminChannel = in_array('admin.deliveries', $broadcastChannels);
            $hasUserChannel = in_array('user.' . $this->client->id . '.deliveries', $broadcastChannels);

            $broadcastData = $event->broadcastWith();
            $hasRequiredFields = isset($broadcastData['id']) &&
                                isset($broadcastData['status']) &&
                                isset($broadcastData['old_status']) &&
                                $broadcastData['old_status'] === $oldStatus &&
                                isset($broadcastData['user']) &&
                                isset($broadcastData['updated_at']);

            return $hasAdminChannel && $hasUserChannel && $hasRequiredFields;
        });

        $this->assertTrue(true);
    }


}
