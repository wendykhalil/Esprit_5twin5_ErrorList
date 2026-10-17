<?php
/**
 * Script to fix missing migrations in the migrations table
 * This resolves the issue where 2026_10_16_161123_create_permission_tables fails
 * because tables already exist from a previous installation
 */

require __DIR__ . '/vendor/autoload.php';

// Load Laravel
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');

// Get the database connection
$db = $app['db'];

// Mark the problematic migration as executed
$migration = '2026_10_16_161123_create_permission_tables';
$batch = 9; // Next batch number

echo "Checking migration status...\n";

// Check if migration already exists in migrations table
$exists = $db->table('migrations')
    ->where('migration', $migration)
    ->exists();

if ($exists) {
    echo "✓ Migration $migration is already marked as executed.\n";
} else {
    echo "Adding $migration to migrations table as batch $batch...\n";
    $db->table('migrations')->insert([
        'migration' => $migration,
        'batch' => $batch,
    ]);
    echo "✓ Migration marked as executed.\n";
}

// List all existing tables
echo "\nExisting permission-related tables:\n";
$tables = $db->select("
    SELECT TABLE_NAME FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME IN ('permissions', 'roles', 'model_has_permissions', 'model_has_roles', 'role_has_permissions')
");

foreach ($tables as $table) {
    echo "  ✓ {$table->TABLE_NAME}\n";
}

echo "\n✓ Fix applied. You can now run: php artisan migrate\n";
