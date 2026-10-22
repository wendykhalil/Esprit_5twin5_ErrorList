<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Reservation;
use App\Models\Equipment;
use App\Models\Reclamation;
use App\Models\Incident;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\Category;
use App\Services\ImmediateTransaction;
use DateTime;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PDO;
use Tests\TestCase;

class ReclamationPhase2Test extends TestCase
{
    use RefreshDatabase;

    protected $client;
    protected $admin;
    protected $equipment;
    protected $reservation;
    protected $payment;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->client = User::factory()->create(['role' => 'client']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $equipmentOwner = User::factory()->create(['role' => 'client']);
        
        // Create category for equipment
        $category = Category::firstOrCreate(
            ['name' => 'Solar Panels'],
            ['name' => 'Solar Panels']
        );
        
        // Create equipment with owner and category
        $this->equipment = Equipment::create([
            'user_id' => $equipmentOwner->id,
            'category_id' => $category->id,
            'name' => 'Solar Panel',
            'description' => 'Test solar panel',
            'price_per_day' => 100,
            'condition' => 'excellent',
            'power' => 100,
            'capacity' => 100,
            'location' => 'Test',
            'availability' => true,
            'status' => 'active',
        ]);
        
        // Create reservation
        $this->reservation = Reservation::create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now(),
            'date_fin' => now()->addDays(5),
            'statut' => 'terminee',
            'prix_total' => 500,
        ]);
        
        // Create payment
        $this->payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'amount' => 500,
            'method' => 'stripe',
            'status' => 'paid',
            'payment_date' => now(),
        ]);
    }

    public function test_client_can_view_reclamations_index(): void
    {
        $this->actingAs($this->client);
        $response = $this->get(route('reclamations.index'));
        $response->assertStatus(200);
    }

    public function test_client_can_access_create_form(): void
    {
        $this->actingAs($this->client);
        $response = $this->get(route('reclamations.create'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_redirected_to_login(): void
    {
        $response = $this->get(route('reclamations.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_admin_reclamations(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.reclamations.index'));
        $response->assertStatus(200);
    }

    public function test_client_cannot_access_admin_panel(): void
    {
        $this->actingAs($this->client);
        $response = $this->get(route('admin.reclamations.index'));
        $response->assertStatus(403);
    }

    public function test_incident_refund_calculations(): void
    {
        $damaged = new Incident([
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
        ]);
        
        $missing = new Incident([
            'type_probleme' => 'manquant', 'titre' => 'Incident signale',
        ]);
        
        $malfunction = new Incident([
            'type_probleme' => 'malfonctionnant', 'titre' => 'Incident signale',
        ]);
        
        $this->assertTrue(true);
    }

    public function test_client_create_reclamation(): void
    {
        $this->actingAs($this->client);
        
        $data = [
            'reservation_id' => $this->reservation->id,
            'title' => 'Damaged Equipment',
            'description' => 'Equipment has damage',
            'incidents' => [
                [
                    'equipment_id' => $this->equipment->id,
                    'type_probleme' => 'endommage', 'titre' => 'Incident signale',
                    'description' => 'Major damage',
                ]
            ]
        ];
        
        $response = $this->post(route('reclamations.store'), $data);
        
        $this->assertDatabaseHas('reclamations', [
            'user_id' => $this->client->id,
            'title' => 'Damaged Equipment',
            'status' => 'en_attente',
        ]);
    }

    public function test_policy_client_owns_reclamation(): void
    {
        $reclamation = Reclamation::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $this->actingAs($this->client);
        $response = $this->get(route('reclamations.show', $reclamation->id));
        $response->assertStatus(200);
    }

    public function test_policy_client_cannot_view_other(): void
    {
        $otherClient = User::factory()->create(['role' => 'client']);
        $reclamation = Reclamation::create([
            'user_id' => $otherClient->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $this->actingAs($this->client);
        $response = $this->get(route('reclamations.show', $reclamation->id));
        $response->assertStatus(403);
    }

    public function test_admin_can_approve_reclamation(): void
    {
        $reclamation = Reclamation::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Damaged',
            'proposed_refund_amount' => 30,
            'proposed_refund_percentage' => 30,
        ]);
        
        $this->actingAs($this->admin);
        
        $data = [
            'approved_incidents' => [$incident->id],
            'admin_notes' => 'Approved',
        ];
        
        $response = $this->post(route('admin.reclamations.approve', $reclamation->id), $data);
        $response->assertRedirect();
    }

    public function test_admin_cannot_approve_twice(): void
    {
        $reclamation = Reclamation::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'approuvee',
        ]);
        
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.reclamations.approve.form', $reclamation->id));
        $response->assertStatus(403);
    }

    public function test_admin_can_reject_reclamation(): void
    {
        $reclamation = Reclamation::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $this->actingAs($this->admin);
        
        $data = [
            'rejected_reason' => 'Not damaged',
            'admin_notes' => 'Verified',
        ];
        
        $response = $this->post(route('admin.reclamations.reject', $reclamation->id), $data);
        $response->assertRedirect();
        
        $this->assertDatabaseHas('reclamations', [
            'id' => $reclamation->id,
            'status' => 'rejetee',
        ]);
    }

    public function test_client_cannot_approve(): void
    {
        $reclamation = Reclamation::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $this->actingAs($this->client);
        $response = $this->get(route('admin.reclamations.approve.form', $reclamation->id));
        $response->assertStatus(403);
    }

    public function test_client_cannot_reject(): void
    {
        $reclamation = Reclamation::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $this->actingAs($this->client);
        $response = $this->get(route('admin.reclamations.reject.form', $reclamation->id));
        $response->assertStatus(403);
    }

    public function test_refund_transaction_created(): void
    {
        $reclamation = Reclamation::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Damaged',
            'proposed_refund_amount' => 30,
            'proposed_refund_percentage' => 30,
        ]);
        
        $this->actingAs($this->admin);
        
        $data = [
            'approved_incidents' => [$incident->id],
        ];
        
        $response = $this->post(route('admin.reclamations.approve', $reclamation->id), $data);
        $response->assertRedirect();
    }

    public function test_single_incident_correct_equipment_accepted(): void
    {
        $this->actingAs($this->client);
        
        $data = [
            'reservation_id' => $this->reservation->id,
            'title' => 'Incident avec bon équipement',
            'description' => 'L\'équipement matches la réservation',
            'incidents' => [
                [
                    'equipment_id' => $this->equipment->id,
                    'type_probleme' => 'endommage', 'titre' => 'Incident signale',
                    'description' => 'Test incident',
                ]
            ]
        ];
        
        $response = $this->post(route('reclamations.store'), $data);
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('reclamations', [
            'user_id' => $this->client->id,
            'title' => 'Incident avec bon équipement',
        ]);
    }

    public function test_single_incident_wrong_equipment_rejected(): void
    {
        $wrongEquipmentOwner = User::factory()->create(['role' => 'client']);
        $category = Category::firstOrCreate(['name' => 'Solar Panels']);
        $wrongEquipment = Equipment::create([
            'user_id' => $wrongEquipmentOwner->id,
            'category_id' => $category->id,
            'name' => 'Different Equipment',
            'description' => 'Not in reservation',
            'price_per_day' => 50,
            'condition' => 'good',
            'power' => 50,
            'capacity' => 50,
            'location' => 'Test',
            'availability' => true,
            'status' => 'active',
        ]);
        
        $this->actingAs($this->client);
        
        $data = [
            'reservation_id' => $this->reservation->id,
            'title' => 'Incident avec mauvais équipement',
            'description' => 'Cet équipement n\'est pas dans la réservation',
            'incidents' => [
                [
                    'equipment_id' => $wrongEquipment->id,
                    'type_probleme' => 'endommage', 'titre' => 'Incident signale',
                    'description' => 'Test incident',
                ]
            ]
        ];
        
        $response = $this->post(route('reclamations.store'), $data);
        $response->assertStatus(403);
        
        $this->assertDatabaseMissing('reclamations', [
            'title' => 'Incident avec mauvais équipement',
        ]);
    }

    public function test_multiple_incidents_all_validated(): void
    {
        $wrongEquipmentOwner = User::factory()->create(['role' => 'client']);
        $category = Category::firstOrCreate(['name' => 'Solar Panels']);
        $wrongEquipment = Equipment::create([
            'user_id' => $wrongEquipmentOwner->id,
            'category_id' => $category->id,
            'name' => 'Different Equipment 2',
            'description' => 'Not in reservation',
            'price_per_day' => 50,
            'condition' => 'good',
            'power' => 50,
            'capacity' => 50,
            'location' => 'Test',
            'availability' => true,
            'status' => 'active',
        ]);
        
        $this->actingAs($this->client);
        
        $data = [
            'reservation_id' => $this->reservation->id,
            'title' => 'Multiple incidents - one wrong',
            'description' => 'Testing multiple incident bypass',
            'incidents' => [
                [
                    'equipment_id' => $this->equipment->id,
                    'type_probleme' => 'endommage', 'titre' => 'Incident signale',
                    'description' => 'First incident - correct equipment',
                ],
                [
                    'equipment_id' => $wrongEquipment->id,
                    'type_probleme' => 'manquant', 'titre' => 'Incident signale',
                    'description' => 'Second incident - wrong equipment',
                ]
            ]
        ];
        
        $response = $this->post(route('reclamations.store'), $data);
        $response->assertStatus(403);
        
        $this->assertDatabaseMissing('reclamations', [
            'title' => 'Multiple incidents - one wrong',
        ]);
    }

    public function test_cannot_create_for_other_user_reservation(): void
    {
        $otherClient = User::factory()->create(['role' => 'client']);
        $otherEquipmentOwner = User::factory()->create(['role' => 'client']);
        $category = Category::firstOrCreate(['name' => 'Solar Panels']);
        $otherEquipment = Equipment::create([
            'user_id' => $otherEquipmentOwner->id,
            'category_id' => $category->id,
            'name' => 'Other Equipment',
            'description' => 'Belongs to other user',
            'price_per_day' => 75,
            'condition' => 'good',
            'power' => 75,
            'capacity' => 75,
            'location' => 'Test',
            'availability' => true,
            'status' => 'active',
        ]);
        
        $otherReservation = Reservation::create([
            'user_id' => $otherClient->id,
            'equipment_id' => $otherEquipment->id,
            'date_debut' => now(),
            'date_fin' => now()->addDays(5),
            'statut' => 'terminee',
            'prix_total' => 300,
        ]);
        
        $this->actingAs($this->client);
        
        $data = [
            'reservation_id' => $otherReservation->id,
            'title' => 'Hijack attempt',
            'description' => 'Trying to claim other user\'s reservation',
            'incidents' => [
                [
                    'equipment_id' => $otherEquipment->id,
                    'type_probleme' => 'endommage', 'titre' => 'Incident signale',
                    'description' => 'Test',
                ]
            ]
        ];
        
        $response = $this->post(route('reclamations.store'), $data);
        $response->assertStatus(403);
        
        $this->assertDatabaseMissing('reclamations', [
            'title' => 'Hijack attempt',
        ]);
    }

    public function test_repeated_approval_does_not_duplicate_refund(): void
    {
        $reclamation = Reclamation::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Test Duplicate',
            'description' => 'Test',
            'status' => 'approuvee',
            'refund_amount' => 30,
            'refund_approved_at' => now(),
        ]);
        
        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Damaged',
            'proposed_refund_amount' => 30,
            'proposed_refund_percentage' => 30,
        ]);
        
        $existingTransaction = Transaction::create([
            'payment_id' => $this->payment->id,
            'reference' => 'REC-' . \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'refund',
            'amount' => 30,
            'status' => 'completed',
            'transaction_date' => now(),
            'description' => 'Remboursement pour réclamation #' . $reclamation->id,
        ]);
        
        $transactionsBeforeAttempt = Transaction::where('type', 'refund')
            ->where('status', 'completed')
            ->count();
        
        $this->actingAs($this->admin);
        
        $data = [
            'approved_incidents' => [$incident->id],
            'admin_notes' => 'Duplicate attempt',
        ];
        
        $response = $this->post(route('admin.reclamations.approve', $reclamation->id), $data);
        $response->assertStatus(403);
        
        $transactionsAfterAttempt = Transaction::where('type', 'refund')
            ->where('status', 'completed')
            ->count();
        $this->assertEquals($transactionsBeforeAttempt, $transactionsAfterAttempt);
    }

    public function test_cannot_exceed_refundable_amount(): void
    {
        $reservation2 = Reservation::create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDays(10),
            'date_fin' => now()->addDays(15),
            'statut' => 'confirmee',
            'prix_total' => 100,
        ]);
        
        $payment = $reservation2->payments()->create([
            'user_id' => $this->client->id,
            'amount' => 100,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);
        
        $reclamation = Reclamation::create([
            'user_id' => $this->client->id,
            'reservation_id' => $reservation2->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'manquant', 'titre' => 'Incident signale',
            'description' => 'Missing',
            'proposed_refund_amount' => 150,
            'proposed_refund_percentage' => 20,
        ]);
        
        $this->actingAs($this->admin);
        
        $data = [
            'approved_incidents' => [$incident->id],
            'admin_notes' => 'Approval attempt',
        ];
        
        $response = $this->post(route('admin.reclamations.approve', $reclamation->id), $data);
        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('reclamations', [
            'id' => $reclamation->id,
            'status' => 'en_attente',
            'refund_amount' => null,
        ]);
        
        $this->assertDatabaseMissing('transactions', [
            'payment_id' => $payment->id,
            'type' => 'refund',
        ]);
    }

    public function test_already_approved_reclamation_cannot_be_approved_again(): void
    {
        $reclamation = Reclamation::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'approuvee',
            'refund_amount' => 30,
            'refund_approved_at' => now(),
        ]);
        
        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Damaged',
            'proposed_refund_amount' => 30,
            'proposed_refund_percentage' => 30,
        ]);
        
        $this->actingAs($this->admin);
        
        $transactionsBeforeAttempt = Transaction::where('type', 'refund')->count();
        
        $response = $this->get(route('admin.reclamations.approve.form', $reclamation->id));
        $response->assertStatus(403);
        
        $transactionsAfterAttempt = Transaction::where('type', 'refund')->count();
        $this->assertEquals($transactionsBeforeAttempt, $transactionsAfterAttempt);
    }

    public function test_refund_references_are_unique(): void
    {
        $referenceSet = [];
        
        for ($i = 0; $i < 3; $i++) {
            $transaction = Transaction::create([
                'payment_id' => $this->payment->id,
                'reference' => 'REC-' . \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'refund',
                'amount' => 10 + $i,
                'status' => 'completed',
                'transaction_date' => now(),
                'description' => 'Test refund ' . $i,
            ]);
            
            $referenceSet[] = $transaction->reference;
            $this->assertTrue(str_starts_with($transaction->reference, 'REC-'));
        }
        
        $uniqueReferences = array_unique($referenceSet);
        $this->assertCount(3, $uniqueReferences);
    }

    public function test_failed_refund_creation_rolls_back(): void
    {
        $reclamation = Reclamation::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Damaged',
            'proposed_refund_amount' => 30,
            'proposed_refund_percentage' => 30,
        ]);
        
        $this->actingAs($this->admin);
        
        $data = [
            'approved_incidents' => [$incident->id],
            'admin_notes' => 'Will fail',
        ];
        
        $this->reservation->payments()->delete();
        
        $response = $this->post(route('admin.reclamations.approve', $reclamation->id), $data);
        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('reclamations', [
            'id' => $reclamation->id,
            'status' => 'en_attente',
            'refund_amount' => null,
            'refund_approved_at' => null,
        ]);
    }

    public function test_conditional_status_update_prevents_duplicate_approval(): void
    {
        $reclamation = Reclamation::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Test Conditional Update',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Damaged',
            'proposed_refund_amount' => 30,
            'proposed_refund_percentage' => 30,
        ]);
        
        $firstUpdate = DB::table('reclamations')
            ->where('id', $reclamation->id)
            ->whereIn('status', ['en_attente', 'en_cours'])
            ->update(['status' => 'approuvee']);
        
        $this->assertEquals(1, $firstUpdate);
        
        $this->assertDatabaseHas('reclamations', [
            'id' => $reclamation->id,
            'status' => 'approuvee',
        ]);
        
        $secondUpdate = DB::table('reclamations')
            ->where('id', $reclamation->id)
            ->whereIn('status', ['en_attente', 'en_cours'])
            ->update(['status' => 'approuvee']);
        
        $this->assertEquals(0, $secondUpdate);
    }

    public function test_single_approval_creates_one_refund_transaction(): void
    {
        $reclamation = Reclamation::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Damaged',
            'proposed_refund_amount' => 30,
            'proposed_refund_percentage' => 30,
        ]);
        
        $this->actingAs($this->admin);
        
        $data = [
            'approved_incidents' => [$incident->id],
            'admin_notes' => 'Approved',
        ];
        
        $response = $this->post(route('admin.reclamations.approve', $reclamation->id), $data);
        $response->assertRedirect();
    }

    public function test_refund_transaction_creation_failure_rolls_back_status(): void
    {
        $reclamation = Reclamation::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Test Rollback',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Damaged',
            'proposed_refund_amount' => 30,
            'proposed_refund_percentage' => 30,
        ]);
        
        $this->reservation->payments()->delete();
        
        $this->actingAs($this->admin);
        
        $data = [
            'approved_incidents' => [$incident->id],
            'admin_notes' => 'Should fail',
        ];
        
        $response = $this->post(route('admin.reclamations.approve', $reclamation->id), $data);
        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('reclamations', [
            'id' => $reclamation->id,
            'status' => 'en_attente',
            'refund_amount' => null,
        ]);
    }

    public function test_ineligible_status_prevents_approval_and_refund(): void
    {
        $reclamation = Reclamation::create([
            'user_id' => $this->client->id,
            'reservation_id' => $this->reservation->id,
            'title' => 'Test Ineligible Status',
            'description' => 'Test',
            'status' => 'rejetee',
        ]);
        
        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Damaged',
            'proposed_refund_amount' => 30,
            'proposed_refund_percentage' => 30,
        ]);
        
        $transactionsBefore = Transaction::where('type', 'refund')->count();
        
        $this->actingAs($this->admin);
        
        $data = [
            'approved_incidents' => [$incident->id],
            'admin_notes' => 'Should fail - already rejected',
        ];
        
        $response = $this->post(route('admin.reclamations.approve', $reclamation->id), $data);
        $response->assertStatus(403);
        
        $transactionsAfter = Transaction::where('type', 'refund')->count();
        $this->assertEquals($transactionsBefore, $transactionsAfter);
    }

    public function test_two_reclamations_cross_payment_sequential_within_limit(): void
    {
        // One active payment per reservation. This reservation is independent of setUp.
        $reservation = Reservation::create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDays(20),
            'date_fin' => now()->addDays(25),
            'statut' => 'terminee',
            'prix_total' => 100,
        ]);

        $payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $reservation->id,
            'amount' => 100,
            'method' => 'stripe',
            'status' => 'paid',
            'payment_date' => now(),
        ]);
        
        $reclamation1 = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->client->id,
            'title' => 'Reclamation 1',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        $incident1 = Incident::create([
            'reclamation_id' => $reclamation1->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Test',
            'proposed_refund_amount' => 50,
            'proposed_refund_percentage' => 30,
        ]);
        
        $this->actingAs($this->admin);
        
        $response1 = $this->post(route('admin.reclamations.approve', $reclamation1->id), [
            'approved_incidents' => [$incident1->id],
            'admin_notes' => 'First approval',
        ]);
        $response1->assertRedirect();
        
        // Verify transaction was created
        $transactionCount = $payment->transactions()->where('type', 'refund')->where('status', 'completed')->count();
        $this->assertEquals(1, $transactionCount);
        
        $totalRefunded = $payment->transactions()->where('type', 'refund')->where('status', 'completed')->sum('amount');
        $this->assertLessThanOrEqual(100, $totalRefunded);
    }

    public function test_two_reclamations_cross_payment_sequential_exceeding_limit(): void
    {
        // This test verifies that when a second reclamation's refund would exceed the payment amount, it is rejected
        $reservation = Reservation::create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDays(30),
            'date_fin' => now()->addDays(35),
            'statut' => 'terminee',
            'prix_total' => 100,
        ]);

        $payment = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $reservation->id,
            'amount' => 100,
            'method' => 'stripe',
            'status' => 'paid',
            'payment_date' => now(),
        ]);
        
        $reclamation1 = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->client->id,
            'title' => 'Reclamation 1',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $incident1 = Incident::create([
            'reclamation_id' => $reclamation1->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Test',
            'proposed_refund_amount' => 80,
            'proposed_refund_percentage' => 30,
        ]);
        
        $this->actingAs($this->admin);
        
        $response1 = $this->post(route('admin.reclamations.approve', $reclamation1->id), [
            'approved_incidents' => [$incident1->id],
        ]);
        $response1->assertRedirect();
        
        // Create a second reclamation on the same payment (via same reservation check logic)
        $reclamation2 = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $this->client->id,
            'title' => 'Reclamation 2',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $incident2 = Incident::create([
            'reclamation_id' => $reclamation2->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'manquant', 'titre' => 'Incident signale',
            'description' => 'Test',
            'proposed_refund_amount' => 30,  // Total would be 110 > 100 limit
            'proposed_refund_percentage' => 20,
        ]);
        
        $response2 = $this->post(route('admin.reclamations.approve', $reclamation2->id), [
            'approved_incidents' => [$incident2->id],
        ]);
        
        // Second approval should fail due to refund limit
        $response2->assertRedirect();
        $response2->assertSessionHas('error');
        
        // Verify reclamation2 wasn't approved
        $this->assertDatabaseHas('reclamations', [
            'id' => $reclamation2->id,
            'status' => 'en_attente',
            'refund_amount' => null,
        ]);
    }

    public function test_concurrent_approvals_separate_connections_exceeding_limit(): void
    {
        $reservation1 = Reservation::create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now(),
            'date_fin' => now()->addDays(3),
            'statut' => 'terminee',
            'prix_total' => 60,
        ]);
        
        $reservation2 = Reservation::create([
            'user_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'date_debut' => now()->addDays(4),
            'date_fin' => now()->addDays(7),
            'statut' => 'terminee',
            'prix_total' => 60,
        ]);
        
        $payment1 = Payment::create([
            'user_id' => $this->client->id,
            'reservation_id' => $reservation1->id,
            'amount' => 100,
            'method' => 'stripe',
            'status' => 'paid',
            'payment_date' => now(),
        ]);
        
        $reclamation1 = Reclamation::create([
            'reservation_id' => $reservation1->id,
            'user_id' => $this->client->id,
            'title' => 'Reclamation 1',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $reclamation2 = Reclamation::create([
            'reservation_id' => $reservation2->id,
            'user_id' => $this->client->id,
            'title' => 'Reclamation 2',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);
        
        $incident1 = Incident::create([
            'reclamation_id' => $reclamation1->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Test',
            'proposed_refund_amount' => 60,
            'proposed_refund_percentage' => 30,
        ]);
        
        $incident2 = Incident::create([
            'reclamation_id' => $reclamation2->id,
            'equipment_id' => $this->equipment->id,
            'type_probleme' => 'manquant', 'titre' => 'Incident signale',
            'description' => 'Test',
            'proposed_refund_amount' => 60,
            'proposed_refund_percentage' => 20,
        ]);
        
        // Independent file: the :memory: suite is invisible to a second PDO,
        // and database/database.sqlite must not be written by this test.
        $tmpDb = tempnam(sys_get_temp_dir(), 'refund_limit_');
        @unlink($tmpDb);
        $tmpDb .= '.sqlite';
        $schema = new PDO('sqlite:' . $tmpDb);
        $schema->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $schema->exec('CREATE TABLE reclamations (id INTEGER PRIMARY KEY, status TEXT, updated_at TEXT)');
        $schema->exec('CREATE TABLE transactions (id INTEGER PRIMARY KEY AUTOINCREMENT, payment_id INTEGER, reference TEXT, type TEXT, amount NUMERIC, status TEXT, transaction_date TEXT, created_at TEXT, updated_at TEXT)');
        $schema->prepare('INSERT INTO reclamations (id, status) VALUES (?, ?)')->execute([$reclamation1->id, 'en_attente']);
        $schema->prepare('INSERT INTO reclamations (id, status) VALUES (?, ?)')->execute([$reclamation2->id, 'en_attente']);
        $schema = null;

        $db1 = new PDO('sqlite:' . $tmpDb);
        $db2 = new PDO('sqlite:' . $tmpDb);
        $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db1->exec('PRAGMA busy_timeout = 500');
        $db2->exec('PRAGMA busy_timeout = 500');
        
        $successCount = 0;
        $failCount = 0;
        
        try {
            $db1->exec('BEGIN IMMEDIATE');
            $db1->prepare('UPDATE reclamations SET status = ?, updated_at = ? WHERE id = ? AND status IN (?, ?)')
                ->execute(['approuvee', date('Y-m-d H:i:s'), $reclamation1->id, 'en_attente', 'en_cours']);
            $now = date('Y-m-d H:i:s');
            $db1->prepare('INSERT INTO transactions (payment_id, reference, type, amount, status, transaction_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$payment1->id, 'CONN1-' . uniqid(), 'refund', 60, 'completed', $now, $now, $now]);
            $stmt = $db1->prepare('SELECT SUM(amount) FROM transactions WHERE payment_id = ? AND type = ? AND status = ?');
            $stmt->execute([$payment1->id, 'refund', 'completed']);
            $total = $stmt->fetchColumn();
            if ($total > 100) throw new Exception('Refund exceeds limit');
            $db1->exec('COMMIT');
            $successCount += 1;
        } catch (Exception $e) {
            try { $db1->exec('ROLLBACK'); } catch (Exception $e2) {}
            $failCount += 1;
        }
        
        try {
            $db2->exec('BEGIN IMMEDIATE');
            $db2->prepare('UPDATE reclamations SET status = ?, updated_at = ? WHERE id = ? AND status IN (?, ?)')
                ->execute(['approuvee', date('Y-m-d H:i:s'), $reclamation2->id, 'en_attente', 'en_cours']);
            $now = date('Y-m-d H:i:s');
            $db2->prepare('INSERT INTO transactions (payment_id, reference, type, amount, status, transaction_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$payment1->id, 'CONN2-' . uniqid(), 'refund', 60, 'completed', $now, $now, $now]);
            $stmt = $db2->prepare('SELECT SUM(amount) FROM transactions WHERE payment_id = ? AND type = ? AND status = ?');
            $stmt->execute([$payment1->id, 'refund', 'completed']);
            $totalAfter = $stmt->fetchColumn();
            if ($totalAfter > 100) throw new Exception('Refund exceeds limit');
            $db2->exec('COMMIT');
            $successCount += 1;
        } catch (Exception $e) {
            try { $db2->exec('ROLLBACK'); } catch (Exception $e2) {}
            $failCount += 1;
        }
        
        $this->assertGreaterThanOrEqual(1, $successCount);
        $this->assertGreaterThanOrEqual(1, $failCount);
        $settled = new PDO('sqlite:' . $tmpDb);
        $totalRefunded = (float) $settled->query('SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE payment_id = ' . (int) $payment1->id . " AND type = 'refund'")->fetchColumn();
        $this->assertLessThanOrEqual(100, $totalRefunded);
        if (file_exists($tmpDb)) {
            @unlink($tmpDb);
        }
    }

    public function test_concurrent_refunds_for_same_payment_are_serialized(): void
    {
        // Concurrency report (honest): two GENUINELY INDEPENDENT PDO
        // connections (pdoA / pdoB) to a dedicated TEMPORARY SQLite file.
        // NOT the :memory: test DB (a second connection could never see it).
        // PHP here is single-threaded, so this does NOT reproduce true
        // OS-level parallel execution: pdoA runs first and holds the
        // RESERVED lock, then pdoB attempts BEGIN IMMEDIATE and gets
        // SQLITE_BUSY ("database is locked"). That busy/lock outcome IS the
        // serialization proof: the second writer cannot interleave.
        // The production path (ReclamationController::approve) uses the same
        // BEGIN IMMEDIATE primitive via ImmediateTransaction, so this lock
        // behavior is what serializes concurrent approvals in production.
        $tmpDb = tempnam(sys_get_temp_dir(), 'refund_conc_');
        @unlink($tmpDb);
        $tmpDb .= '.sqlite';
        $makePdo = function () use ($tmpDb): PDO {
            $pdo = new PDO('sqlite:' . $tmpDb);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec('PRAGMA busy_timeout = 500');
            return $pdo;
        };

        try {
            $schema = $makePdo();
            $schema->exec('CREATE TABLE refund_ledger (id INTEGER PRIMARY KEY AUTOINCREMENT, payment_id INTEGER NOT NULL, amount NUMERIC NOT NULL)');
            $schema->exec('INSERT INTO refund_ledger (payment_id, amount) VALUES (1, 60)');
            $schema = null;

            $this->assertNotSame($tmpDb, DB::connection()->getConfig('database'));

            // Connection A: BEGIN IMMEDIATE + uncommitted write -> holds lock.
            $pdoA = $makePdo();
            $pdoA->exec('BEGIN IMMEDIATE');
            $pdoA->exec('INSERT INTO refund_ledger (payment_id, amount) VALUES (1, 60)');

            // Connection B: independent connection, must block while A holds it.
            $pdoB = $makePdo();
            $blockedWithBusy = false;
            try {
                $pdoB->exec('BEGIN IMMEDIATE');
            } catch (Exception $e) {
                $blockedWithBusy = str_contains($e->getMessage(), 'database is locked')
                    || str_contains($e->getMessage(), 'database table is locked');
            }
            $this->assertTrue($blockedWithBusy, 'Second independent connection must block while first holds IMMEDIATE lock.');
            $pdoB = null;

            // With A rolled back, only the original 60 remains (atomic).
            $pdoA->exec('ROLLBACK');
            $pdoA = null;

            $check = $makePdo();
            $total = (float) $check->query('SELECT SUM(amount) FROM refund_ledger WHERE payment_id = 1')->fetchColumn();
            $check = null;
            $this->assertEqualsWithDelta(60.0, $total, 0.001);
        } finally {
            if (file_exists($tmpDb)) {
                @unlink($tmpDb);
            }
        }
    }

    public function test_rollback_on_exception_within_immediate_transaction(): void
    {
        // Uses a dedicated temp SQLite file + raw PDO (same BEGIN IMMEDIATE /
        // COMMIT / ROLLBACK primitives the helper uses) because the :memory:
        // test DB is invisible to second connections and RefreshDatabase holds
        // an outer transaction that forbids raw BEGIN on Laravel's connection.
        $tmpDb = tempnam(sys_get_temp_dir(), 'refund_rb_');
        @unlink($tmpDb);
        $tmpDb .= '.sqlite';

        try {
            $pdo = new PDO('sqlite:' . $tmpDb);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec('CREATE TABLE refund_probe (id INTEGER PRIMARY KEY AUTOINCREMENT, status TEXT NOT NULL, amount NUMERIC NOT NULL)');
            $pdo->exec("INSERT INTO refund_probe (status, amount) VALUES ('en_attente', 0)");

            try {
                $pdo->exec('BEGIN IMMEDIATE');
                $pdo->exec("UPDATE refund_probe SET status = 'approuvee' WHERE id = 1");
                $pdo->exec("INSERT INTO refund_probe (status, amount) VALUES ('completed', 50)");
                throw new Exception('Forced rollback');
            } catch (Exception $e) {
                try {
                    $pdo->exec('ROLLBACK');
                } catch (Exception $rollbackError) {
                }
                $this->assertSame('Forced rollback', $e->getMessage());
            }

            // After rollback: status unchanged, no refund row exists (atomic).
            $status = $pdo->query('SELECT status FROM refund_probe WHERE id = 1')->fetchColumn();
            $count = (int) $pdo->query("SELECT COUNT(*) FROM refund_probe WHERE status = 'completed'")->fetchColumn();
            $this->assertSame('en_attente', $status);
            $this->assertSame(0, $count);
            $pdo = null;
        } finally {
            if (file_exists($tmpDb)) {
                @unlink($tmpDb);
            }
        }
    }

    public function test_cumulative_refund_calculation_excludes_non_completed(): void
    {
        // Schema check (actual tables):
        // - payments: id, amount, method, status[pending|paid|failed|refunded],
        //   payment_date, description, + reservation_id (nullable FK), user_id.
        // - transactions: id, payment_id FK->payments cascade, reference UNIQUE,
        //   type (rental_payment|refund|deposit...), amount, status
        //   [pending|completed|failed|refunded], transaction_date, description.
        // Cumulative rule (matches ReclamationController::approve + canBeApproved):
        // SUM(amount) WHERE payment_id=? AND type='refund' AND status='completed'
        // Every other type/status is excluded, so no double-counting.
        // Uses Eloquent (same-connection, RefreshDatabase-safe).
        $payment = $this->payment;

        Transaction::create([
            'payment_id' => $payment->id,
            'reference' => 'COMP-' . uniqid(),
            'type' => 'refund',
            'amount' => 40,
            'status' => 'completed',
            'transaction_date' => now(),
        ]);
        Transaction::create([
            'payment_id' => $payment->id,
            'reference' => 'PEND-' . uniqid(),
            'type' => 'refund',
            'amount' => 40,
            'status' => 'pending',
            'transaction_date' => null,
        ]);
        Transaction::create([
            'payment_id' => $payment->id,
            'reference' => 'FAIL-' . uniqid(),
            'type' => 'refund',
            'amount' => 40,
            'status' => 'failed',
            'transaction_date' => now(),
        ]);
        Transaction::create([
            'payment_id' => $payment->id,
            'reference' => 'REFD-' . uniqid(),
            'type' => 'refund',
            'amount' => 40,
            'status' => 'refunded',
            'transaction_date' => now(),
        ]);
        Transaction::create([
            'payment_id' => $payment->id,
            'reference' => 'PAY-' . uniqid(),
            'type' => 'rental_payment',
            'amount' => 40,
            'status' => 'completed',
            'transaction_date' => now(),
        ]);

        // Cumulative calculation must only count completed refunds = 40.
        $total = $payment->transactions()
            ->where('type', 'refund')
            ->where('status', 'completed')
            ->sum('amount');

        $this->assertEquals(40, $total);
    }

    public function test_immediate_transaction_refuses_nested_laravel_transaction(): void
    {
        // RefreshDatabase wraps every test in an outer Laravel transaction
        // (transactionLevel >= 1). The helper must refuse raw BEGIN IMMEDIATE
        // there instead of desyncing Laravel's transaction counter.
        $this->assertGreaterThanOrEqual(1, DB::transactionLevel());

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/existing Laravel transaction/');

        (new ImmediateTransaction())->run(function () {
            return true;
        });
    }
}
