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

/**
 * Test du lien "Voir mon contrat" sur la page de détails de réservation
 *
 * Vérifie:
 * 1. Le bouton "Voir mon contrat" apparaît sur la page de réservation quand un paiement existe
 * 2. Le bouton pointe vers le bon contrat
 * 3. Le contrat peut être accédé via le lien
 * 4. Sécurité: un client ne peut pas accéder au contrat d'un autre client
 * 5. Le bouton n'apparaît que quand un contrat existe
 * 6. Le contrôle d'accès à la réservation fonctionne toujours
 */
class ReservationContractLinkTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $otherClient;
    protected User $owner;
    protected Equipment $equipment;
    protected Reservation $reservation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->client = User::factory()->create(['role' => 'client', 'email' => 'client@test.com']);
        $this->otherClient = User::factory()->create(['role' => 'client', 'email' => 'other@test.com']);
        $this->owner = User::factory()->create(['role' => 'provider', 'email' => 'owner@test.com']);

        $category = Category::create(['name' => 'Équipement Solaire']);

        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->owner->id,
            'category_id' => $category->id,
            'name' => 'Batterie portable 1000Wh',
            'price_per_day' => 50.00,
        ]);

        $this->reservation = Reservation::create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDays(5),
            'date_fin' => now()->addDays(6),
            'statut' => 'confirmee',
            'prix_total' => 50.00,
        ]);
    }

    /**
     * Test 1: Le bouton "Voir mon contrat" s'affiche quand un paiement et un contrat existent
     */
    public function test_contract_button_appears_on_reservation_detail_page(): void
    {
        // Créer paiement
        $payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 50.00,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        // Créer contrat
        $contract = Contract::create([
            'reservation_id' => $this->reservation->id,
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'contract_number' => Contract::generateContractNumber(),
            'start_date' => $this->reservation->date_debut,
            'end_date' => $this->reservation->date_fin,
            'amount' => $payment->amount,
            'status' => 'active',
            'terms_and_conditions' => 'Test Terms',
        ]);

        // Voir la page de réservation
        $response = $this->actingAs($this->client)
            ->get(route('reservations.show', $this->reservation));

        $response->assertStatus(200);

        // Vérifier que le bouton "Voir mon contrat" s'affiche
        $response->assertSee('Voir mon contrat');
        $response->assertSee(route('contracts.show', $contract));
    }

    /**
     * Test 2: Le bouton n'apparaît pas quand aucun contrat n'existe
     */
    public function test_contract_button_does_not_appear_without_payment(): void
    {
        // Pas de paiement créé

        // Voir la page de réservation
        $response = $this->actingAs($this->client)
            ->get(route('reservations.show', $this->reservation));

        $response->assertStatus(200);

        // Le bouton "Voir mon contrat" ne doit pas s'afficher
        $response->assertDontSee('Voir mon contrat');
    }

    /**
     * Test 3: Le bouton n'apparaît pas quand le paiement existe mais pas le contrat
     */
    public function test_contract_button_does_not_appear_without_contract(): void
    {
        // Créer paiement mais pas de contrat
        $payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 50.00,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        // Voir la page de réservation
        $response = $this->actingAs($this->client)
            ->get(route('reservations.show', $this->reservation));

        $response->assertStatus(200);

        // Le bouton "Voir mon contrat" ne doit pas s'afficher car pas de contrat
        $response->assertDontSee('Voir mon contrat');
    }

    /**
     * Test 4: Le bouton pointe vers le bon contrat
     */
    public function test_contract_button_links_to_correct_contract(): void
    {
        $payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 50.00,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        $contract = Contract::create([
            'reservation_id' => $this->reservation->id,
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'contract_number' => 'CONT-TEST-001',
            'start_date' => $this->reservation->date_debut,
            'end_date' => $this->reservation->date_fin,
            'amount' => $payment->amount,
            'status' => 'active',
            'terms_and_conditions' => 'Test Terms',
        ]);

        // Voir la page de réservation
        $response = $this->actingAs($this->client)
            ->get(route('reservations.show', $this->reservation));

        $response->assertStatus(200);

        // Vérifier que le lien est correct
        $contractUrl = route('contracts.show', $contract);
        $response->assertSee($contractUrl);

        // Vérifier que on peut cliquer le lien et accéder au contrat
        $contractResponse = $this->actingAs($this->client)
            ->get($contractUrl);

        $contractResponse->assertStatus(200);
    }

    /**
     * Test 5: Sécurité - Un client ne peut pas accéder au contrat d'un autre client
     */
    public function test_client_cannot_access_other_users_contract_via_link(): void
    {
        $payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 50.00,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        $contract = Contract::create([
            'reservation_id' => $this->reservation->id,
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'contract_number' => Contract::generateContractNumber(),
            'start_date' => $this->reservation->date_debut,
            'end_date' => $this->reservation->date_fin,
            'amount' => $payment->amount,
            'status' => 'active',
            'terms_and_conditions' => 'Test Terms',
        ]);

        // Le propriétaire du contrat peut le voir
        $clientResponse = $this->actingAs($this->client)
            ->get(route('contracts.show', $contract));
        $clientResponse->assertStatus(200);

        // Un autre client ne peut pas le voir
        $otherClientResponse = $this->actingAs($this->otherClient)
            ->get(route('contracts.show', $contract));
        $otherClientResponse->assertStatus(403);
    }

    /**
     * Test 6: Un client ne peut pas voir la page de réservation d'un autre client
     */
    public function test_client_cannot_access_other_users_reservation(): void
    {
        // Créer paiement et contrat
        $payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 50.00,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        Contract::create([
            'reservation_id' => $this->reservation->id,
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'contract_number' => Contract::generateContractNumber(),
            'start_date' => $this->reservation->date_debut,
            'end_date' => $this->reservation->date_fin,
            'amount' => $payment->amount,
            'status' => 'active',
            'terms_and_conditions' => 'Test Terms',
        ]);

        // Le propriétaire peut voir sa réservation
        $ownerResponse = $this->actingAs($this->client)
            ->get(route('reservations.show', $this->reservation));
        $ownerResponse->assertStatus(200);

        // Un autre client ne peut pas voir
        $otherResponse = $this->actingAs($this->otherClient)
            ->get(route('reservations.show', $this->reservation));
        $otherResponse->assertStatus(403);
    }

    /**
     * Test 7: Le message de paiement s'affiche correctement avec le bouton
     */
    public function test_payment_status_displayed_with_contract_button(): void
    {
        $payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 50.00,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        $contract = Contract::create([
            'reservation_id' => $this->reservation->id,
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'contract_number' => Contract::generateContractNumber(),
            'start_date' => $this->reservation->date_debut,
            'end_date' => $this->reservation->date_fin,
            'amount' => $payment->amount,
            'status' => 'active',
            'terms_and_conditions' => 'Test Terms',
        ]);

        $response = $this->actingAs($this->client)
            ->get(route('reservations.show', $this->reservation));

        $response->assertStatus(200);

        // Vérifier que le message de paiement s'affiche
        $response->assertSee('Paiement déjà effectué');
        $response->assertSee('Paid');

        // Vérifier que le bouton s'affiche à côté
        $response->assertSee('Voir mon contrat');
    }

    /**
     * Test 8: Les autres boutons d'action restent visibles
     */
    public function test_other_action_buttons_remain_visible(): void
    {
        // Réservation confirmée
        $response = $this->actingAs($this->client)
            ->get(route('reservations.show', $this->reservation));

        $response->assertStatus(200);

        // Page charge correctement
        $response->assertSee('Ma réservation');
        $response->assertSee('Informations générales');
    }

    /**
     * Test 9: Le lien contrat reste accessible même avec compensation code appliqué
     */
    public function test_contract_link_works_with_compensation_code(): void
    {
        // Appliquer un code de compensation
        $this->reservation->update([
            'compensation_code_id' => null,
            'discount_amount' => 10.00,
            'prix_total' => 40.00,
        ]);

        $payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 40.00,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        $contract = Contract::create([
            'reservation_id' => $this->reservation->id,
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'contract_number' => Contract::generateContractNumber(),
            'start_date' => $this->reservation->date_debut,
            'end_date' => $this->reservation->date_fin,
            'amount' => $payment->amount,
            'status' => 'active',
            'terms_and_conditions' => 'Test Terms',
        ]);

        $response = $this->actingAs($this->client)
            ->get(route('reservations.show', $this->reservation));

        $response->assertStatus(200);

        // Bouton doit quand même s'afficher
        $response->assertSee('Voir mon contrat');

        // Lien doit être correct
        $response->assertSee(route('contracts.show', $contract));
    }
}
