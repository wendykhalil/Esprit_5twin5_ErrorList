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
 * Test du bouton "Voir mon contrat" sur la page de paiement confirmé
 * 
 * Vérifie:
 * 1. Le bouton apparaît quand un contrat existe pour la réservation du paiement
 * 2. Le bouton pointe vers le bon contrat
 * 3. Sécurité: un client ne peut pas accéder au contrat d'un autre client
 * 4. Absence de contrat gérée sans erreur
 * 5. Les autres liens continuent de fonctionner
 * 6. Acceptation du contrat crée une seule livraison
 */
class PaymentContractAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $owner;
    protected Equipment $equipment;
    protected Reservation $reservation;
    protected Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create(['role' => 'client', 'email' => 'client@test.com']);
        $this->owner = User::factory()->create(['role' => 'client', 'email' => 'owner@test.com']);

        $category = Category::create(['name' => 'Équipement Solaire']);

        $this->equipment = Equipment::factory()->create([
            'user_id' => $this->owner->id,
            'category_id' => $category->id,
            'name' => 'Panneau Solaire 400W',
            'price_per_day' => 50.00,
        ]);

        // Create and confirm a reservation
        $this->reservation = Reservation::create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDays(5),
            'date_fin' => now()->addDays(10),
            'statut' => 'confirmee',
            'prix_total' => 250.00,
        ]);
    }

    /**
     * Test 1: Le bouton "Voir mon contrat" s'affiche quand un contrat existe
     */
    public function test_contract_button_appears_when_contract_exists(): void
    {
        // Créer un paiement avec réservation et contrat
        $this->payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 250.00,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        // Créer un contrat pour cette réservation
        $contract = Contract::create([
            'reservation_id' => $this->reservation->id,
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'contract_number' => Contract::generateContractNumber(),
            'start_date' => $this->reservation->date_debut,
            'end_date' => $this->reservation->date_fin,
            'amount' => $this->payment->amount,
            'status' => 'active',
            'terms_and_conditions' => 'Terms test',
        ]);

        // Voir la page de paiement
        $response = $this->actingAs($this->client)
            ->get(route('payments.show', $this->payment));

        $response->assertStatus(200);
        $response->assertViewHas('contract', $contract);
        
        // Vérifier que le bouton "Voir mon contrat" est présent
        $response->assertSee('Voir mon contrat');
        $response->assertSee(route('contracts.show', $contract));
    }

    /**
     * Test 2: Le bouton n'apparaît pas quand aucun contrat n'existe
     */
    public function test_contract_button_does_not_appear_when_no_contract_exists(): void
    {
        // Créer un paiement sans contrat
        $this->payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 250.00,
            'method' => 'bank_transfer',
            'status' => 'pending',
            'payment_date' => now(),
        ]);

        // Voir la page de paiement
        $response = $this->actingAs($this->client)
            ->get(route('payments.show', $this->payment));

        $response->assertStatus(200);
        $response->assertViewHas('contract', null);
        
        // Vérifier que le bouton "Voir mon contrat" N'EST PAS présent
        $this->assertStringNotContainsString('Voir mon contrat', $response->getContent());
    }

    /**
     * Test 3: Le bouton pointe vers le bon contrat
     */
    public function test_contract_button_links_to_correct_contract(): void
    {
        $this->payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 250.00,
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
            'amount' => $this->payment->amount,
            'status' => 'active',
            'terms_and_conditions' => 'Terms',
        ]);

        // Voir la page de paiement
        $response = $this->actingAs($this->client)
            ->get(route('payments.show', $this->payment));

        // Vérifier que le lien pointe vers ce contrat spécifique
        $contractUrl = route('contracts.show', $contract);
        $response->assertSee($contractUrl);

        // Vérifier que on peut cliquer le lien et accéder au contrat
        $contractResponse = $this->actingAs($this->client)
            ->get($contractUrl);
        
        $contractResponse->assertStatus(200);
        $contractResponse->assertViewHas('contract', $contract);
    }

    /**
     * Test 4: Sécurité - Un client ne peut pas accéder au contrat d'un autre client
     */
    public function test_client_cannot_access_other_users_contract(): void
    {
        $otherClient = User::factory()->create(['role' => 'client', 'email' => 'other@test.com']);

        $this->payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 250.00,
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
            'amount' => $this->payment->amount,
            'status' => 'active',
            'terms_and_conditions' => 'Terms',
        ]);

        // Le propriétaire du contrat peut le voir
        $clientResponse = $this->actingAs($this->client)
            ->get(route('contracts.show', $contract));
        $clientResponse->assertStatus(200);

        // Un autre client ne peut pas le voir
        $otherClientResponse = $this->actingAs($otherClient)
            ->get(route('contracts.show', $contract));
        $otherClientResponse->assertStatus(403);
    }

    /**
     * Test 5: Vérifier que le paiement charge correctement la réservation et le contrat
     */
    public function test_payment_loads_reservation_and_contract_correctly(): void
    {
        $this->payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 250.00,
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
            'amount' => $this->payment->amount,
            'status' => 'active',
            'terms_and_conditions' => 'Terms',
        ]);

        $response = $this->actingAs($this->client)
            ->get(route('payments.show', $this->payment));

        $response->assertStatus(200);
        
        // Vérifier que le paiement et le contrat sont bien chargés dans la vue
        $paymentFromView = $response->viewData('payment');
        $contractFromView = $response->viewData('contract');
        
        $this->assertEquals($this->payment->id, $paymentFromView->id);
        $this->assertEquals($contract->id, $contractFromView->id);
        $this->assertEquals($contract->user_id, $this->client->id);
    }

    /**
     * Test 6: Les autres boutons continuent de fonctionner
     */
    public function test_other_buttons_still_work(): void
    {
        $this->payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 250.00,
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
            'amount' => $this->payment->amount,
            'status' => 'active',
            'terms_and_conditions' => 'Terms',
        ]);

        $response = $this->actingAs($this->client)
            ->get(route('payments.show', $this->payment));

        $response->assertStatus(200);
        
        // Vérifier que tous les boutons sont présents
        $response->assertSee(route('payments.invoice', $this->payment));
        $response->assertSee(route('payments.history'));
        $response->assertSee(route('equipments.index'));
        
        // Vérifier que les boutons sont cliquables
        $invoiceResponse = $this->actingAs($this->client)
            ->get(route('payments.invoice', $this->payment));
        $invoiceResponse->assertStatus(200);

        $historyResponse = $this->actingAs($this->client)
            ->get(route('payments.history'));
        $historyResponse->assertStatus(200);
    }

    /**
     * Test 7: Acceptation du contrat crée une seule livraison
     */
    public function test_accepting_contract_creates_single_delivery(): void
    {
        $this->payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 250.00,
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
            'amount' => $this->payment->amount,
            'status' => 'active',
            'terms_and_conditions' => 'Terms',
        ]);

        // Before accepting: no deliveries
        $this->assertEquals(0, \App\Models\Delivery::where('reservation_id', $this->reservation->id)->count());

        // Accept the contract
        $response = $this->actingAs($this->client)
            ->post(route('contracts.accept', $contract));

        $response->assertRedirect();

        // After accepting: exactly 1 delivery created
        $deliveries = \App\Models\Delivery::where('reservation_id', $this->reservation->id)->get();
        $this->assertEquals(1, $deliveries->count());
        $this->assertEquals('prete', $deliveries->first()->status);
    }

    /**
     * Test 8: Un paiement sans réservation ne provoque pas d'erreur
     */
    public function test_payment_without_reservation_shows_no_contract(): void
    {
        // Créer un paiement sans réservation associée
        $this->payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => null,
            'amount' => 100.00,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        // Ne devrait pas provoquer d'erreur
        $response = $this->actingAs($this->client)
            ->get(route('payments.show', $this->payment));

        $response->assertStatus(200);
        $response->assertViewHas('contract', null);
    }

    /**
     * Test 9: La page "Prochaines étapes" s'affiche différemment selon le contrat
     */
    public function test_next_steps_changes_with_contract(): void
    {
        // Case 1: Avec contrat
        $paymentWithContract = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 250.00,
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
            'amount' => $paymentWithContract->amount,
            'status' => 'active',
            'terms_and_conditions' => 'Terms',
        ]);

        $responseWith = $this->actingAs($this->client)
            ->get(route('payments.show', $paymentWithContract));

        $responseWith->assertStatus(200);
        // Doit montrer "Contrat généré" et "Accepter le contrat"
        $responseWith->assertSee('Contrat généré');
        $responseWith->assertSee('Accepter le contrat');

        // Case 2: Sans contrat
        $paymentWithoutContract = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => null,
            'amount' => 100.00,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        $responseWithout = $this->actingAs($this->client)
            ->get(route('payments.show', $paymentWithoutContract));

        $responseWithout->assertStatus(200);
        // Doit montrer "Détails disponibles" et "Préparation de la location"
        $responseWithout->assertSee('Détails disponibles');
        $responseWithout->assertSee('Préparation de la location');
    }
}
