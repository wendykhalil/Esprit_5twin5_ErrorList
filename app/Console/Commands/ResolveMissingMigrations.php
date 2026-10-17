<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResolveMissingMigrations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrations:resolve {--migration= : Specific migration to resolve}';

    /**
     * The console command description.
     */
    protected $description = 'Mark pending migrations as executed without running them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $specific = $this->option('migration');

        if ($specific) {
            $this->resolveMigration($specific);
        } else {
            $this->resolvePendingMigrations();
        }
    }

    /**
     * Resolve pending migrations
     */
    private function resolvePendingMigrations()
    {
        $pending = DB::table('migrations')
            ->whereNull('batch')
            ->orderBy('migration')
            ->pluck('migration');

        if ($pending->isEmpty()) {
            $this->info('No pending migrations found.');
            return;
        }

        $this->info('Pending migrations:');
        foreach ($pending as $migration) {
            $this->line("  - $migration");
        }

        if (!$this->confirm('Mark all pending migrations as executed?')) {
            return;
        }

        $maxBatch = DB::table('migrations')->max('batch') ?? 0;
        $newBatch = $maxBatch + 1;

        foreach ($pending as $migration) {
            DB::table('migrations')
                ->where('migration', $migration)
                ->update(['batch' => $newBatch]);
            $this->info("✓ Marked as executed: $migration");
        }

        $this->info("All pending migrations marked as batch $newBatch");
    }

    /**
     * Resolve a specific migration
     */
    private function resolveMigration(string $migration)
    {
        $exists = DB::table('migrations')
            ->where('migration', $migration)
            ->exists();

        if (!$exists) {
            $this->error("Migration '$migration' not found in migrations table.");
            return;
        }

        $batch = DB::table('migrations')
            ->where('migration', $migration)
            ->value('batch');

        if ($batch !== null) {
            $this->info("Migration '$migration' is already marked as executed (batch $batch).");
            return;
        }

        $maxBatch = DB::table('migrations')->max('batch') ?? 0;
        $newBatch = $maxBatch + 1;

        DB::table('migrations')
            ->where('migration', $migration)
            ->update(['batch' => $newBatch]);

        $this->info("✓ Migration '$migration' marked as executed (batch $newBatch).");
    }
}
