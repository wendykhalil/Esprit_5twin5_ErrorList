<?php

namespace App\Console\Commands;

use App\Models\CompensationCode;
use App\Models\User;
use Illuminate\Console\Command;

class CreateTestCompensationCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-compensation-codes {--user-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test compensation codes for development and testing. Default creates codes for first user, or specify --user-id';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');

        // Get user
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }
        } else {
            $user = User::first();
            if (!$user) {
                $this->error('No users found in database. Create a user first.');
                return 1;
            }
        }

        $this->info("Creating test compensation codes for user: {$user->name} (ID: {$user->id})");

        // Test codes to create
        $testCodes = [
            [
                'name' => '50% Percentage Discount',
                'type' => 'percentage',
                'value' => 50,
                'max_uses' => 1,
                'description' => 'Test code with 50% discount (affects only rental price)',
            ],
            [
                'name' => '25% Percentage Discount',
                'type' => 'percentage',
                'value' => 25,
                'max_uses' => 3,
                'description' => 'Test code with 25% discount, can be used 3 times',
            ],
            [
                'name' => '50 TND Fixed Discount',
                'type' => 'fixed',
                'value' => 50,
                'max_uses' => 1,
                'description' => 'Test code with fixed 50 TND discount',
            ],
            [
                'name' => '100 TND Fixed Discount',
                'type' => 'fixed',
                'value' => 100,
                'max_uses' => 2,
                'description' => 'Test code with fixed 100 TND discount, can be used 2 times',
            ],
            [
                'name' => 'Expired Code',
                'type' => 'percentage',
                'value' => 50,
                'max_uses' => 1,
                'description' => 'Test code that is already expired (should be rejected)',
                'expired' => true,
            ],
            [
                'name' => 'Not Yet Valid Code',
                'type' => 'percentage',
                'value' => 30,
                'max_uses' => 1,
                'description' => 'Test code that is not yet valid (should be rejected)',
                'future' => true,
            ],
        ];

        $createdCodes = [];

        foreach ($testCodes as $testCode) {
            $code = CompensationCode::create([
                'code' => CompensationCode::generateCode(),
                'type' => $testCode['type'],
                'value' => $testCode['value'],
                'valid_from' => $testCode['future'] ?? false ? now()->addDays(5) : now()->subDay(),
                'valid_until' => $testCode['expired'] ?? false ? now()->subDay() : now()->addDays(30),
                'max_uses' => $testCode['max_uses'],
                'uses_count' => 0,
                'is_active' => true,
                'user_id' => $user->id,
                'reclamation_id' => null,
                'created_by' => null,
                'description' => $testCode['description'],
            ]);

            $createdCodes[] = $code;
            $status = '';
            if ($testCode['expired'] ?? false) {
                $status = ' (EXPIRED)';
            } elseif ($testCode['future'] ?? false) {
                $status = ' (NOT YET VALID)';
            }

            $this->line("✓ Created{$status}: {$testCode['name']}");
            $this->line("  Code: <fg=cyan>{$code->code}</>");
            $this->line("  Type: {$code->type}, Value: {$code->value}");
            $this->line("  Valid from: {$code->valid_from->format('d/m/Y H:i')} to {$code->valid_until->format('d/m/Y H:i')}");
            $this->line("  Max uses: {$code->max_uses}");
            $this->newLine();
        }

        $this->info('Summary:');
        $this->info("Created " . count($createdCodes) . " test compensation codes.");
        $this->newLine();

        $this->info('Usage in tests:');
        $this->line('1. Use code: <fg=cyan>' . $createdCodes[0]->code . '</> for 50% discount');
        $this->line('2. Use code: <fg=cyan>' . $createdCodes[2]->code . '</> for fixed 50 TND discount');
        $this->line('3. Try code: <fg=cyan>' . $createdCodes[4]->code . '</> - should be EXPIRED');
        $this->line('4. Try code: <fg=cyan>' . $createdCodes[5]->code . '</> - should NOT YET BE VALID');
        $this->newLine();

        $this->info('Test scenario: Create a reservation for 100 TND with 50% code → expect 50 TND discount, total 50 TND');

        return 0;
    }
}
