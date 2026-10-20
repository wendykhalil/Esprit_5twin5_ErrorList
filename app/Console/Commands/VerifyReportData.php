<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\Reclamation;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyReportData extends Command
{
    protected $signature = 'verify:report-data';
    protected $description = 'Verify report data filtering and business logic';

    public function handle()
    {
        $this->info('=== VÉRIFICATION: Rapport AI Data Filtering ===');

        $this->line('\n1. DATES AND DATA SOURCES');
        $this->line('------------------------');

        // Check payment_date column
        try {
            $columns = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'payments' AND TABLE_SCHEMA = DATABASE()");
            $hasPaymentDate = false;
            foreach ($columns as $col) {
                if ($col->COLUMN_NAME === 'payment_date') {
                    $hasPaymentDate = true;
                    break;
                }
            }
        } catch (\Exception $e) {
            $hasPaymentDate = false;
        }
        $this->line('payment_date column exists: ' . ($hasPaymentDate ? '✓' : '✗'));

        // Check sample payments
        $this->line('\n2. SAMPLE PAYMENTS');
        $this->line('------------------');

        $payments = Payment::with('reservation')->where('status', 'paid')->limit(3)->get();
        
        if ($payments->isEmpty()) {
            $this->warn('No paid payments found in database');
        } else {
            foreach ($payments as $payment) {
                $this->line("Payment #{$payment->id}: Status={$payment->status}, Amount={$payment->amount}");
                $this->line("  created_at: {$payment->created_at}");
                $this->line("  payment_date: " . ($payment->payment_date ?? '(NULL)'));
                if ($payment->reservation) {
                    $this->line("  Res ID: {$payment->reservation_id}, date_debut: {$payment->reservation->date_debut}");
                }
            }
        }

        // Check reclamations structure
        $this->line('\n3. RECLAMATIONS AND INCIDENTS STRUCTURE');
        $this->line('----------------------------------------');

        $reclamation = Reclamation::with('incidents')->first();
        if ($reclamation) {
            $this->line("Reclamation #{$reclamation->id}:");
            $this->line("  status: {$reclamation->status}");
            $this->line("  created_at: {$reclamation->created_at}");
            $this->line("  refund_approved_at: " . ($reclamation->refund_approved_at ?? '(NULL)'));
            $this->line("  refund_amount: {$reclamation->refund_amount}");
            $this->line("  incidents: {$reclamation->incidents->count()}");
            
            foreach ($reclamation->incidents as $incident) {
                $this->line("    - Incident #{$incident->id}: {$incident->type_probleme}");
            }
        } else {
            $this->warn('No reclamations found');
        }

        // Cost data check
        $this->line('\n4. COST DATA AVAILABILITY');
        $this->line('-------------------------');

        $equipment = \App\Models\Equipment::first();
        if ($equipment) {
            $costFields = ['price_per_day', 'cost_per_day', 'acquisition_price', 'maintenance_cost'];
            foreach ($costFields as $field) {
                $value = $equipment->getAttribute($field);
                if ($field === 'price_per_day' && $value) {
                    $this->line("✓ {$field}: {$value}");
                } else {
                    $this->line("✗ {$field}: (not available)");
                }
            }
        }

        // Test period filtering
        $this->line('\n5. PERIOD FILTERING TEST');
        $this->line('------------------------');

        $startDate = Carbon::parse('2026-10-01');
        $endDate = Carbon::parse('2026-10-31');
        $this->line("Test period: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        // Current method (by reservation date_debut)
        $countByResBetween = Payment::whereHas('reservation', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('date_debut', [$startDate, $endDate]);
        })->count();
        $this->line("Payments (filtered by reservation date_debut): {$countByResBetween}");

        // Alternative (if payment_date existed and was used)
        if ($hasPaymentDate) {
            $countByPaymentDate = Payment::whereNotNull('payment_date')
                ->whereBetween('payment_date', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->where('status', 'paid')
                ->count();
            $this->line("Payments (if filtered by payment_date): {$countByPaymentDate}");
            
            if ($countByPaymentDate !== $countByResBetween) {
                $this->error('⚠️  DISCREPANCY: Methods give different results!');
            }
        }

        $this->line('\n=== END OF VERIFICATION ===');
    }
}
