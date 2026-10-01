<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Your Equipment module seeders
        $this->call([
            CategorySeeder::class,
            EquipmentSeeder::class,
        ]);

        // Payment & Transaction module seeders
        $this->seedPaymentsAndTransactions();
    }

    /**
     * Seed Payment and Transaction data.
     * Creates 10 payments with 1-3 transactions each.
     */
    private function seedPaymentsAndTransactions(): void
    {
        $payments = Payment::factory(10)->create();

        foreach ($payments as $payment) {

            $transactionCount = rand(1, 3);

            Transaction::factory($transactionCount)
                ->forPayment($payment)
                ->create();
        }
    }
}