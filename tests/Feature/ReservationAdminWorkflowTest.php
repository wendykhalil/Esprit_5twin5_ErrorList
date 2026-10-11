<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests du workflow administrateur pour les réservations
 * Vérifie l'acceptation, le refus, et la gestion des transitions de statut
 */
class ReservationAdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $client;
    protected User $owner;
    protected Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->client = User::factory()->create(['role' => 'client']);
        $this->owner = User::factory()->create(['role' => 'client']);

        $category = Category::create(['name' => 'Panneaux Solaires']);

        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->owner->id,
            'category_id' => $category->id,
            'name' => 'Panneau 300W',
            'price_per_day' => 75.00,
        ]);
    }

    /**
     * Test: L'admin peut accepter une réservation en attente
     */
    public function test_admin_can_approve_pending_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'en_attente',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.reservations.approve', $reservation));

        $response->assertRedirect(route('admin.reservations.show', $reservation));
        $response->assertSessionHas('success');

        $reservation->refresh();
        $this->assertEquals('confirmee', $reservation->statut);
    }

    /**
     * Test: L'admin ne peut pas accepter une réservation déjà confirmée
     */
    public function test_admin_cannot_approve_confirmed_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'confirmee',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.reservations.approve', $reservation));

        $response->assertRedirect(route('admin.reservations.show', $reservation));
        $response->assertSessionHas('error');

        $reservation->refresh();
        $this->assertEquals('confirmee', $reservation->statut);
    }

    /**
     * Test: L'admin ne peut pas accepter une réservation si les dates ne sont plus disponibles
     */
    public function test_admin_cannot_approve_if_dates_unavailable(): void
    {
        $reservation1 = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-10-15',
            'date_fin' => '2026-10-20',
            'statut' => 'en_attente',
        ]);

        // Une autre réservation confirmée sur les mêmes dates
        $reservation2 = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-10-15',
            'date_fin' => '2026-10-20',
            'statut' => 'confirmee',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.reservations.approve', $reservation1));

        $response->assertRedirect(route('admin.reservations.show', $reservation1));
        $response->assertSessionHas('error');

        $reservation1->refresh();
        $this->assertEquals('en_attente', $reservation1->statut);
    }

    /**
     * Test: L'admin peut refuser une réservation en attente
     */
    public function test_admin_can_reject_pending_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'en_attente',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.reservations.reject', $reservation));

        $response->assertRedirect(route('admin.reservations.show', $reservation));
        $response->assertSessionHas('success', 'Réservation refusée.');

        $reservation->refresh();
        $this->assertEquals('refusee', $reservation->statut);
    }

    /**
     * Test: L'admin ne peut pas refuser une réservation déjà confirmée
     */
    public function test_admin_cannot_reject_confirmed_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'confirmee',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.reservations.reject', $reservation));

        $response->assertRedirect(route('admin.reservations.show', $reservation));
        $response->assertSessionHas('error');

        $reservation->refresh();
        $this->assertEquals('confirmee', $reservation->statut);
    }

    /**
     * Test: L'admin peut créer une réservation pour un client
     */
    public function test_admin_can_create_reservation(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.reservations.store'), [
                'user_id' => $this->client->id,
                'equipment_id' => $this->equipment->id,
                'date_debut' => '2026-10-15',
                'date_fin' => '2026-10-20',
                'statut' => 'confirmee',
            ]);

        $response->assertRedirect(route('admin.reservations.index'));

        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'confirmee',
            'prix_total' => 375.00, // 5 jours × 75.00
        ]);
    }

    /**
     * Test: L'admin ne peut pas créer une réservation d'auto-réservation
     */
    public function test_admin_cannot_create_self_reservation(): void
    {
        $ownEquipment = Equipment::factory()->create([
            'user_id' => $this->client->id,
            'category_id' => $this->equipment->category_id,
            'price_per_day' => 50.00,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.reservations.store'), [
                'user_id' => $this->client->id,
                'equipment_id' => $ownEquipment->id,
                'date_debut' => '2026-10-15',
                'date_fin' => '2026-10-20',
                'statut' => 'confirmee',
            ]);

        $response->assertSessionHasErrors('equipment_id');
    }

    /**
     * Test: L'admin peut modifier une réservation
     */
    public function test_admin_can_update_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-10-15',
            'date_fin' => '2026-10-20',
            'statut' => 'en_attente',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.reservations.update', $reservation), [
                'user_id' => $this->client->id,
                'equipment_id' => $this->equipment->id,
                'date_debut' => '2026-10-16',
                'date_fin' => '2026-10-21',
                'statut' => 'en_attente',
            ]);

        $response->assertRedirect(route('admin.reservations.show', $reservation));

        $reservation->refresh();
        $this->assertEquals('2026-10-16', $reservation->date_debut->format('Y-m-d'));
        $this->assertEquals('2026-10-21', $reservation->date_fin->format('Y-m-d'));
        // Prix recalculé: 5 jours × 75.00 = 375.00
        $this->assertEquals(375.00, $reservation->prix_total);
    }

    /**
     * Test: L'admin ne peut pas supprimer une réservation confirmée
     */
    public function test_admin_cannot_delete_confirmed_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'confirmee',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.reservations.destroy', $reservation));

        $response->assertRedirect(route('admin.reservations.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);
    }

    /**
     * Test: L'admin peut supprimer une réservation en attente
     */
    public function test_admin_can_delete_pending_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'en_attente',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.reservations.destroy', $reservation));

        $response->assertRedirect(route('admin.reservations.index'));

        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
    }

    /**
     * Test: L'admin peut voir le filtrage par statut
     */
    public function test_admin_can_filter_by_status(): void
    {
        Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'statut' => 'en_attente',
        ]);

        Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'statut' => 'confirmee',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.reservations.index', ['status' => 'en_attente']));

        $reservations = $response->viewData('reservations');
        
        foreach ($reservations as $reservation) {
            $this->assertEquals('en_attente', $reservation->statut);
        }
    }

    /**
     * Test: Les réservations refusées n'empêchent pas les nouvelles réservations
     */
    public function test_refused_reservations_dont_block_new_ones(): void
    {
        $refused = Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-10-15',
            'date_fin' => '2026-10-20',
            'statut' => 'refusee',
        ]);

        // Nouvelle réservation sur les mêmes dates devrait réussir
        $response = $this->actingAs($this->admin)
            ->post(route('admin.reservations.store'), [
                'user_id' => $this->client->id,
                'equipment_id' => $this->equipment->id,
                'date_debut' => '2026-10-15',
                'date_fin' => '2026-10-20',
                'statut' => 'confirmee',
            ]);

        $response->assertRedirect(route('admin.reservations.index'));

        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'confirmee',
        ]);
    }

    /**
     * Test: L'admin peut voir les réservations groupées par statut
     */
    public function test_admin_can_see_all_statuses_in_filter(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reservations.index'));

        $statuses = $response->viewData('statuses');
        
        $expectedStatuses = ['en_attente', 'confirmee', 'en_cours', 'terminee', 'litige', 'annulee', 'refusee'];
        foreach ($expectedStatuses as $status) {
            $this->assertArrayHasKey($status, $statuses);
        }
    }

    /**
     * Test: Approver une réservation crée automatiquement une livraison
     */
    public function test_admin_approval_does_not_auto_create_delivery(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'en_attente',
        ]);

        // Verify no delivery exists before approval
        $this->assertFalse($reservation->delivery()->exists());

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.reservations.approve', $reservation));

        $response->assertRedirect(route('admin.reservations.show', $reservation));
        $response->assertSessionHas('success');

        // Verify reservation was confirmed
        $reservation->refresh();
        $this->assertEquals('confirmee', $reservation->statut);

        // IMPORTANT: Verify NO delivery was created on approval
        // Delivery is now created only when client accepts the contract
        $this->assertFalse($reservation->delivery()->exists());
    }
}
