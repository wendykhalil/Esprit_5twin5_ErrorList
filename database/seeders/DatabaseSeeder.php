<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() === 0) {
            User::factory()->create([
                'name' => 'Utilisateur SolarShare',
                'email' => 'demo@solarshare.tn',
            ]);
        }

        $this->call([
            CategorySeeder::class,
            EquipmentSeeder::class,
            ReservationSeeder::class,
            InspectionSeeder::class,
            SupportTicketSeeder::class,
        ]);

        // Payment & Transaction module seeders
        $this->seedPaymentsAndTransactions();

        // Technical Services module seeders
        $this->call([
            ServiceProviderSeeder::class,
            ServiceRequestSeeder::class,
        ]);
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