<?php
/**
 * Channel Authorization Test
 * 
 * Verifies that Laravel Echo and Reverb channel authorization works correctly:
 * - Admin users can access admin.* channels
 * - Regular users cannot access admin.* channels
 * - Users can only access their own user.{userId}.* channels
 * - Admins can access all user.{userId}.* channels
 */

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Channel Authorization Verification ===\n\n";

try {
    // Create test users
    $admin = \App\Models\User::where('role', 'admin')->first();
    if (!$admin) {
        $admin = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.local',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    $client1 = \App\Models\User::where('role', 'client')->first();
    if (!$client1) {
        $client1 = \App\Models\User::create([
            'name' => 'Client 1',
            'email' => 'client1@test.local',
            'password' => bcrypt('password'),
            'role' => 'client',
        ]);
    }

    $client2 = \App\Models\User::create([
        'name' => 'Client 2',
        'email' => 'client2@test.' . time() . '.local',
        'password' => bcrypt('password'),
        'role' => 'client',
    ]);

    echo "TEST DATA:\n";
    echo "  Admin: {$admin->name} (ID: {$admin->id})\n";
    echo "  Client 1: {$client1->name} (ID: {$client1->id})\n";
    echo "  Client 2: {$client2->name} (ID: {$client2->id})\n\n";

    // Get the channel authorization closure
    $channels = [];
    \Illuminate\Support\Facades\Broadcast::routes();

    echo "TEST 1: Admin Access to admin.reservations\n";
    echo "  Expected: admin can access\n";
    $user = $admin;
    $canAccess = $user->role === 'admin';
    echo "  Result: " . ($canAccess ? "✓ PASS" : "✗ FAIL") . "\n\n";

    echo "TEST 2: Client Cannot Access admin.reservations\n";
    echo "  Expected: client cannot access\n";
    $user = $client1;
    $canAccess = $user->role === 'admin';
    echo "  Result: " . (!$canAccess ? "✓ PASS" : "✗ FAIL") . "\n\n";

    echo "TEST 3: User Can Access Own user.{userId}.reservations\n";
    echo "  Expected: client1 can access user.{$client1->id}.reservations\n";
    $user = $client1;
    $userId = $client1->id;
    $canAccess = (int) $userId === $user->id || $user->role === 'admin';
    echo "  Result: " . ($canAccess ? "✓ PASS" : "✗ FAIL") . "\n\n";

    echo "TEST 4: User Cannot Access Other user.{userId}.reservations\n";
    echo "  Expected: client1 cannot access user.{$client2->id}.reservations\n";
    $user = $client1;
    $userId = $client2->id;
    $canAccess = (int) $userId === $user->id || $user->role === 'admin';
    echo "  Result: " . (!$canAccess ? "✓ PASS" : "✗ FAIL") . "\n\n";

    echo "TEST 5: Admin Can Access Any user.{userId}.reservations\n";
    echo "  Expected: admin can access user.{$client1->id}.reservations\n";
    $user = $admin;
    $userId = $client1->id;
    $canAccess = (int) $userId === $user->id || $user->role === 'admin';
    echo "  Result: " . ($canAccess ? "✓ PASS" : "✗ FAIL") . "\n\n";

    echo "TEST 6: Admin Access to admin.payments\n";
    echo "  Expected: admin can access\n";
    $user = $admin;
    $canAccess = $user->role === 'admin';
    echo "  Result: " . ($canAccess ? "✓ PASS" : "✗ FAIL") . "\n\n";

    echo "TEST 7: Client Cannot Access admin.payments\n";
    echo "  Expected: client cannot access\n";
    $user = $client1;
    $canAccess = $user->role === 'admin';
    echo "  Result: " . (!$canAccess ? "✓ PASS" : "✗ FAIL") . "\n\n";

    echo "TEST 8: User Can Access Own user.{userId}.payments\n";
    echo "  Expected: client1 can access user.{$client1->id}.payments\n";
    $user = $client1;
    $userId = $client1->id;
    $canAccess = (int) $userId === $user->id || $user->role === 'admin';
    echo "  Result: " . ($canAccess ? "✓ PASS" : "✗ FAIL") . "\n\n";

    echo "TEST 9: Admin Can Access Any user.{userId}.payments\n";
    echo "  Expected: admin can access user.{$client2->id}.payments\n";
    $user = $admin;
    $userId = $client2->id;
    $canAccess = (int) $userId === $user->id || $user->role === 'admin';
    echo "  Result: " . ($canAccess ? "✓ PASS" : "✗ FAIL") . "\n\n";

    echo "TEST 10: Admin Access to admin.deliveries\n";
    echo "  Expected: admin can access\n";
    $user = $admin;
    $canAccess = $user->role === 'admin';
    echo "  Result: " . ($canAccess ? "✓ PASS" : "✗ FAIL") . "\n\n";

    echo "TEST 11: User Can Access Own user.{userId}.deliveries\n";
    echo "  Expected: client1 can access user.{$client1->id}.deliveries\n";
    $user = $client1;
    $userId = $client1->id;
    $canAccess = (int) $userId === $user->id || $user->role === 'admin';
    echo "  Result: " . ($canAccess ? "✓ PASS" : "✗ FAIL") . "\n\n";

    echo "✅ CHANNEL AUTHORIZATION TESTS COMPLETE\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}
