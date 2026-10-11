<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Reclamation;
use App\Models\Reservation;
use App\Models\Equipment;
use App\Models\Category;
use App\Models\Incident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReclamationAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /**
     * Test that admin can access reclamations index
     */
    public function test_admin_can_access_reclamations_index(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.reclamations.index'));

        $response->assertOk();
        $response->assertSee('Réclamations');
    }

    /**
     * Test that non-admin cannot access reclamations index
     */
    public function test_non_admin_cannot_access_reclamations_index(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client)->get(route('admin.reclamations.index'));

        $response->assertForbidden();
    }

    /**
     * Test that admin can see reclamation in list when it exists
     */
    public function test_admin_can_see_reclamation_in_list(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = \App\Models\Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create([
            'category_id' => $category->id,
            'user_id' => $provider->id,
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
        ]);

        $reclamation = Reclamation::factory()->create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'Test Reclamation Visible',
            'status' => 'en_attente',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reclamations.index'));

        $response->assertOk();
        $response->assertSee('Test Reclamation Visible');
        $response->assertSee($client->name);
    }

    /**
     * Test that admin can access reclamation detail page
     */
    public function test_admin_can_view_reclamation_details(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = \App\Models\Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create([
            'category_id' => $category->id,
            'user_id' => $provider->id,
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
        ]);

        $reclamation = Reclamation::factory()->create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'Detailed Test Reclamation',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reclamations.show', $reclamation));

        $response->assertOk();
        $response->assertSee('Detailed Test Reclamation');
    }

    /**
     * Test that admin can see associated incidents in detail page
     */
    public function test_admin_can_see_incidents_in_reclamation_detail(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = \App\Models\Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create([
            'category_id' => $category->id,
            'price_per_day' => 100,
            'user_id' => $provider->id,
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
        ]);

        $reclamation = Reclamation::factory()->create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'Reclamation with Incidents',
        ]);

        // Create incident with unique description to test visibility
        $incident = Incident::factory()->create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'endommage',
            'description' => 'UNIQUE_INCIDENT_DESCRIPTION_XYZ',
            'proposed_refund_percentage' => 30,
            'proposed_refund_amount' => 30,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reclamations.show', $reclamation));

        $response->assertOk();
        $response->assertSee('UNIQUE_INCIDENT_DESCRIPTION_XYZ');
    }

    /**
     * Test that admin can filter reclamations by status
     */
    public function test_admin_can_filter_reclamations_by_status(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = \App\Models\Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create([
            'category_id' => $category->id,
            'user_id' => $provider->id,
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
        ]);

        // Create reclamations with different statuses
        $attente_reclamation = Reclamation::factory()->create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'UNIQUE_ATTENTE_TITLE_ABC123',
            'status' => 'en_attente',
        ]);

        $approuvee_reclamation = Reclamation::factory()->create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'UNIQUE_APPROUVEE_TITLE_XYZ789',
            'status' => 'approuvee',
        ]);

        // Filter by en_attente
        $response = $this->actingAs($admin)
            ->get(route('admin.reclamations.index', ['status' => 'en_attente']));

        $response->assertOk();
        $response->assertSee('UNIQUE_ATTENTE_TITLE_ABC123');
        // The "Approuvée" title should not appear in the table rows
        $response->assertDontSee('UNIQUE_APPROUVEE_TITLE_XYZ789');
    }

    /**
     * Test that client cannot access admin reclamation list
     */
    public function test_client_cannot_access_admin_reclamation_list(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client)->get(route('admin.reclamations.index'));

        $response->assertForbidden();
    }

    /**
     * Test that unauthenticated user cannot access reclamations
     */
    public function test_unauthenticated_user_cannot_access_reclamations(): void
    {
        $response = $this->get(route('admin.reclamations.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that admin can access the approval form page
     */
    public function test_admin_can_access_approval_form_page(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = \App\Models\Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create([
            'category_id' => $category->id,
            'price_per_day' => 100,
            'user_id' => $provider->id,
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
        ]);

        $reclamation = Reclamation::factory()->create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'Approval Form Test',
            'status' => 'en_attente',
        ]);

        // Create incident so there's something to approve
        Incident::factory()->create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'endommage',
            'description' => 'Equipment damaged',
            'proposed_refund_percentage' => 30,
            'proposed_refund_amount' => 30,
        ]);

        // Access approval form page
        $response = $this->actingAs($admin)->get(route('admin.reclamations.approve.form', $reclamation->id));

        // Should load successfully (HTTP 200)
        $response->assertOk();
        // Should contain the reclamation title
        $response->assertSee('Approval Form Test');
        // Should display payment information section
        $response->assertSee('Paiement Original');
    }

    /**
     * Test that approval form page handles missing payment gracefully
     */
    public function test_approval_form_page_handles_missing_payment(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = \App\Models\Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create([
            'category_id' => $category->id,
            'user_id' => $provider->id,
        ]);

        // Create a reservation WITHOUT payment
        $reservation = Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
        ]);

        $reclamation = Reclamation::factory()->create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'No Payment Reclamation',
            'status' => 'en_attente',
        ]);

        Incident::factory()->create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'endommage',
            'proposed_refund_amount' => 50,
        ]);

        // Access approval form page - should not crash
        $response = $this->actingAs($admin)->get(route('admin.reclamations.approve.form', $reclamation->id));

        // Should load successfully (HTTP 200)
        $response->assertOk();
        // Should display "Aucun paiement trouvé"
        $response->assertSee('Aucun paiement trouvé');
    }

    /**
     * Test that admin can submit approval form with valid compensation code data
     */
    public function test_admin_can_submit_approval_form_with_valid_data(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = \App\Models\Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create([
            'category_id' => $category->id,
            'price_per_day' => 100,
            'user_id' => $provider->id,
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
        ]);

        $reclamation = Reclamation::factory()->create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'Approval Test Reclamation',
            'status' => 'en_attente',
        ]);

        $incident = Incident::factory()->create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'endommage',
            'description' => 'Equipment damaged during rental',
            'proposed_refund_percentage' => 30,
            'proposed_refund_amount' => 30,
        ]);

        // Submit approval form with valid data
        $response = $this->actingAs($admin)->post(route('admin.reclamations.approve', $reclamation->id), [
            'type' => 'percentage',
            'value' => 30,
            'days_valid' => 30,
            'max_uses' => 1,
            'admin_notes' => 'Approved due to equipment damage',
            'approved_incidents' => [$incident->id],
        ]);

        // Should redirect to reclamation show page
        $response->assertRedirect(route('admin.reclamations.show', $reclamation->id));
        $response->assertSessionHas('success');

        // Verify reclamation status changed to 'approuvee'
        $this->assertDatabaseHas('reclamations', [
            'id' => $reclamation->id,
            'status' => 'approuvee',
            'admin_notes' => 'Approved due to equipment damage',
        ]);

        // Verify compensation code was created
        $compensationCode = \App\Models\CompensationCode::where('reclamation_id', $reclamation->id)->first();
        $this->assertNotNull($compensationCode);
        $this->assertEquals('percentage', $compensationCode->type);
        $this->assertEquals(30, $compensationCode->value);
        $this->assertEquals($client->id, $compensationCode->user_id);
    }

    /**
     * Test that approval form validates required fields
     */
    public function test_approval_form_validates_required_fields(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = \App\Models\Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create([
            'category_id' => $category->id,
            'user_id' => $provider->id,
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
        ]);

        $reclamation = Reclamation::factory()->create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'Validation Test',
            'status' => 'en_attente',
        ]);

        Incident::factory()->create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
        ]);

        // Submit form with missing required fields
        $response = $this->actingAs($admin)->post(route('admin.reclamations.approve', $reclamation->id), [
            // All required fields missing
            'admin_notes' => 'Missing compensation code fields',
            'approved_incidents' => [],
        ]);

        // Should redirect back with validation errors
        $response->assertRedirect();
        $response->assertSessionHasErrors(['type', 'value', 'days_valid', 'max_uses']);

        // Reclamation should still be in 'en_attente' status
        $this->assertDatabaseHas('reclamations', [
            'id' => $reclamation->id,
            'status' => 'en_attente',
        ]);

        // No compensation code should be created
        $this->assertDatabaseMissing('compensation_codes', [
            'reclamation_id' => $reclamation->id,
        ]);
    }

    /**
     * Test that approval form validates compensation code constraints
     */
    public function test_approval_form_validates_compensation_code_constraints(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = \App\Models\Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create([
            'category_id' => $category->id,
            'user_id' => $provider->id,
        ]);

        $reservation = Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
        ]);

        $reclamation = Reclamation::factory()->create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'Constraint Validation Test',
            'status' => 'en_attente',
        ]);

        Incident::factory()->create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
        ]);

        // Test 1: Percentage value > 100%
        $response = $this->actingAs($admin)->post(route('admin.reclamations.approve', $reclamation->id), [
            'type' => 'percentage',
            'value' => 150,  // Invalid: > 100%
            'days_valid' => 30,
            'max_uses' => 1,
            'approved_incidents' => [],
        ]);

        $response->assertSessionHasErrors('value');

        // Test 2: Invalid days_valid (outside 1-365 range)
        $response = $this->actingAs($admin)->post(route('admin.reclamations.approve', $reclamation->id), [
            'type' => 'percentage',
            'value' => 30,
            'days_valid' => 400,  // Invalid: > 365
            'max_uses' => 1,
            'approved_incidents' => [],
        ]);

        $response->assertSessionHasErrors('days_valid');

        // Test 3: Invalid max_uses (< 1)
        $response = $this->actingAs($admin)->post(route('admin.reclamations.approve', $reclamation->id), [
            'type' => 'percentage',
            'value' => 30,
            'days_valid' => 30,
            'max_uses' => 0,  // Invalid: < 1
            'approved_incidents' => [],
        ]);

        $response->assertSessionHasErrors('max_uses');

        // Reclamation should still be in 'en_attente' status
        $this->assertDatabaseHas('reclamations', [
            'id' => $reclamation->id,
            'status' => 'en_attente',
        ]);

        // No compensation code should be created
        $this->assertDatabaseMissing('compensation_codes', [
            'reclamation_id' => $reclamation->id,
        ]);
    }
}
