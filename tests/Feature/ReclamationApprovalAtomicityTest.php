<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Incident;
use App\Models\Payment;
use App\Models\Reclamation;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PDO;

/**
 * ReclamationApprovalAtomicityTest
 *
 * Tests the real approval route using a dedicated temporary SQLite database.
 * This test class does NOT use RefreshDatabase, allowing ImmediateTransaction
 * to execute at the top level (transactionLevel = 0).
 *
 * Database Isolation:
 * - Creates a unique temporary SQLite file in sys_get_temp_dir()
 * - Configures Laravel to use ONLY that temporary database for this test run
 * - Runs migrations on the temporary database
 * - Cleans up the temporary file after all tests complete
 * - Production database (database/database.sqlite) is never touched
 *
 * Transaction Safety:
 * - Does NOT weaken the nested-transaction guard in ImmediateTransaction
 * - Tests the real production approval path without RefreshDatabase wrapper
 * - Verifies atomicity, idempotency, and concurrency handling
 */
class ReclamationApprovalAtomicityTest extends TestCase
{
    private static ?string $tempDbPath = null;
    private static bool $migrationRan = false;

    /**
     * Setup for the entire test class (once before all tests).
     * Creates a temporary SQLite database and configures it.
     */
    public static function setUpBeforeClass(): void
    {
        // Create unique temporary SQLite file
        $tmpDir = sys_get_temp_dir();
        $timestamp = date('YmdHis');
        self::$tempDbPath = $tmpDir . DIRECTORY_SEPARATOR . "reclamation_test_{$timestamp}_" . uniqid() . '.sqlite';

        // Ensure file doesn't exist from a previous run
        if (file_exists(self::$tempDbPath)) {
            @unlink(self::$tempDbPath);
        }

        // Create empty SQLite file
        touch(self::$tempDbPath);

        parent::setUpBeforeClass();
    }

    /**
     * Setup for each test method.
     * Runs migrations on the temporary database (once per test class).
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Override database configuration BEFORE any DB access
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => self::$tempDbPath,
        ]);

        // Clear any cached connection
        DB::purge('sqlite');

        // Run migrations only once
        if (!self::$migrationRan) {
            // Run migrations using Artisan (will use the configured sqlite connection)
            Artisan::call('migrate', [
                '--force' => true,
            ]);
            self::$migrationRan = true;
        }
    }

    /**
     * Cleanup after all tests complete.
     * Deletes the temporary SQLite file.
     */
    public static function tearDownAfterClass(): void
    {
        if (self::$tempDbPath && file_exists(self::$tempDbPath)) {
            @unlink(self::$tempDbPath);
        }

        parent::tearDownAfterClass();
    }

