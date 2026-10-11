<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de sécurité du module Reservation
 * Vérifie les restrictions de statut, l'auto-réservation, et les transitions invalides
 */
class ReservationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $owner;
    protected User $admin;
    protected Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create(['role' => 'client']);
        $this->owner = User::factory()->create(['role' => 'client']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $category = Category::create(['name' => 'Panneaux Solaires']);

        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->owner->id,
            'category_id' => $category->id,
            'name' => 'Panneau 200W',
            'price_per_day' => 50.00,
        ]);
    }

    /**
     * Test: Un client ne peut pas réserver son propre équipement
     */
    public function test_client_cannot_reserve_own_equipment(): void
    {
        $ownEquipment = Equipment::factory()->create([
            'user_id' => $this->client->id,
            'category_id' => $this->equipment->category_id,
            'price_per_day' => 50.00,
        ]);

        $response = $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $ownEquipment->id,
                'date_debut' => '2026-10-15',
                'date_fin' => '2026-10-20',
            ]);

        $response->assertSessionHasErrors('equipment_id');
        $this->assertDatabaseMissing('reservations', [
            'user_id' => $this->client->id,
            'equipment_id' => $ownEquipment->id,
        ]);
    }

    /**
     * Test: Un client peut créer une réservation sur l'équipement d'un autre
     */
    public function test_client_can_reserve_others_equipment(): void
    {
        $this->actingAs($this->client);

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-10-15',
            'date_fin' => '2026-10-20',
        ]);

        $response->assertRedirect(route('reservations.index'));

        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'en_attente',
        ]);
    }

    /**
     * Test: Un client ne peut modifier une réservation que si elle est en attente
     */
    public function test_client_can_only_modify_pending_reservation(): void
    {
        $pending = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'en_attente',
        ]);

        $confirmed = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'confirmee',
        ]);

        $this->actingAs($this->client);

        // Modification de la réservation en attente: OK
        $response = $this->get(route('reservations.edit', $pending));
        $response->assertOk();

        // Modification de la réservation confirmée: 403
        $response = $this->get(route('reservations.edit', $confirmed));
        $response->assertStatus(403);
    }

    /**
     * Test: Un client ne peut supprimer que les réservations en attente
     */
    public function test_client_can_only_delete_pending_reservation(): void
    {
        $pending = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'en_attente',
        ]);

        $confirmed = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'confirmee',
        ]);

        $this->actingAs($this->client);

        // Suppression de la réservation en attente: OK
        $response = $this->delete(route('reservations.destroy', $pending));
        $response->assertRedirect(route('reservations.index'));
        $this->assertDatabaseMissing('reservations', ['id' => $pending->id]);

        // Suppression de la réservation confirmée: 403
        $response = $this->delete(route('reservations.destroy', $confirmed));
        $response->assertStatus(403);
    }

    /**
     * Test: Un client peut annuler une réservation en attente ou confirmée
     */
    public function test_client_can_cancel_pending_or_confirmed_reservation(): void
    {
        $pending = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'en_attente',
        ]);

        $confirmed = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'confirmee',
        ]);

        $this->actingAs($this->client);

        // Annulation de la réservation en attente
        $response = $this->patch(route('reservations.cancel', $pending));
        $response->assertRedirect(route('reservations.index'));

        $pending->refresh();
        $this->assertEquals('annulee', $pending->statut);

        // Annulation de la réservation confirmée
        $response = $this->patch(route('reservations.cancel', $confirmed));
        $response->assertRedirect(route('reservations.index'));

        $confirmed->refresh();
        $this->assertEquals('annulee', $confirmed->statut);
    }

    /**
     * Test: Un client ne peut pas annuler une réservation terminée
     */
    public function test_client_cannot_cancel_completed_reservation(): void
    {
        $completed = Reservation::factory()->create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'terminee',
        ]);

        $response = $this->actingAs($this->client)
            ->patch(route('reservations.cancel', $completed));

        $response->assertStatus(403);
        $completed->refresh();
        $this->assertEquals('terminee', $completed->statut);
    }

    /**
     * Test: Un client ne peut pas modifier la réservation d'un autre
     */
    public function test_client_cannot_modify_other_client_reservation(): void
    {
        $otherReservation = Reservation::factory()->create([
            'user_id' => $this->owner->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'en_attente',
        ]);

        $response = $this->actingAs($this->client)
            ->get(route('reservations.edit', $otherReservation));

        $response->assertStatus(403);
    }

    /**
     * Test: Un client ne peut pas voir la réservation d'un autre
     */
    public function test_client_cannot_view_other_client_reservation(): void
    {
        $otherReservation = Reservation::factory()->create([
            'user_id' => $this->owner->id,
            'equipment_id' => $this->equipment->id,
        ]);

        $response = $this->actingAs($this->client)
            ->get(route('reservations.show', $otherReservation));

        $response->assertStatus(403);
    }

    /**
     * Test: Impossible de créer deux réservations qui se chevauchent
     */
    public function test_cannot_create_overlapping_reservations(): void
    {
        // Première réservation
        Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-10-15',
            'date_fin' => '2026-10-20',
            'statut' => 'confirmee',
        ]);

        // Tentative de créer une réservation qui chevauche
        $response = $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => '2026-10-18',
                'date_fin' => '2026-10-22',
            ]);

        $response->assertSessionHasErrors('date_debut');
    }

    /**
     * Test: Les réservations annulées n'empêchent pas les nouvelles réservations
     */
    public function test_cancelled_reservations_dont_block_new_ones(): void
    {
        // Réservation annulée
        Reservation::factory()->create([
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-10-15',
            'date_fin' => '2026-10-20',
            'statut' => 'annulee',
        ]);

        // Nouvelle réservation sur les mêmes dates devrait réussir
        $response = $this->actingAs($this->client)
            ->post(route('reservations.store'), [
                'equipment_id' => $this->equipment->id,
                'date_debut' => '2026-10-15',
                'date_fin' => '2026-10-20',
            ]);

        $response->assertRedirect(route('reservations.index'));

        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'statut' => 'en_attente',
        ]);
    }

    /**
     * Test: Le prix est toujours calculé côté serveur
     */
    public function test_price_is_always_calculated_server_side(): void
    {
        $this->actingAs($this->client);

        $response = $this->post(route('reservations.store'), [
            'equipment_id' => $this->equipment->id,
            'date_debut' => '2026-10-15',
            'date_fin' => '2026-10-20', // 5 jours
            // Pas de prix envoyé du client
        ]);

        // Prix attendu: 5 jours × 50.00 = 250.00
        $this->assertDatabaseHas('reservations', [
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 250.00,
        ]);
    }

    /**
     * Test: L'index affiche seulement les réservations de l'utilisateur
     */
    public function test_index_shows_only_user_reservations(): void
    {
        Reservation::factory()->create(['user_id' => $this->client->id]);
        Reservation::factory()->create(['user_id' => $this->owner->id]);

        $response = $this->actingAs($this->client)
            ->get(route('reservations.index'));

        $reservations = $response->viewData('reservations');
        
        // Tous les éléments affichés doivent appartenir à $this->client
        foreach ($reservations as $reservation) {
            $this->assertEquals($this->client->id, $reservation->user_id);
        }
    }
}
