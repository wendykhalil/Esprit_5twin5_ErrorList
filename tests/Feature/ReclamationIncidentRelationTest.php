<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Incident;
use App\Models\Reclamation;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReclamationIncidentRelationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup test data
     */
    private function setupTestData()
    {
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::firstOrCreate(['name' => 'Solar Equipment']);

        $equipment = Equipment::factory()->create([
            'user_id' => $provider->id,
            'category_id' => $category->id,
            'price_per_day' => 100.00,
        ]);

        $reservation = Reservation::create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'date_debut' => now()->addDay(),
            'date_fin' => now()->addDays(5),
            'statut' => 'confirmee',
            'prix_total' => 500.00,
        ]);

        return compact('client', 'provider', 'equipment', 'reservation', 'category');
    }

    /**
     * Test: Reclamation can be created with reservation and user
     */
    public function test_reclamation_can_be_created()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $reclamation = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $client->id,
            'title' => 'Équipement endommagé',
            'description' => 'Le panneau solaire a une fissure importante',
            'status' => 'en_attente',
        ]);

        $this->assertInstanceOf(Reclamation::class, $reclamation);
        $this->assertEquals($reservation->id, $reclamation->reservation_id);
        $this->assertEquals($client->id, $reclamation->user_id);
        $this->assertEquals('en_attente', $reclamation->status);
    }

    /**
     * Test: One-to-Many: Reclamation has many Incidents
     */
    public function test_reclamation_has_many_incidents()
    {
        ['client' => $client, 'reservation' => $reservation, 'equipment' => $equipment] = $this->setupTestData();

        $reclamation = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $client->id,
            'title' => 'Dommages multiples',
            'description' => 'Plusieurs problèmes détectés',
            'status' => 'en_attente',
        ]);

        $incident1 = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'endommage',
            'description' => 'Fissure sur le panneau',
            'proposed_refund_percentage' => 30.0,
            'proposed_refund_amount' => 30.00,
        ]);

        $incident2 = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'manquant',
            'description' => 'Câble manquant',
            'proposed_refund_percentage' => 20.0,
            'proposed_refund_amount' => 20.00,
        ]);

        $this->assertCount(2, $reclamation->incidents);
        $this->assertTrue($reclamation->incidents->contains($incident1));
        $this->assertTrue($reclamation->incidents->contains($incident2));
    }

    /**
     * Test: Incident belongs to Reclamation
     */
    public function test_incident_belongs_to_reclamation()
    {
        ['client' => $client, 'reservation' => $reservation, 'equipment' => $equipment] = $this->setupTestData();

        $reclamation = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $client->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);

        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'endommage',
            'description' => 'Test incident',
            'proposed_refund_percentage' => 30.0,
            'proposed_refund_amount' => 30.00,
        ]);

        $this->assertInstanceOf(Reclamation::class, $incident->reclamation);
        $this->assertEquals($reclamation->id, $incident->reclamation->id);
    }

    /**
     * Test: Reclamation belongs to User
     */
    public function test_reclamation_belongs_to_user()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $reclamation = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $client->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);

        $this->assertInstanceOf(User::class, $reclamation->user);
        $this->assertEquals($client->id, $reclamation->user->id);
    }

    /**
     * Test: Reclamation belongs to Reservation
     */
    public function test_reclamation_belongs_to_reservation()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $reclamation = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $client->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);

        $this->assertInstanceOf(Reservation::class, $reclamation->reservation);
        $this->assertEquals($reservation->id, $reclamation->reservation->id);
    }

    /**
     * Test: Incident belongs to Equipment
     */
    public function test_incident_belongs_to_equipment()
    {
        ['client' => $client, 'reservation' => $reservation, 'equipment' => $equipment] = $this->setupTestData();

        $reclamation = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $client->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);

        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'endommage',
            'description' => 'Test',
            'proposed_refund_percentage' => 30.0,
            'proposed_refund_amount' => 30.00,
        ]);

        $this->assertInstanceOf(Equipment::class, $incident->equipment);
        $this->assertEquals($equipment->id, $incident->equipment->id);
    }

    /**
     * Test: User can have many Reclamations
     */
    public function test_user_can_have_many_reclamations()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $reclamation1 = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $client->id,
            'title' => 'Réclamation 1',
            'description' => 'Description 1',
            'status' => 'en_attente',
        ]);

        // Create another reservation for second reclamation
        $reservation2 = Reservation::create([
            'user_id' => $client->id,
            'equipment_id' => $reservation->equipment_id,
            'date_debut' => now()->addDays(10),
            'date_fin' => now()->addDays(15),
            'statut' => 'confirmee',
            'prix_total' => 500.00,
        ]);

        $reclamation2 = Reclamation::create([
            'reservation_id' => $reservation2->id,
            'user_id' => $client->id,
            'title' => 'Réclamation 2',
            'description' => 'Description 2',
            'status' => 'en_attente',
        ]);

        $this->assertCount(2, $client->reclamations);
        $this->assertTrue($client->reclamations->contains($reclamation1));
        $this->assertTrue($client->reclamations->contains($reclamation2));
    }

    /**
     * Test: Reservation can have many Reclamations
     */
    public function test_reservation_can_have_many_reclamations()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $reclamation1 = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $client->id,
            'title' => 'Réclamation 1',
            'description' => 'Description 1',
            'status' => 'en_attente',
        ]);

        $reclamation2 = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $client->id,
            'title' => 'Réclamation 2',
            'description' => 'Description 2',
            'status' => 'rejetee',
        ]);

        $this->assertCount(2, $reservation->reclamations);
        $this->assertTrue($reservation->reclamations->contains($reclamation1));
        $this->assertTrue($reservation->reclamations->contains($reclamation2));
    }

    /**
     * Test: Foreign key cascade delete - delete Reclamation cascade deletes Incidents
     */
    public function test_deleting_reclamation_cascade_deletes_incidents()
    {
        ['client' => $client, 'reservation' => $reservation, 'equipment' => $equipment] = $this->setupTestData();

        $reclamation = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $client->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);

        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'endommage',
            'description' => 'Test',
            'proposed_refund_percentage' => 30.0,
            'proposed_refund_amount' => 30.00,
        ]);

        $incidentId = $incident->id;
        $reclamation->delete();

        $this->assertNull(Incident::find($incidentId));
    }

    /**
     * Test: Foreign key cascade delete - delete Reservation cascade deletes Reclamations
     */
    public function test_deleting_reservation_cascade_deletes_reclamations()
    {
        ['client' => $client, 'reservation' => $reservation] = $this->setupTestData();

        $reclamation = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $client->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);

        $reclamationId = $reclamation->id;
        $reservation->delete();

        $this->assertNull(Reclamation::find($reclamationId));
    }

    /**
     * Test: Incident type constants
     */
    public function test_incident_type_constants()
    {
        $this->assertContains('endommage', Incident::TYPES);
        $this->assertContains('manquant', Incident::TYPES);
        $this->assertContains('malfonctionnant', Incident::TYPES);
        $this->assertContains('autre', Incident::TYPES);
    }

    /**
     * Test: Reclamation status constants
     */
    public function test_reclamation_status_constants()
    {
        $this->assertContains('en_attente', Reclamation::STATUSES);
        $this->assertContains('en_cours', Reclamation::STATUSES);
        $this->assertContains('approuvee', Reclamation::STATUSES);
        $this->assertContains('rejetee', Reclamation::STATUSES);
        $this->assertContains('fermee', Reclamation::STATUSES);
    }

    /**
     * Test: Reclamation calculateTotalRefund method
     */
    public function test_reclamation_calculate_total_refund()
    {
        ['client' => $client, 'reservation' => $reservation, 'equipment' => $equipment] = $this->setupTestData();

        $reclamation = Reclamation::create([
            'reservation_id' => $reservation->id,
            'user_id' => $client->id,
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);

        Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'endommage',
            'description' => 'Damage 1',
            'proposed_refund_percentage' => 30.0,
            'proposed_refund_amount' => 30.00,
        ]);

        Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'manquant',
            'description' => 'Missing item',
            'proposed_refund_percentage' => 20.0,
            'proposed_refund_amount' => 20.00,
        ]);

        $total = $reclamation->calculateTotalRefund();
        $this->assertEquals(50.00, $total);
    }
}
