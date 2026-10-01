<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Payment;
use App\Models\Transaction;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // ========== PAYMENT & TRANSACTION SEEDS ==========
        // Seed Payment and Transaction module data
        $this->seedPaymentsAndTransactions();
    }

    /**
     * Seed Payment and Transaction data.
     * Creates 10 payments with 1-3 transactions each.
     */
    private function seedPaymentsAndTransactions(): void
    {
        // Create 10 payments
        $payments = Payment::factory(10)->create();

        // For each payment, create 1-3 transactions
        foreach ($payments as $payment) {
            $transactionCount = rand(1, 3);

            // Create transactions for this payment
            Transaction::factory($transactionCount)
                ->forPayment($payment)
                ->create();
        }
    }
}
