<?php

namespace Tests\Unit;

use App\Events\ReservationCreated;
use App\Events\ReservationStatusChanged;
use App\Events\PaymentProcessed;
use App\Events\DeliveryStatusChanged;
use PHPUnit\Framework\TestCase;

/**
 * BroadcastingEventTest
 *
 * Unit tests for broadcast events without database dependencies.
 * Verifies that events are properly configured for broadcasting.
 */
class BroadcastingEventTest extends TestCase
{
    /**
     * Test: ReservationCreated event is properly configured
     */
    public function test_reservation_created_event_class_exists(): void
    {
        $this->assertTrue(class_exists(ReservationCreated::class));
    }

    /**
     * Test: ReservationStatusChanged event is properly configured
     */
    public function test_reservation_status_changed_event_class_exists(): void
    {
        $this->assertTrue(class_exists(ReservationStatusChanged::class));
    }

    /**
     * Test: PaymentProcessed event is properly configured
     */
    public function test_payment_processed_event_class_exists(): void
    {
        $this->assertTrue(class_exists(PaymentProcessed::class));
    }

    /**
     * Test: DeliveryStatusChanged event is properly configured
     */
    public function test_delivery_status_changed_event_class_exists(): void
    {
        $this->assertTrue(class_exists(DeliveryStatusChanged::class));
    }

    /**
     * Test: ReservationCreated implements ShouldBroadcast
     */
    public function test_reservation_created_implements_should_broadcast(): void
    {
        $interfaces = class_implements(ReservationCreated::class);
        $this->assertTrue(
            isset($interfaces['Illuminate\Contracts\Broadcasting\ShouldBroadcast']),
            'ReservationCreated must implement ShouldBroadcast'
        );
    }

    /**
     * Test: ReservationStatusChanged implements ShouldBroadcast
     */
    public function test_reservation_status_changed_implements_should_broadcast(): void
    {
        $interfaces = class_implements(ReservationStatusChanged::class);
        $this->assertTrue(
            isset($interfaces['Illuminate\Contracts\Broadcasting\ShouldBroadcast']),
            'ReservationStatusChanged must implement ShouldBroadcast'
        );
    }

    /**
     * Test: PaymentProcessed implements ShouldBroadcast
     */
    public function test_payment_processed_implements_should_broadcast(): void
    {
        $interfaces = class_implements(PaymentProcessed::class);
        $this->assertTrue(
            isset($interfaces['Illuminate\Contracts\Broadcasting\ShouldBroadcast']),
            'PaymentProcessed must implement ShouldBroadcast'
        );
    }

    /**
     * Test: DeliveryStatusChanged implements ShouldBroadcast
     */
    public function test_delivery_status_changed_implements_should_broadcast(): void
    {
        $interfaces = class_implements(DeliveryStatusChanged::class);
        $this->assertTrue(
            isset($interfaces['Illuminate\Contracts\Broadcasting\ShouldBroadcast']),
            'DeliveryStatusChanged must implement ShouldBroadcast'
        );
    }

    /**
     * Test: All event classes have required methods
     */
    public function test_events_have_broadcast_methods(): void
    {
        $events = [
            ReservationCreated::class,
            ReservationStatusChanged::class,
            PaymentProcessed::class,
            DeliveryStatusChanged::class,
        ];

        foreach ($events as $eventClass) {
            $this->assertTrue(
                method_exists($eventClass, 'broadcastOn'),
                "$eventClass must have broadcastOn() method"
            );
            $this->assertTrue(
                method_exists($eventClass, 'broadcastWith'),
                "$eventClass must have broadcastWith() method"
            );
            $this->assertTrue(
                method_exists($eventClass, 'broadcastAs'),
                "$eventClass must have broadcastAs() method"
            );
        }
    }
}