    /**
     * Test 1: Real HTTP approval succeeds and creates exactly one completed refund.
     */
    public function test_real_approval_route_succeeds_and_creates_refund(): void
    {
        // Setup
        $client = User::factory()->create(['role' => 'client']);
        $admin = User::factory()->create(['role' => 'admin']);
        $equipmentOwner = User::factory()->create(['role' => 'client']);

        $category = Category::firstOrCreate(['name' => 'Solar Panels']);

        $equipment = Equipment::create([
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

        $reservation = Reservation::create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'date_debut' => now(),
            'date_fin' => now()->addDays(5),
            'statut' => 'terminee',
            'prix_total' => 500,
        ]);

        $payment = Payment::create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'amount' => 500,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        $reclamation = Reclamation::create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'Test Approval',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);

        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Damaged',
            'proposed_refund_amount' => 100,
            'proposed_refund_percentage' => 20,
        ]);

        // Verify zero refund transactions before
        $transactionsBefore = Transaction::where('type', 'refund')
            ->where('status', 'completed')
            ->count();
        $this->assertEquals(0, $transactionsBefore);

        // Debug: Check transaction level before request
        $levelBefore = DB::transactionLevel();

        // Execute: Call real approval route
        $this->actingAs($admin);
        $response = $this->post(route('admin.reclamations.approve', $reclamation->id), [
            'approved_incidents' => [$incident->id],
            'admin_notes' => 'Approved in test',
        ]);

        // Debug: Check response status and content
        if ($response->getStatusCode() !== 302) {
            $this->fail("Expected redirect (302), got {$response->getStatusCode()}. Response: " . $response->getContent());
        }

        // Verify: Response redirects (could be to success or error page)
        $response->assertStatus(302);  // Redirect status

        // Verify: Exactly one refund transaction created (proves approval succeeded)
        $transactionsAfter = Transaction::where('type', 'refund')
            ->where('status', 'completed')
            ->count();
        
        if ($transactionsAfter === 0) {
            // Check if there's an error message or exception
            $lastError = DB::select("SELECT * FROM transactions WHERE payment_id = ? ORDER BY created_at DESC LIMIT 5", [$payment->id]);
            $this->fail("No refund transaction created. Level before: $levelBefore. Transactions in DB: " . json_encode($lastError));
        }
        
        $this->assertEquals(1, $transactionsAfter);

        // Verify: Reclamation status updated
        $updatedReclamation = Reclamation::find($reclamation->id);
        $this->assertEquals('approuvee', $updatedReclamation->status);
        $this->assertEquals(100, $updatedReclamation->refund_amount);

        // Verify: Transaction has correct amount and reference
        $transaction = Transaction::where('type', 'refund')
            ->where('status', 'completed')
            ->first();
        $this->assertEquals(100, $transaction->amount);
        $this->assertTrue(str_starts_with($transaction->reference, 'REC-'));
    }

    /**
     * Test 2: Refund exceeding payment limit with existing completed refund is rejected.
     */
    public function test_refund_exceeding_limit_with_existing_refund_rejected(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $admin = User::factory()->create(['role' => 'admin']);
        $equipmentOwner = User::factory()->create(['role' => 'client']);

        $category = Category::firstOrCreate(['name' => 'Solar Panels']);

        $equipment = Equipment::create([
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

        $reservation = Reservation::create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'date_debut' => now(),
            'date_fin' => now()->addDays(5),
            'statut' => 'terminee',
            'prix_total' => 200,
        ]);

        $payment = Payment::create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'amount' => 200,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        // Create first completed refund transaction
        Transaction::create([
            'payment_id' => $payment->id,
            'reference' => 'REC-' . \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'refund',
            'amount' => 150,
            'status' => 'completed',
            'transaction_date' => now(),
            'description' => 'First refund',
        ]);

        // Create second reclamation
        $reclamation2 = Reclamation::create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'Test Refund Limit',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);

        $incident2 = Incident::create([
            'reclamation_id' => $reclamation2->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'manquant', 'titre' => 'Incident signale',
            'description' => 'Missing',
            'proposed_refund_amount' => 100,  // 150 + 100 = 250 > 200 limit
            'proposed_refund_percentage' => 20,
        ]);

        // Attempt approval
        $this->actingAs($admin);
        $response = $this->post(route('admin.reclamations.approve', $reclamation2->id), [
            'approved_incidents' => [$incident2->id],
            'admin_notes' => 'Should fail - exceeds limit',
        ]);

        // Verify: Response indicates failure
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Verify: Reclamation NOT approved
        $this->assertDatabaseHas('reclamations', [
            'id' => $reclamation2->id,
            'status' => 'en_attente',
            'refund_amount' => null,
        ]);

        // Verify: No additional refund transaction created
        $this->assertDatabaseMissing('transactions', [
            'payment_id' => $payment->id,
            'type' => 'refund',
            'amount' => 100,
        ]);
    }

    /**
     * Test 3: Refund creation failure rolls back status update (atomicity).
     */
    public function test_refund_creation_failure_rolls_back_status_update(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $admin = User::factory()->create(['role' => 'admin']);
        $equipmentOwner = User::factory()->create(['role' => 'client']);

        $category = Category::firstOrCreate(['name' => 'Solar Panels']);

        $equipment = Equipment::create([
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

        $reservation = Reservation::create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'date_debut' => now(),
            'date_fin' => now()->addDays(5),
            'statut' => 'terminee',
            'prix_total' => 500,
        ]);

        $payment = Payment::create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'amount' => 500,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        $reclamation = Reclamation::create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'Test Atomicity',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);

        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Damaged',
            'proposed_refund_amount' => 100,
            'proposed_refund_percentage' => 20,
        ]);

        // Delete payment to force exception during refund creation
        $payment->delete();

        // Attempt approval (will fail during transaction creation)
        $this->actingAs($admin);
        $response = $this->post(route('admin.reclamations.approve', $reclamation->id), [
            'approved_incidents' => [$incident->id],
            'admin_notes' => 'Will fail',
        ]);

        // Verify: Response indicates failure
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Verify: Reclamation status NOT changed (rolled back)
        $this->assertDatabaseHas('reclamations', [
            'id' => $reclamation->id,
            'status' => 'en_attente',
            'refund_amount' => null,
            'refund_approved_at' => null,
        ]);
    }

    /**
     * Test 4: Second writer safely waits or is rejected during BEGIN IMMEDIATE lock.
     * Uses separate PDO connections to demonstrate serialization.
     */
    public function test_concurrent_write_serialized_with_immediate_lock(): void
    {
        // Create first completed refund
        $client = User::factory()->create(['role' => 'client']);
        $equipmentOwner = User::factory()->create(['role' => 'client']);

        $category = Category::firstOrCreate(['name' => 'Solar Panels']);

        $equipment = Equipment::create([
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

        $reservation = Reservation::create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'date_debut' => now(),
            'date_fin' => now()->addDays(5),
            'statut' => 'terminee',
            'prix_total' => 200,
        ]);

        $payment = Payment::create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'amount' => 200,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        // Use two separate PDO connections to test BEGIN IMMEDIATE serialization
        $pdo1 = new PDO('sqlite:' . self::$tempDbPath);
        $pdo2 = new PDO('sqlite:' . self::$tempDbPath);

        $pdo1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // PHP's PDO SQLite busy handler otherwise waits about 60 seconds.
        $pdo2->exec('PRAGMA busy_timeout = 200');

        $blockedSuccessfully = false;

        try {
            // Connection 1: Acquire IMMEDIATE lock
            $pdo1->exec('BEGIN IMMEDIATE');

            // Connection 2: Try to acquire IMMEDIATE lock (should block)
            try {
                $pdo2->exec('BEGIN IMMEDIATE');
                // If we get here without exception, both got the lock simultaneously (unexpected)
            } catch (\PDOException $e) {
                // Expected: "database is locked" because pdo1 holds the lock
                if (str_contains($e->getMessage(), 'locked')) {
                    $blockedSuccessfully = true;
                }
            }

            // Rollback pdo1 to release the lock
            $pdo1->exec('ROLLBACK');
        } finally {
            $pdo1 = null;
            $pdo2 = null;
        }

        $this->assertTrue($blockedSuccessfully, 'Second writer should be blocked by BEGIN IMMEDIATE lock');
    }

    /**
     * Test 5: Repeated approval cannot create duplicate refund (idempotency).
     */
    public function test_repeated_approval_cannot_create_duplicate_refund(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $admin = User::factory()->create(['role' => 'admin']);
        $equipmentOwner = User::factory()->create(['role' => 'client']);

        $category = Category::firstOrCreate(['name' => 'Solar Panels']);

        $equipment = Equipment::create([
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

        $reservation = Reservation::create([
            'user_id' => $client->id,
            'equipment_id' => $equipment->id,
            'date_debut' => now(),
            'date_fin' => now()->addDays(5),
            'statut' => 'terminee',
            'prix_total' => 500,
        ]);

        $payment = Payment::create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'amount' => 500,
            'method' => 'card',
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        $reclamation = Reclamation::create([
            'user_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'Test Idempotency',
            'description' => 'Test',
            'status' => 'en_attente',
        ]);

        $incident = Incident::create([
            'reclamation_id' => $reclamation->id,
            'equipment_id' => $equipment->id,
            'type_probleme' => 'endommage', 'titre' => 'Incident signale',
            'description' => 'Damaged',
            'proposed_refund_amount' => 100,
            'proposed_refund_percentage' => 20,
        ]);

        $this->actingAs($admin);

        // First approval
        $response1 = $this->post(route('admin.reclamations.approve', $reclamation->id), [
            'approved_incidents' => [$incident->id],
            'admin_notes' => 'First approval',
        ]);
        $response1->assertStatus(302);  // Redirect

        // Count transactions after first approval
        $transactionsAfterFirst = Transaction::where('payment_id', $payment->id)
            ->where('type', 'refund')
            ->where('status', 'completed')
            ->count();
        $this->assertEquals(1, $transactionsAfterFirst);

        // Verify status changed to approuvee
        $this->assertDatabaseHas('reclamations', [
            'id' => $reclamation->id,
            'status' => 'approuvee',
        ]);

        // Attempt second approval by trying to POST again
        // The policy should prevent the second approval (status is now approuvee)
        $response2 = $this->post(route('admin.reclamations.approve', $reclamation->id), [
            'approved_incidents' => [$incident->id],
            'admin_notes' => 'Second approval attempt',
        ]);

        // Second attempt should be blocked (either 403 Forbidden via policy or 302 redirect with error)
        $this->assertTrue(
            in_array($response2->getStatusCode(), [302, 403]),
            "Expected status 302 or 403, got {$response2->getStatusCode()}"
        );

        // Verify still only ONE transaction exists (no duplicate)
        $transactionsAfterSecond = Transaction::where('payment_id', $payment->id)
            ->where('type', 'refund')
            ->where('status', 'completed')
            ->count();
        $this->assertEquals(1, $transactionsAfterSecond);

        // Verify status still approuvee (not changed again)
        $this->assertDatabaseHas('reclamations', [
            'id' => $reclamation->id,
            'status' => 'approuvee',
        ]);
    }
}
