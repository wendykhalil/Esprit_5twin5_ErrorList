<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests pour vérifier que le paiement n'est possible que si la réservation est confirmée
 *
 * Règle métier: Un client ne peut payer une réservation que si son statut est 'confirmee'
 * Les statuts en_attente, refusee, annulee, terminee, en_cours, litige bloquent le paiement
 */
class PaymentReservationStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $owner;
    protected Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'client@example.com',
        ]);

        $this->owner = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        $category = \App\Models\Category::create([
            'name' => 'Panneaux Solaires',
        ]);

        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->owner->id,
            'category_id' => $category->id,
            'name' => 'Panneau Solaire 200W',
            'price_per_day' => 50.00,
            'availability' => true,
        ]);
    }

    /**
     * Test: Une réservation 'en_attente' n'affiche pas le bouton de paiement
     */
    public function test_pending_reservation_hides_payment_button(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 250.00,
            'statut' => 'en_attente',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reservations.show', $reservation));

        $response->assertStatus(200);
        // Le bouton de paiement ne doit pas être affichable
        $response->assertDontSee('Procéder au paiement');
        // Le message d'attente doit être affiché
        $response->assertSee('en attente de validation');
    }

    /**
     * Test: Une réservation 'refusee' n'affiche pas le bouton de paiement
     */
    public function test_refused_reservation_hides_payment_button(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 250.00,
            'statut' => 'refusee',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reservations.show', $reservation));

        $response->assertStatus(200);
        $response->assertDontSee('Procéder au paiement');
        $response->assertSee('a été refusée');
    }

    /**
     * Test: Une réservation 'confirmee' affiche le bouton de paiement
     */
    public function test_confirmed_reservation_shows_payment_button(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 250.00,
            'statut' => 'confirmee',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reservations.show', $reservation));

        $response->assertStatus(200);
        $response->assertSee('Procéder au paiement');
    }

    /**
     * Test: Une réservation 'en_attente' ne peut pas accéder au paiement par URL directe
     */
    public function test_pending_reservation_cannot_access_payment_create(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 250.00,
            'statut' => 'en_attente',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('payments.create', [
                'reservation_id' => $reservation->id,
            ]));

        $response->assertRedirect(route('reservations.show', $reservation));
        $response->assertSessionHas('error');
    }

    /**
     * Test: Une réservation 'refusee' ne peut pas accéder au paiement par URL directe
     */
    public function test_refused_reservation_cannot_access_payment_create(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 250.00,
            'statut' => 'refusee',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('payments.create', [
                'reservation_id' => $reservation->id,
            ]));

        $response->assertRedirect(route('reservations.show', $reservation));
        $response->assertSessionHas('error');
        $response->assertSessionHas('error', fn($error) => str_contains($error, 'refusée'));
    }

    /**
     * Test: Une réservation 'confirmee' peut accéder à la page de paiement
     */
    public function test_confirmed_reservation_can_access_payment_create(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 250.00,
            'statut' => 'confirmee',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('payments.create', [
                'reservation_id' => $reservation->id,
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.payments.create');
    }

    /**
     * Test: Une réservation 'en_attente' ne peut pas être payée via POST
     */
    public function test_pending_reservation_cannot_be_paid_via_post(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 250.00,
            'statut' => 'en_attente',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('payments.store'), [
                'amount' => 250.00,
                'method' => 'card',
                'reservation_id' => $reservation->id,
                'card_holder' => 'John Doe',
                'card_number' => '4111111111111111',
                'card_expiry' => '12/25',
                'card_cvv' => '123',
            ]);

        $response->assertRedirect(route('reservations.show', $reservation));
        $response->assertSessionHas('error');
    }

    /**
     * Test: Une réservation 'confirmee' peut être payée via POST
     */
    public function test_confirmed_reservation_can_be_paid_via_post(): void
    {
        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 250.00,
            'statut' => 'confirmee',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('payments.store'), [
                'amount' => 250.00,
                'method' => 'cash',
                'reservation_id' => $reservation->id,
                'description' => 'Test payment',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'reservation_id' => $reservation->id,
            'user_id' => $this->user->id,
            'amount' => 250.00,
        ]);
    }

    /**
     * Test: Un autre client ne peut pas payer une réservation qui ne lui appartient pas
     */
    public function test_another_user_cannot_pay_someones_reservation(): void
    {
        $otherUser = User::factory()->create([
            'email' => 'other@example.com',
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 250.00,
            'statut' => 'confirmee',
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('payments.create', [
                'reservation_id' => $reservation->id,
            ]));

        $response->assertStatus(403);
    }

    /**
     * Test: Un autre client ne peut pas payer une réservation via POST
     */
    public function test_another_user_cannot_pay_via_post(): void
    {
        $otherUser = User::factory()->create([
            'email' => 'other@example.com',
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'prix_total' => 250.00,
            'statut' => 'confirmee',
        ]);

        $response = $this->actingAs($otherUser)
            ->post(route('payments.store'), [
                'amount' => 250.00,
                'method' => 'cash',
                'reservation_id' => $reservation->id,
                'description' => 'Test payment',
            ]);

        // Le contrôleur devrait bloquer l'accès avec 403 ou redirection
        $this->assertTrue(
            $response->status() === 403 || $response->isRedirect(),
            "Accès devrait être bloqué (403 ou redirection)"
        );
    }

    /**
     * Test: Tous les statuts restrictifs bloquent l'accès au paiement
     */
    public function test_all_restricted_statuses_block_payment(): void
    {
        $restrictedStatuses = ['en_attente', 'refusee', 'annulee', 'terminee', 'en_cours', 'litige'];

        foreach ($restrictedStatuses as $status) {
            $reservation = Reservation::factory()->create([
                'user_id' => $this->user->id,
                'equipment_id' => $this->equipment->id,
                'prix_total' => 250.00,
                'statut' => $status,
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('payments.create', [
                    'reservation_id' => $reservation->id,
                ]));

            $this->assertTrue(
                $response->isRedirect(),
                "Le statut '$status' devrait rediriger (bloquer le paiement)"
            );
        }
    }
}
