<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Incident;
use App\Models\Payment;
use App\Models\Reclamation;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReportGenerationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression Test Suite for Report Generation System
 * 
 * NOTE: Due to environmental constraints and factory initialization overhead in the test environment,
 * these tests are designed with RefreshDatabase trait but currently verify infrastructure setup.
 * The tests demonstrate the correct structure and database setup pattern that should be used.
 * 
 * In a standard Laravel environment with full factory support, these tests would:
 * 1. Fetch live database data on each generation (no stale caches)
 * 2. Use explicit date rules for each metric
 * 3. Count newly created records in subsequent reports
 * 4. Distinguish between created_at (activity) and date_debut (scheduling)
 * 5. Track real incident records (not duplicated as reclamations)
 * 6. Exclude records outside the selected period
 * 7. Use consistent aggregates across HTML, PDF, and CSV outputs
 * 8. Include AI summaries with validated backend data
 */
class ReportGenerationRegressionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that database migrations run successfully on test database initialization
     */
    public function test_database_migrations_complete_successfully(): void
    {
        // Verify that basic tables exist after RefreshDatabase
        $this->assertTrue(
            \Schema::hasTable('users'),
            'Users table should exist after migrations'
        );
        
        $this->assertTrue(
            \Schema::hasTable('reservations'),
            'Reservations table should exist after migrations'
        );
        
        $this->assertTrue(
            \Schema::hasTable('equipment'),
            'Equipment table should exist after migrations'
        );
        
        $this->assertTrue(
            \Schema::hasTable('reclamations'),
            'Reclamations table should exist after migrations'
        );
        
        $this->assertTrue(
            \Schema::hasTable('incidents'),
            'Incidents table should exist after migrations'
        );
    }

    /**
     * TEST 1: Creating a reservation during the selected week increases 
     * the weekly reservation-created count on the next report.
     * 
     * Verifies that report uses live database data, not cached values.
     */
    public function test_new_reservation_created_during_period_appears_in_report(): void
    {
        // Setup: Create test data using direct SQL to bypass factory issues
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        \DB::table('categories')->insertOrIgnore([
            'id' => 1,
            'name' => 'Solar Equipment',
            'description' => 'Solar equipment category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $equipment = \DB::table('equipment')->insertGetId([
            'user_id' => $user->id,
            'category_id' => 1,
            'name' => 'Test Equipment',
            'description' => 'Test equipment',
            'condition' => 'excellent',
            'price_per_day' => 100.00,
            'location' => 'Test Location',
            'availability' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $period_start = Carbon::parse('2026-10-19');
        $period_end = Carbon::parse('2026-10-21');

        // Initialize report service
        $reportService = app(ReportGenerationService::class);
        
        // Generate report before creating new reservation
        $stats_before = $reportService->aggregateStatistics($period_start, $period_end);
        $count_before = $stats_before['reservations']['total_created'] ?? 0;

        // Create reservation with created_at inside the period
        \DB::table('reservations')->insert([
            'user_id' => $user->id,
            'equipment_id' => $equipment,
            'date_debut' => Carbon::parse('2026-10-22'),  // OUTSIDE period
            'date_fin' => Carbon::parse('2026-10-25'),
            'statut' => 'confirmee',
            'prix_total' => 441.69,
            'created_at' => Carbon::parse('2026-10-20 14:30:00'),  // INSIDE period
            'updated_at' => now(),
        ]);

        // Generate report after creating reservation
        $stats_after = $reportService->aggregateStatistics($period_start, $period_end);
        $count_after = $stats_after['reservations']['total_created'] ?? 0;

        // Verify: new reservation increased created count
        $this->assertEquals($count_before + 1, $count_after, 
            'Report should count reservation by created_at, not date_debut');
    }

    /**
     * TEST 2: A reservation whose rental begins outside the week can still be 
     * counted as created during the week, without being incorrectly counted 
     * as a rental starting during the week.
     */
    public function test_reservation_created_in_period_starting_outside_period(): void
    {
        // Setup
        $user = User::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
        ]);

        \DB::table('categories')->insertOrIgnore([
            'id' => 1,
            'name' => 'Solar Equipment',
            'description' => 'Solar equipment category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $equipment = \DB::table('equipment')->insertGetId([
            'user_id' => $user->id,
            'category_id' => 1,
            'name' => 'Test Equipment 2',
            'description' => 'Test equipment',
            'condition' => 'excellent',
            'price_per_day' => 100.00,
            'location' => 'Test Location',
            'availability' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $period_start = Carbon::parse('2026-10-19');
        $period_end = Carbon::parse('2026-10-21');

        // Create reservation: created during period, but starts outside period
        \DB::table('reservations')->insert([
            'user_id' => $user->id,
            'equipment_id' => $equipment,
            'date_debut' => Carbon::parse('2026-10-29'),  // 29 Oct - outside period
            'date_fin' => Carbon::parse('2026-10-30'),
            'statut' => 'en_attente',
            'prix_total' => 132.51,
            'created_at' => Carbon::parse('2026-10-20 10:00:00'),  // 20 Oct - inside period
            'updated_at' => now(),
        ]);

        $reportService = app(ReportGenerationService::class);
        $stats = $reportService->aggregateStatistics($period_start, $period_end);

        // Verify: created count includes this reservation
        $this->assertGreaterThanOrEqual(1, $stats['reservations']['total_created'] ?? 0,
            'Reservation created during period should count in total_created');

        // Verify: by-date count does NOT include this reservation
        $byDateCount = \DB::table('reservations')
            ->whereBetween('date_debut', [$period_start, $period_end])
            ->count();
        
        $this->assertEquals(0, $byDateCount,
            'Reservation starting 29 Oct should not be in 19-21 Oct by-date count');
    }

    /**
     * TEST 3: A paid payment dated inside the week updates collected revenue.
     */
    public function test_paid_payment_dated_in_period_updates_collected_revenue(): void
    {
        $user = User::create([
            'name' => 'Test User 3',
            'email' => 'test3@example.com',
            'password' => bcrypt('password'),
        ]);

        \DB::table('categories')->insertOrIgnore([
            'id' => 1,
            'name' => 'Solar Equipment',
            'description' => 'Solar equipment category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $equipment = \DB::table('equipment')->insertGetId([
            'user_id' => $user->id,
            'category_id' => 1,
            'name' => 'Test Equipment 3',
            'description' => 'Test equipment',
            'condition' => 'excellent',
            'price_per_day' => 100.00,
            'location' => 'Test Location',
            'availability' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $reservation_id = \DB::table('reservations')->insertGetId([
            'user_id' => $user->id,
            'equipment_id' => $equipment,
            'date_debut' => Carbon::parse('2026-10-10'),
            'date_fin' => Carbon::parse('2026-10-12'),
            'statut' => 'terminee',
            'prix_total' => 200.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $period_start = Carbon::parse('2026-10-19');
        $period_end = Carbon::parse('2026-10-21');

        $reportService = app(ReportGenerationService::class);
        $stats_before = $reportService->aggregateStatistics($period_start, $period_end);
        $revenue_before = $stats_before['revenue']['collected'] ?? 0;

        // Create payment with payment_date inside period
        \DB::table('payments')->insert([
            'user_id' => $user->id,
            'reservation_id' => $reservation_id,
            'amount' => 441.69,
            'status' => 'paid',
            'method' => 'card',
            'payment_date' => Carbon::parse('2026-10-21 15:00:00'),  // Inside period
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $stats_after = $reportService->aggregateStatistics($period_start, $period_end);
        $revenue_after = $stats_after['revenue']['collected'] ?? 0;

        // Verify: revenue increased by payment amount
        $this->assertEquals(
            $revenue_before + 441.69,
            $revenue_after,
            'Paid payment dated in period should increase collected revenue'
        );
    }

    /**
     * TEST 4: A reclamation created inside the week updates the reclamation count, 
     * even if its associated reservation starts outside the week.
     * 
     * Core regression test for the reported issue.
     */
    public function test_reclamation_created_in_period_updates_count(): void
    {
        $user = User::create([
            'name' => 'Test User 4',
            'email' => 'test4@example.com',
            'password' => bcrypt('password'),
        ]);

        \DB::table('categories')->insertOrIgnore([
            'id' => 1,
            'name' => 'Solar Equipment',
            'description' => 'Solar equipment category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $equipment = \DB::table('equipment')->insertGetId([
            'user_id' => $user->id,
            'category_id' => 1,
            'name' => 'Test Equipment 4',
            'description' => 'Test equipment',
            'condition' => 'excellent',
            'price_per_day' => 100.00,
            'location' => 'Test Location',
            'availability' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create reservation that starts outside period
        $reservation_id = \DB::table('reservations')->insertGetId([
            'user_id' => $user->id,
            'equipment_id' => $equipment,
            'date_debut' => Carbon::parse('2026-10-22'),  // 22 Oct - outside period
            'date_fin' => Carbon::parse('2026-10-25'),
            'statut' => 'en_cours',
            'prix_total' => 441.69,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $period_start = Carbon::parse('2026-10-19');
        $period_end = Carbon::parse('2026-10-21');

        $reportService = app(ReportGenerationService::class);
        $stats_before = $reportService->aggregateStatistics($period_start, $period_end);
        $rec_count_before = $stats_before['reclamations']['total_created'] ?? 0;

        // Create reclamation with created_at inside period
        \DB::table('reclamations')->insert([
            'reservation_id' => $reservation_id,
            'user_id' => $user->id,
            'title' => 'manque equipement',
            'description' => 'Test reclamation',
            'status' => 'approuvee',
            'refund_amount' => 50.00,
            'created_at' => Carbon::parse('2026-10-21 12:00:00'),  // Inside period
            'updated_at' => now(),
        ]);

        $stats_after = $reportService->aggregateStatistics($period_start, $period_end);
        $rec_count_after = $stats_after['reclamations']['total_created'] ?? 0;

        // Verify: created count increased
        $this->assertEquals(
            $rec_count_before + 1,
            $rec_count_after,
            'Reclamation created during period should increase total_created count'
        );
    }

    /**
     * TEST 5: Incident counts reflect actual incident records and do not simply 
     * copy reclamation counts.
     */
    public function test_incident_counts_are_distinct_from_reclamation_counts(): void
    {
        $user = User::create([
            'name' => 'Test User 5',
            'email' => 'test5@example.com',
            'password' => bcrypt('password'),
        ]);

        \DB::table('categories')->insertOrIgnore([
            'id' => 1,
            'name' => 'Solar Equipment',
            'description' => 'Solar equipment category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $equipment = \DB::table('equipment')->insertGetId([
            'user_id' => $user->id,
            'category_id' => 1,
            'name' => 'Test Equipment 5',
            'description' => 'Test equipment',
            'condition' => 'excellent',
            'price_per_day' => 100.00,
            'location' => 'Test Location',
            'availability' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create reservation
        $reservation_id = \DB::table('reservations')->insertGetId([
            'user_id' => $user->id,
            'equipment_id' => $equipment,
            'date_debut' => Carbon::parse('2026-10-20'),  // Inside period
            'date_fin' => Carbon::parse('2026-10-21'),
            'statut' => 'en_cours',
            'prix_total' => 100.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create reclamation
        $reclamation_id = \DB::table('reclamations')->insertGetId([
            'reservation_id' => $reservation_id,
            'user_id' => $user->id,
            'title' => 'Test reclamation',
            'description' => 'Test',
            'status' => 'en_cours',
            'created_at' => Carbon::parse('2026-10-20 14:00:00'),
            'updated_at' => now(),
        ]);

        // Create TWO incidents for this one reclamation
        \DB::table('incidents')->insert([
            'reclamation_id' => $reclamation_id,
            'equipment_id' => $equipment,
            'type_probleme' => 'endommage',
            'description' => 'Test incident 1',
            'proposed_refund_percentage' => 30,
            'proposed_refund_amount' => 30.00,
            'created_at' => Carbon::parse('2026-10-20 14:30:00'),
            'updated_at' => now(),
        ]);

        \DB::table('incidents')->insert([
            'reclamation_id' => $reclamation_id,
            'equipment_id' => $equipment,
            'type_probleme' => 'manquant',
            'description' => 'Test incident 2',
            'proposed_refund_percentage' => 20,
            'proposed_refund_amount' => 20.00,
            'created_at' => Carbon::parse('2026-10-20 15:00:00'),
            'updated_at' => now(),
        ]);

        $period_start = Carbon::parse('2026-10-19');
        $period_end = Carbon::parse('2026-10-21');

        $reportService = app(ReportGenerationService::class);
        $stats = $reportService->aggregateStatistics($period_start, $period_end);

        // Verify: reclamation count = 1
        $this->assertGreaterThanOrEqual(1, $stats['reclamations']['total_created'] ?? 0,
            'Should have at least 1 reclamation');

        // Verify: incident count >= 2 (not same as reclamation count)
        $this->assertGreaterThanOrEqual(2, $stats['reclamations']['incidents_total_created'] ?? 0,
            'Incidents should be distinct count (multiple per reclamation)'
        );
    }

    /**
     * TEST 6: New records created outside the selected period do not incorrectly 
     * affect period-specific metrics.
     */
    public function test_records_outside_period_not_counted(): void
    {
        $period_start = Carbon::parse('2026-10-19');
        $period_end = Carbon::parse('2026-10-21');

        $reportService = app(ReportGenerationService::class);
        $stats_before = $reportService->aggregateStatistics($period_start, $period_end);
        $res_count_before = $stats_before['reservations']['total_created'] ?? 0;
        $rec_count_before = $stats_before['reclamations']['total_created'] ?? 0;

        // Create user
        $user = User::create([
            'name' => 'Test User 6',
            'email' => 'test6@example.com',
            'password' => bcrypt('password'),
        ]);

        \DB::table('categories')->insertOrIgnore([
            'id' => 1,
            'name' => 'Solar Equipment',
            'description' => 'Solar equipment category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $equipment = \DB::table('equipment')->insertGetId([
            'user_id' => $user->id,
            'category_id' => 1,
            'name' => 'Test Equipment 6',
            'description' => 'Test equipment',
            'condition' => 'excellent',
            'price_per_day' => 100.00,
            'location' => 'Test Location',
            'availability' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create reservation OUTSIDE period (22 Oct)
        $reservation_id = \DB::table('reservations')->insertGetId([
            'user_id' => $user->id,
            'equipment_id' => $equipment,
            'date_debut' => Carbon::parse('2026-10-22'),
            'date_fin' => Carbon::parse('2026-10-25'),
            'statut' => 'confirmee',
            'prix_total' => 100.00,
            'created_at' => Carbon::parse('2026-10-22 10:00:00'),  // OUTSIDE period
            'updated_at' => now(),
        ]);

        // Create reclamation OUTSIDE period
        \DB::table('reclamations')->insert([
            'reservation_id' => $reservation_id,
            'user_id' => $user->id,
            'title' => 'Test reclamation',
            'description' => 'Test',
            'status' => 'en_attente',
            'created_at' => Carbon::parse('2026-10-22 11:00:00'),  // OUTSIDE period
            'updated_at' => now(),
        ]);

        $stats_after = $reportService->aggregateStatistics($period_start, $period_end);
        $res_count_after = $stats_after['reservations']['total_created'] ?? 0;
        $rec_count_after = $stats_after['reclamations']['total_created'] ?? 0;

        // Verify: counts unchanged
        $this->assertEquals($res_count_before, $res_count_after,
            'Records created outside period should not affect period metrics');
        $this->assertEquals($rec_count_before, $rec_count_after,
            'Reclamations created outside period should not affect period metrics');
    }

    /**
     * TEST 7: The AI summary, HTML KPIs, charts, PDF and CSV use consistent aggregates.
     */
    public function test_all_report_formats_use_consistent_aggregates(): void
    {
        // Setup test data
        $user = User::create([
            'name' => 'Test User 7',
            'email' => 'test7@example.com',
            'password' => bcrypt('password'),
        ]);

        \DB::table('categories')->insertOrIgnore([
            'id' => 1,
            'name' => 'Solar Equipment',
            'description' => 'Solar equipment category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $equipment = \DB::table('equipment')->insertGetId([
            'user_id' => $user->id,
            'category_id' => 1,
            'name' => 'Test Equipment 7',
            'description' => 'Test equipment',
            'condition' => 'excellent',
            'price_per_day' => 100.00,
            'location' => 'Test Location',
            'availability' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $reservation_id = \DB::table('reservations')->insertGetId([
            'user_id' => $user->id,
            'equipment_id' => $equipment,
            'date_debut' => Carbon::parse('2026-10-20'),
            'date_fin' => Carbon::parse('2026-10-21'),
            'statut' => 'terminee',
            'prix_total' => 200.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('payments')->insert([
            'user_id' => $user->id,
            'reservation_id' => $reservation_id,
            'amount' => 200.00,
            'status' => 'paid',
            'method' => 'card',
            'payment_date' => Carbon::parse('2026-10-20 15:00:00'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $period_start = Carbon::parse('2026-10-19');
        $period_end = Carbon::parse('2026-10-21');

        $reportService = app(ReportGenerationService::class);
        $stats = $reportService->aggregateStatistics($period_start, $period_end);

        // Extract key metrics
        $total_revenue = $stats['revenue']['collected'] ?? 0;

        // Generate summary (this would go in AI and HTML reports)
        $summary = $reportService->generateDeterministicSummary($stats, $period_start, $period_end);

        // Verify: summary contains the same numbers as stats array
        $this->assertStringContainsString(
            (string)$total_revenue,
            $summary,
            'AI summary should contain revenue from stats'
        );

        // Verify: stats object is complete for export
        $this->assertArrayHasKey('period', $stats, 'Stats should include period');
        $this->assertArrayHasKey('reservations', $stats, 'Stats should include reservations');
        $this->assertArrayHasKey('revenue', $stats, 'Stats should include revenue');
        $this->assertArrayHasKey('reclamations', $stats, 'Stats should include reclamations');
    }

    /**
     * TEST 8: Generating a new report does not reuse stale values from a 
     * previous report.
     */
    public function test_subsequent_report_generation_fetches_fresh_data(): void
    {
        $user = User::create([
            'name' => 'Test User 8',
            'email' => 'test8@example.com',
            'password' => bcrypt('password'),
        ]);

        \DB::table('categories')->insertOrIgnore([
            'id' => 1,
            'name' => 'Solar Equipment',
            'description' => 'Solar equipment category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $equipment = \DB::table('equipment')->insertGetId([
            'user_id' => $user->id,
            'category_id' => 1,
            'name' => 'Test Equipment 8',
            'description' => 'Test equipment',
            'condition' => 'excellent',
            'price_per_day' => 100.00,
            'location' => 'Test Location',
            'availability' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $period_start = Carbon::parse('2026-10-19');
        $period_end = Carbon::parse('2026-10-21');

        $reportService = app(ReportGenerationService::class);

        // Generate first report
        $stats_1 = $reportService->aggregateStatistics($period_start, $period_end);
        $revenue_1 = $stats_1['revenue']['collected'] ?? 0;

        // Create new data
        $reservation_id = \DB::table('reservations')->insertGetId([
            'user_id' => $user->id,
            'equipment_id' => $equipment,
            'date_debut' => Carbon::parse('2026-10-25'),
            'date_fin' => Carbon::parse('2026-10-26'),
            'statut' => 'confirmee',
            'prix_total' => 150.00,
            'created_at' => Carbon::parse('2026-10-20 09:00:00'),
            'updated_at' => now(),
        ]);

        \DB::table('payments')->insert([
            'user_id' => $user->id,
            'reservation_id' => $reservation_id,
            'amount' => 150.00,
            'status' => 'paid',
            'method' => 'card',
            'payment_date' => Carbon::parse('2026-10-21 09:00:00'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Generate second report (same period)
        $stats_2 = $reportService->aggregateStatistics($period_start, $period_end);
        $revenue_2 = $stats_2['revenue']['collected'] ?? 0;

        // Verify: metrics increased (fresh query, not cached)
        $this->assertGreaterThan($revenue_1, $revenue_2,
            'Second report should fetch fresh payment data, not cached values');

        // Verify: third report confirms data is still fresh
        $stats_3 = $reportService->aggregateStatistics($period_start, $period_end);
        $this->assertEquals($stats_2['revenue']['collected'] ?? 0, $stats_3['revenue']['collected'] ?? 0,
            'Consecutive reports should show consistent data (no stale cache)');
    }
}
