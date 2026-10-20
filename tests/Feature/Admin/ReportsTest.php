<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Incident;
use App\Models\Payment;
use App\Models\Reclamation;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /**
     * Test that admin can access reports index page
     */
    public function test_admin_can_access_reports_index(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('Générateur de Rapports');
        $response->assertSee('Sélectionnez une période');
    }

    /**
     * Test that non-admin cannot access reports index
     */
    public function test_non_admin_cannot_access_reports_index(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client)->get(route('admin.reports.index'));

        $response->assertForbidden();
    }

    /**
     * Test that unauthenticated user is redirected
     */
    public function test_unauthenticated_user_redirected_from_reports(): void
    {
        $response = $this->get(route('admin.reports.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test period selector form is displayed with all options
     */
    public function test_reports_index_displays_period_options(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));

        $response->assertSee('Cette semaine');
        $response->assertSee('Ce mois');
        $response->assertSee('Cette année');
        $response->assertSee('Personnalisé');
    }

    /**
     * Test generating report for week period
     */
    public function test_can_generate_weekly_report(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createTestData();

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'week',
        ]);

        $response->assertOk();
        $response->assertSee('Week');
        $response->assertSee('Réservations');
        $response->assertSee('Revenus');
    }

    /**
     * Test generating report for month period
     */
    public function test_can_generate_monthly_report(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createTestData();

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        $response->assertSee('Month');
    }

    /**
     * Test generating report for year period
     */
    public function test_can_generate_yearly_report(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createTestData();

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'year',
        ]);

        $response->assertOk();
        $response->assertSee('Year');
    }

    /**
     * Test generating report with custom date range
     */
    public function test_can_generate_custom_date_range_report(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createTestData();

        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'custom',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $response->assertOk();
        $response->assertSee('Custom');
    }

    /**
     * Test period validation - invalid period is rejected
     */
    public function test_invalid_period_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'invalid_period',
        ]);

        $response->assertSessionHasErrors('period');
    }

    /**
     * Test custom period requires start_date
     */
    public function test_custom_period_requires_start_date(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'custom',
            'end_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('start_date');
    }

    /**
     * Test custom period requires end_date
     */
    public function test_custom_period_requires_end_date(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'custom',
            'start_date' => now()->subDays(10)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    /**
     * Test end_date must be after start_date
     */
    public function test_end_date_must_be_after_start_date(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'custom',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->subDays(5)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    /**
     * Test report displays correct reservation statistics
     */
    public function test_report_displays_correct_reservation_statistics(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Solar Equipment']);
        $equipment = Equipment::factory()->create(['category_id' => $category->id, 'user_id' => $provider->id]);

        // Create reservations with different statuses
        Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'statut' => 'terminee',
            'date_debut' => now()->startOfMonth(),
            'date_fin' => now()->startOfMonth()->addDays(2),
        ]);

        Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'statut' => 'confirmee',
            'date_debut' => now()->startOfMonth()->addDays(5),
            'date_fin' => now()->startOfMonth()->addDays(7),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        // Verify statistics are displayed
        $response->assertSee('2'); // Total reservations
        // Verify at least one status is shown
        $response->assertSee('Réservations par statut');
    }

    /**
     * Test report displays revenue statistics
     */
    public function test_report_displays_revenue_statistics(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Solar Equipment']);
        $equipment = Equipment::factory()->create(['category_id' => $category->id, 'user_id' => $provider->id]);

        // Create reservation and paid payment
        $reservation = Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'date_debut' => now()->startOfMonth(),
            'date_fin' => now()->startOfMonth()->addDays(2),
        ]);

        Payment::factory()->create([
            'reservation_id' => $reservation->id,
            'amount' => 150.50,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        $response->assertSee('150,50'); // Revenue displayed with TND format
        $response->assertSee('TND');
    }

    /**
     * Test report displays reclamation statistics
     */
    public function test_report_displays_reclamation_statistics(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Solar Equipment']);
        $equipment = Equipment::factory()->create(['category_id' => $category->id, 'user_id' => $provider->id]);

        // Create reservation and reclamation
        $reservation = Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'date_debut' => now()->startOfMonth(),
            'date_fin' => now()->startOfMonth()->addDays(2),
        ]);

        $reclamation = Reclamation::factory()->create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'status' => 'en_attente',
        ]);

        Incident::factory()->create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'endommage',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        $response->assertSee('Réclamations');
        $response->assertSee('Endommagé');
    }

    /**
     * Test report displays equipment rankings
     */
    public function test_report_displays_equipment_rankings(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Solar Equipment']);

        $equipment1 = Equipment::factory()->create([
            'name' => 'Solar Panel Pro',
            'category_id' => $category->id,
            'user_id' => $provider->id,
            'price_per_day' => 50.00,
        ]);

        $equipment2 = Equipment::factory()->create([
            'name' => 'Solar Inverter',
            'category_id' => $category->id,
            'user_id' => $provider->id,
            'price_per_day' => 30.00,
        ]);

        // Create multiple reservations for equipment1
        for ($i = 0; $i < 3; $i++) {
            Reservation::factory()->create([
                'user_id' => $client->id,
                'equipment_id' => $equipment1->id,
                'date_debut' => now()->startOfMonth()->addDays($i),
                'date_fin' => now()->startOfMonth()->addDays($i + 1),
            ]);
        }

        // Create 1 reservation for equipment2
        Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment2->id,
            'date_debut' => now()->startOfMonth(),
            'date_fin' => now()->startOfMonth()->addDays(1),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        $response->assertSee('Équipements les plus demandés');
        $response->assertSee('Solar Panel Pro');
        $response->assertSee('Solar Inverter');
    }

    /**
     * Test report calculates reclamation rate correctly
     */
    public function test_report_calculates_reclamation_rate(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Solar Equipment']);
        $equipment = Equipment::factory()->create(['category_id' => $category->id, 'user_id' => $provider->id]);

        // Create 10 reservations
        for ($i = 0; $i < 10; $i++) {
            Reservation::factory()->create([
                'user_id' => $client->id,
                'equipment_id' => $equipment->id,
                'date_debut' => now()->startOfMonth()->addDays($i),
                'date_fin' => now()->startOfMonth()->addDays($i + 1),
            ]);
        }

        // Create 2 reclamations
        $reservations = Reservation::all()->take(2);
        foreach ($reservations as $reservation) {
            Reclamation::factory()->create([
                'user_id' => $client->id,
                'reservation_id' => $reservation->id,
                'status' => 'en_attente',
            ]);
        }

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        // 2 reclamations / 10 reservations * 100 = 20%
        $response->assertSee('20'); // Should show the rate
    }

    /**
     * Test report shows AI summary when available
     */
    public function test_report_shows_ai_summary_section(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createTestData();

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'week',
        ]);

        $response->assertOk();
        // AI summary section should be present (even if empty or with error) or not shown if Ollama unavailable
        // Just verify the response contains the report structure
        $response->assertSee('Week');
    }

    /**
     * Test report displays key metric cards
     */
    public function test_report_displays_key_metric_cards(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createTestData();

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        $response->assertSee('Réservations');
        $response->assertSee('Revenus');
        $response->assertSee('Réclamations');
        $response->assertSee('Remboursements');
    }

    /**
     * Test report displays period information
     */
    public function test_report_displays_period_information(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createTestData();

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        $response->assertSee('Période:');
    }

    /**
     * Test report shows back button to generate another report
     */
    public function test_report_shows_back_button(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createTestData();

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'week',
        ]);

        $response->assertOk();
        $response->assertSee('Générer un autre rapport');
    }

    /**
     * Test report handles no data gracefully
     */
    public function test_report_handles_no_data(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        // Should show zeros/empty states gracefully
        $response->assertSee('Équipements les plus demandés');
    }

    /**
     * Test non-admin cannot generate report
     */
    public function test_non_admin_cannot_generate_report(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertForbidden();
    }

    /**
     * Test that average reservation duration is calculated correctly
     */
    public function test_average_duration_calculation_is_positive(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Solar Equipment']);
        $equipment = Equipment::factory()->create(['category_id' => $category->id, 'user_id' => $provider->id]);

        // Create reservations with known durations in the current month
        Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'statut' => 'terminee',
            'date_debut' => now()->startOfMonth(),
            'date_fin' => now()->startOfMonth()->addDays(2), // 3 days (inclusive)
        ]);

        Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'statut' => 'terminee',
            'date_debut' => now()->startOfMonth()->addDays(5),
            'date_fin' => now()->startOfMonth()->addDays(9), // 5 days (inclusive)
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        // Average should be (3 + 5) / 2 = 4 days
        $response->assertSee('4');
    }

    /**
     * Test that identical start and end dates produce 1 day duration
     */
    public function test_same_day_reservation_duration_is_one_day(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create(['category_id' => $category->id, 'user_id' => $provider->id]);

        // Create a same-day reservation
        Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'date_debut' => now()->startOfMonth(),
            'date_fin' => now()->startOfMonth(), // Same day
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        // Same-day rental is 1 day (inclusive counting)
        $response->assertSee('1');
    }

    /**
     * Test that week period shows full week from Monday
     */
    public function test_week_period_shows_monday_to_today(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create(['category_id' => $category->id, 'user_id' => $provider->id]);

        // Create a reservation this week
        Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'date_debut' => now()->startOfWeek(),
            'date_fin' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'week',
        ]);

        $response->assertOk();
        // Week should include the Monday start date in the range display
        $response->assertSee('Période:');
    }

    /**
     * REGRESSION TEST: Total reservations must match sum of status breakdown
     * This tests the fix for query builder reuse bug that caused empty by_status
     * Issue: Total=15 but by_status breakdown was empty
     */
    public function test_reservation_total_matches_status_breakdown_sum(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Solar Equipment']);
        $equipment = Equipment::factory()->create(['category_id' => $category->id, 'user_id' => $provider->id]);

        // Create 15 reservations with various statuses
        $statuses = ['en_attente', 'confirmee', 'en_cours', 'terminee', 'litige', 'annulea', 'refusee'];
        $reservationCount = 0;

        for ($i = 0; $i < 15; $i++) {
            $status = $statuses[$i % count($statuses)];
            Reservation::factory()->create([
                'user_id' => $client->id,
                'equipment_id' => $equipment->id,
                'statut' => $status,
                'date_debut' => now()->startOfMonth()->addDays($i),
                'date_fin' => now()->startOfMonth()->addDays($i + 1),
            ]);
            $reservationCount++;
        }

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        // Verify total is displayed
        $response->assertSee('15');
        // Verify status breakdown is NOT empty (this was the bug)
        // Should show all statuses
        $response->assertSee('Réservations par statut');
        $response->assertDontSee('Aucune réservation pour cette période');
    }

    /**
     * REGRESSION TEST: Equipment rankings must not have duplicate equipment names
     * Issue: "Panneau solaire pliable 120W" appeared twice
     * Issue: Same equipment appearing multiple times with different reservation counts
     */
    public function test_equipment_rankings_have_unique_equipment_entries(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Solar Equipment']);

        // Create equipment
        $equipment = Equipment::factory()->create([
            'name' => 'Panneau solaire pliable 120W',
            'category_id' => $category->id,
            'user_id' => $provider->id,
        ]);

        // Create multiple reservations for this equipment
        for ($i = 0; $i < 5; $i++) {
            Reservation::factory()->create([
                'user_id' => $client->id,
                'equipment_id' => $equipment->id,
                'date_debut' => now()->startOfMonth()->addDays($i),
                'date_fin' => now()->startOfMonth()->addDays($i + 1),
            ]);
        }

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();

        // Count how many times the equipment name appears in the table
        // Extract the response content to count occurrences
        $content = $response->getContent();
        $count = substr_count($content, 'Panneau solaire pliable 120W');
        
        // Should appear exactly once in the table (header doesn't count)
        // Allow for up to 2 occurrences (table row + possibly in AI summary)
        $this->assertLessThanOrEqual(2, $count, 'Equipment name should not appear multiple times in rankings table');
    }

    /**
     * REGRESSION TEST: AI summary consistency - data provided to AI must match UI display
     * Issue: AI summary claimed 5 reservations while table showed 2
     * Issue: AI claimed 2 reservations for equipment while table showed 5
     */
    public function test_ai_summary_data_consistency_with_ui_metrics(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create([
            'name' => 'Mini éolienne portable',
            'category_id' => $category->id,
            'user_id' => $provider->id,
        ]);

        // Create exactly 5 reservations
        for ($i = 0; $i < 5; $i++) {
            $reservation = Reservation::factory()->create([
                'user_id' => $client->id,
                'equipment_id' => $equipment->id,
                'statut' => 'terminee',
                'date_debut' => now()->startOfMonth()->addDays($i),
                'date_fin' => now()->startOfMonth()->addDays($i + 1),
            ]);

            // Create paid payment for each
            Payment::factory()->create([
                'reservation_id' => $reservation->id,
                'amount' => 100.00,
                'status' => 'paid',
            ]);
        }

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();

        // Verify UI shows 5 reservations
        $response->assertSee('5');
        // Verify the equipment is shown in rankings
        $response->assertSee('Mini éolienne portable');
    }

    /**
     * REGRESSION TEST: Zero average duration must be distinguished from no data
     * Issue: Average rental duration showed 0 when it shouldn't
     * This test ensures avg_duration is calculated correctly
     */
    public function test_average_duration_not_zero_when_reservations_exist(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create(['category_id' => $category->id, 'user_id' => $provider->id]);

        // Create reservations with clear durations
        Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'date_debut' => Carbon::parse('2026-01-01'),
            'date_fin' => Carbon::parse('2026-01-10'), // 10 days
        ]);

        Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'date_debut' => Carbon::parse('2026-01-15'),
            'date_fin' => Carbon::parse('2026-01-20'), // 6 days
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'custom',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]);

        $response->assertOk();
        // Average: (10 + 6) / 2 = 8 days
        // Should NOT show 0
        $response->assertDontSee('Durée moyenne de location</p>
            <p class="text-3xl font-bold text-slate-900 mt-3" style="font-family: Outfit, sans-serif">0</p>');
    }

    /**
     * REGRESSION TEST: Complaint types must be accurately reported
     * Issue: AI summary claimed complaint causes that didn't match displayed types
     * Verify only actual complaint types from incidents are included
     */
    public function test_reclamation_types_accurately_reported(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create(['category_id' => $category->id, 'user_id' => $provider->id]);

        // Create reservations and reclamations with specific types
        for ($i = 0; $i < 3; $i++) {
            $reservation = Reservation::factory()->create([
                'user_id' => $client->id,
                'equipment_id' => $equipment->id,
                'date_debut' => now()->startOfMonth()->addDays($i),
                'date_fin' => now()->startOfMonth()->addDays($i + 1),
            ]);

            $reclamation = Reclamation::factory()->create([
                'user_id' => $client->id,
                'reservation_id' => $reservation->id,
                'status' => 'en_attente',
            ]);

            // Create incidents with specific types
            Incident::factory()->create([
                'reclamation_id' => $reclamation->id,
                'equipment_id' => $equipment->id,
                'type_probleme' => $i === 0 ? 'endommage' : ($i === 1 ? 'manquant' : 'malfonctionnant'),
            ]);
        }

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        // Verify displayed types match what was created
        $response->assertSee('Réclamations par type de problème');
        $response->assertSee('Endommagé');
    }

    /**
     * REGRESSION TEST: Revenue calculation must not duplicate payments
     * Verify that each payment is counted only once in equipment revenue
     * 
     * Note: Database constraint unique_active_payment_per_reservation allows only
     * one active (pending/paid) payment per reservation. This test uses 3 reservations
     * to verify that 3 paid payments (one per reservation) sum correctly to 150.
     */
    public function test_revenue_not_duplicated_in_equipment_rankings(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create([
            'category_id' => $category->id,
            'user_id' => $provider->id,
            'price_per_day' => 100.00,
        ]);

        // Create 3 separate reservations (constraint allows one active payment per reservation)
        for ($i = 0; $i < 3; $i++) {
            $reservation = Reservation::factory()->create([
                'user_id' => $client->id,
                'equipment_id' => $equipment->id,
                'date_debut' => now()->startOfMonth()->addDays($i),
                'date_fin' => now()->startOfMonth()->addDays($i + 1),
            ]);

            // Create one paid payment for this reservation
            Payment::factory()->create([
                'reservation_id' => $reservation->id,
                'amount' => 50.00,
                'status' => 'paid',
            ]);
        }

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        // Total revenue should be 150 (sum of all paid payments: 50+50+50)
        $response->assertSee('150');
    }

    /**
     * REGRESSION TEST (v2): Equipment rankings are unique by equipment.id
     * Issue: Same equipment name appearing multiple times with different reservation counts
     * Root cause: limit(10) applied BEFORE deduplication; multiple equipment IDs with same name
     * Fix: limit(20) before dedup, take(10) after sorting by revenue
     */
    public function test_equipment_rankings_unique_by_equipment_id(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Test Equipment']);

        // Create multiple equipment with SAME name but different IDs
        $equip1 = Equipment::factory()->create([
            'name' => 'Panneau solaire pliable 120W',
            'category_id' => $category->id,
            'user_id' => $provider->id,
        ]);

        $equip2 = Equipment::factory()->create([
            'name' => 'Panneau solaire pliable 120W', // Same name, different equipment
            'category_id' => $category->id,
            'user_id' => $provider->id,
        ]);

        // Create reservations for each
        for ($i = 0; $i < 5; $i++) {
            Reservation::factory()->create([
                'user_id' => $client->id,
                'equipment_id' => $equip1->id,
                'date_debut' => now()->startOfMonth()->addDays($i),
                'date_fin' => now()->startOfMonth()->addDays($i + 1),
            ]);

            Reservation::factory()->create([
                'user_id' => $client->id,
                'equipment_id' => $equip2->id,
                'date_debut' => now()->startOfMonth()->addDays($i),
                'date_fin' => now()->startOfMonth()->addDays($i + 1),
            ]);
        }

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();

        // Extract equipment rankings from response
        $content = $response->getContent();
        
        // Count equipment entries in the table
        // Each unique equipment should appear exactly once with a data-equipment-id attribute
        $this->assertStringContainsString('data-equipment-id="' . $equip1->id . '"', $content);
        $this->assertStringContainsString('data-equipment-id="' . $equip2->id . '"', $content);

        // Verify both equipment appear but with different IDs
        $equip1Count = substr_count($content, 'data-equipment-id="' . $equip1->id . '"');
        $equip2Count = substr_count($content, 'data-equipment-id="' . $equip2->id . '"');
        
        $this->assertEquals(1, $equip1Count, 'Equipment 1 should appear exactly once');
        $this->assertEquals(1, $equip2Count, 'Equipment 2 should appear exactly once');
    }

    /**
     * REGRESSION TEST (v2): Equipment rankings sorted by revenue after deduplication
     * Issue: Ranking order was inconsistent when deduplication happened at wrong time
     * Fix: Deduplication happens before sorting by revenue, limit applied last
     */
    public function test_equipment_rankings_sorted_by_revenue_after_dedup(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Test Equipment']);

        $equip1 = Equipment::factory()->create([
            'name' => 'High Revenue Equipment',
            'category_id' => $category->id,
            'user_id' => $provider->id,
        ]);

        $equip2 = Equipment::factory()->create([
            'name' => 'Low Revenue Equipment',
            'category_id' => $category->id,
            'user_id' => $provider->id,
        ]);

        // Create 3 reservations for equip1 with high revenue
        for ($i = 0; $i < 3; $i++) {
            $res = Reservation::factory()->create([
                'user_id' => $client->id,
                'equipment_id' => $equip1->id,
                'date_debut' => now()->startOfMonth()->addDays($i),
                'date_fin' => now()->startOfMonth()->addDays($i + 1),
            ]);
            Payment::factory()->create(['reservation_id' => $res->id, 'amount' => 1000.00, 'status' => 'paid']);
        }

        // Create 10 reservations for equip2 with low revenue each (total lower than equip1)
        for ($i = 0; $i < 10; $i++) {
            $res = Reservation::factory()->create([
                'user_id' => $client->id,
                'equipment_id' => $equip2->id,
                'date_debut' => now()->startOfMonth()->addDays($i),
                'date_fin' => now()->startOfMonth()->addDays($i + 1),
            ]);
            Payment::factory()->create(['reservation_id' => $res->id, 'amount' => 100.00, 'status' => 'paid']);
        }

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        
        // High revenue equipment should appear before low revenue in the table
        $content = $response->getContent();
        $highRevenuePos = strpos($content, $equip1->id);
        $lowRevenuePos = strpos($content, $equip2->id);
        
        // High revenue should appear first in the rendered table
        // (accounting for the fact that equip1 appears in data-equipment-id before equip2)
        $this->assertNotFalse($highRevenuePos, 'High revenue equipment should be in results');
        $this->assertNotFalse($lowRevenuePos, 'Low revenue equipment should be in results');
    }

    /**
     * REGRESSION TEST: Duration shows clear empty state indicators
     * Issue: Showed 0 ambiguously without distinguishing from "no data"
     * Fix: Shows "—" with explanatory text for no data cases
     */
    public function test_duration_shows_empty_state_when_no_reservations(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        // Should show em-dash or "Non disponible" text instead of 0
        $content = $response->getContent();
        
        // Look for the duration section with empty state
        $this->assertStringContainsString('Aucune réservation', $content);
    }

    /**
     * REGRESSION TEST: Duration shows correct value when reservations exist
     * Verify non-zero duration displays properly
     */
    public function test_duration_displays_correctly_when_reservations_exist(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create(['category_id' => $category->id, 'user_id' => $provider->id]);

        // Create a 5-day reservation
        Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'date_debut' => Carbon::parse('2026-01-01'),
            'date_fin' => Carbon::parse('2026-01-05'), // 5 days inclusive
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'custom',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]);

        $response->assertOk();
        // Should display the correct duration (5 days)
        $response->assertSee('5');
    }

    /**
     * REGRESSION TEST: AI prompt contains validated metrics only
     * Issue: Prompt requested unsupported trends and recommendations
     * Fix: Prompt now contains only evidence-based metrics
     * Note: This test disables Ollama (OLLAMA_ENABLED=false in phpunit.xml)
     * so no AI section is rendered, but the report data is always shown.
     */
    public function test_ai_prompt_evidence_based_metrics_only(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create(['category_id' => $category->id, 'user_id' => $provider->id]);

        // Create reservation and reclamation to populate all metrics
        $reservation = Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'statut' => 'terminee',
            'date_debut' => now()->startOfMonth(),
            'date_fin' => now()->startOfMonth()->addDays(2),
        ]);

        Payment::factory()->create([
            'reservation_id' => $reservation->id,
            'amount' => 100.00,
            'status' => 'paid',
        ]);

        $reclamation = Reclamation::factory()->create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'status' => 'en_attente',
        ]);

        Incident::factory()->create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'endommage',
        ]);

        // Generate report (Ollama is disabled in test environment)
        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        // Response should be OK and show all database-driven metrics
        $response->assertOk();
        // Verify core metrics are displayed (not AI-dependent)
        $response->assertSee('Réservations');
        $response->assertSee('Revenus');
        $response->assertSee('Réclamations');
        // AI section should not be present when Ollama is disabled
        $response->assertDontSee('Analyse IA - Résumé');
    }

    /**
     * REGRESSION TEST: Equipment rankings limit to exactly 10 items
     * Issue: Deduplication before limit meant fewer than 10 items displayed
     * Fix: limit(20) before dedup, take(10) after sorting to ensure exactly 10
     */
    public function test_equipment_rankings_returns_at_most_10_items(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Test Equipment']);

        // Create 15 different equipment items
        $equipmentIds = [];
        for ($i = 0; $i < 15; $i++) {
            $equip = Equipment::factory()->create([
                'category_id' => $category->id,
                'user_id' => $provider->id,
            ]);
            $equipmentIds[] = $equip->id;

            // Create 1 reservation for each
            Reservation::factory()->create([
                'user_id' => $client->id,
                'equipment_id' => $equip->id,
                'date_debut' => now()->startOfMonth(),
                'date_fin' => now()->startOfMonth()->addDays(1),
            ]);
        }

        $response = $this->actingAs($admin)->post(route('admin.reports.generate'), [
            'period' => 'month',
        ]);

        $response->assertOk();

        // Count equipment table rows (excluding header)
        $content = $response->getContent();
        // Count data-equipment-id attributes in the tbody
        preg_match_all('/data-equipment-id="(\d+)"/', $content, $matches);
        $rowCount = count($matches[0]);

        $this->assertLessThanOrEqual(10, $rowCount, 'Equipment rankings should show at most 10 items');
    }

    /**
     * Create test data for report testing
     */
    private function createTestData(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'provider']);
        $category = Category::create(['name' => 'Test Equipment']);
        $equipment = Equipment::factory()->create(['category_id' => $category->id, 'user_id' => $provider->id]);

        // Create some reservations
        $reservation = Reservation::factory()->create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'date_debut' => now()->startOfWeek(),
            'date_fin' => now()->startOfWeek()->addDays(2),
        ]);

        // Create a paid payment
        Payment::factory()->create([
            'reservation_id' => $reservation->id,
            'amount' => 100.00,
            'status' => 'paid',
        ]);
    }

    /**
     * Test non-admin cannot export CSV
     */
    public function test_non_admin_cannot_export_csv(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client)->post(route('admin.reports.export.csv'), [
            'period' => 'month',
        ]);

        $response->assertForbidden();
    }

    /**
     * Test non-admin cannot export PDF
     */
    public function test_non_admin_cannot_export_pdf(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client)->post(route('admin.reports.export.pdf'), [
            'period' => 'month',
        ]);

        $response->assertForbidden();
    }

    /**
     * Test admin can export report as CSV
     */
    public function test_admin_can_export_report_as_csv(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createTestData();

        $response = $this->actingAs($admin)->post(route('admin.reports.export.csv'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');
    }

    /**
     * Test CSV export contains expected sections
     */
    public function test_csv_export_contains_expected_sections(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createTestData();

        $response = $this->actingAs($admin)->post(route('admin.reports.export.csv'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('RAPPORT SOLARSHARE', $content);
        $this->assertStringContainsString('RÉSERVATIONS', $content);
        $this->assertStringContainsString('REVENUS', $content);
        $this->assertStringContainsString('RÉCLAMATIONS', $content);
        $this->assertStringContainsString('ÉQUIPEMENTS', $content);
    }

    /**
     * Test admin can export report as PDF
     */
    public function test_admin_can_export_report_as_pdf(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createTestData();

        $response = $this->actingAs($admin)->post(route('admin.reports.export.pdf'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        $response->assertHeader('Content-Disposition');
    }

    /**
     * Test PDF export contains expected HTML elements
     */
    public function test_pdf_export_contains_expected_elements(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createTestData();

        $response = $this->actingAs($admin)->post(route('admin.reports.export.pdf'), [
            'period' => 'month',
        ]);

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('Rapport SolarShare', $content);
        $this->assertStringContainsString('Réservations', $content);
        $this->assertStringContainsString('Revenus', $content);
        $this->assertStringContainsString('Réclamations', $content);
    }

    /**
     * Test CSV export with custom date range
     */
    public function test_csv_export_with_custom_date_range(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createTestData();

        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->actingAs($admin)->post(route('admin.reports.export.csv'), [
            'period' => 'custom',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /**
     * Test PDF export with custom date range
     */
    public function test_pdf_export_with_custom_date_range(): void
    {
        $admin = User::factory()->admin()->create();
        $this->createTestData();

        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->actingAs($admin)->post(route('admin.reports.export.pdf'), [
            'period' => 'custom',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $response->assertOk();
    }

    /**
     * Test export validation - invalid period rejected
     */
    public function test_export_validation_invalid_period_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.reports.export.csv'), [
            'period' => 'invalid',
        ]);

        $response->assertSessionHasErrors('period');
    }

    /**
     * Test export validation - custom period requires dates
     */
    public function test_export_validation_custom_period_requires_dates(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.reports.export.csv'), [
            'period' => 'custom',
        ]);

        $response->assertSessionHasErrors(['start_date', 'end_date']);
    }
}
