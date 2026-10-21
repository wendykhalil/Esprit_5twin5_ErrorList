<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Payment;
use App\Models\Reclamation;
use App\Models\Reservation;
use Carbon\Carbon;
use Tests\TestCase;

class ReportDataVerificationTest extends TestCase
{
    /**
     * Verify that the current database matches the problem statement
     * This is a diagnostic test, not a fix test
     */
    public function test_verify_problem_statement_data(): void
    {
        // Problem statement describes existing records:
        // - Reservation #074: 22-25 Oct, 441.69 TND
        // - Reservation #075: 29-30 Oct, 132.51 TND
        // - Payment #31: 441.69 TND, paid, 21 Oct
        // - Reclamation #17: manque equipement, approved
        // - Compensation code created 21 Oct

        echo "\n=== DATABASE STATE FOR PERIOD 19/10 - 21/10 ===\n";

        // Check Reservation #074
        $res074 = Reservation::find(74);
        echo "Reservation #074: " . ($res074 ? "EXISTS" : "NOT FOUND") . "\n";
        if ($res074) {
            echo "  date_debut: {$res074->date_debut}, date_fin: {$res074->date_fin}, prix_total: {$res074->prix_total}\n";
            echo "  created_at: {$res074->created_at->format('Y-m-d H:i')}\n";
        }

        // Check Reservation #075
        $res075 = Reservation::find(75);
        echo "Reservation #075: " . ($res075 ? "EXISTS" : "NOT FOUND") . "\n";
        if ($res075) {
            echo "  date_debut: {$res075->date_debut}, date_fin: {$res075->date_fin}, prix_total: {$res075->prix_total}\n";
            echo "  created_at: {$res075->created_at->format('Y-m-d H:i')}\n";
        }

        // Check Payment #31
        $pay31 = Payment::find(31);
        echo "Payment #31: " . ($pay31 ? "EXISTS" : "NOT FOUND") . "\n";
        if ($pay31) {
            echo "  amount: {$pay31->amount}, status: {$pay31->status}\n";
            echo "  payment_date: " . ($pay31->payment_date ? $pay31->payment_date->format('Y-m-d H:i') : 'NULL') . "\n";
            echo "  reservation_id: {$pay31->reservation_id}\n";
        }

        // Check Reclamation #17
        $rec17 = Reclamation::find(17);
        echo "Reclamation #17: " . ($rec17 ? "EXISTS" : "NOT FOUND") . "\n";
        if ($rec17) {
            $res = $rec17->reservation;
            echo "  title: {$rec17->title}, status: {$rec17->status}\n";
            echo "  created_at: {$rec17->created_at->format('Y-m-d H:i')}\n";
            echo "  reservation_id: {$rec17->reservation_id}, res_date_debut: {$res->date_debut}\n";
        }

        // Analyze counts for period 19-21 Oct
        $start = Carbon::parse('2026-10-19');
        $end = Carbon::parse('2026-10-21');

        $resCount = Reservation::whereBetween('date_debut', [$start, $end])->count();
        $recCount = Reclamation::whereHas('reservation', function($q) use ($start, $end) {
            $q->whereBetween('date_debut', [$start, $end]);
        })->count();
        $payAmount = Payment::where('status', 'paid')
            ->whereNotNull('payment_date')
            ->whereBetween('payment_date', [$start->startOfDay(), $end->endOfDay()])
            ->sum('amount');

        echo "\n=== COUNTS FOR PERIOD 19/10 - 21/10 (BY date_debut/payment_date) ===\n";
        echo "Reservations (date_debut): $resCount\n";
        echo "Reclamations (linked to date_debut): $recCount\n";
        echo "Paid Revenue (payment_date): $payAmount TND\n";

        // Show all records for that period by created_at instead
        echo "\n=== RECORDS CREATED DURING 19/10 - 21/10 ===\n";
        $resCreatedInPeriod = Reservation::whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->selectRaw('id, date_debut, date_fin, created_at, prix_total')
            ->get();
        echo "Reservations created: " . $resCreatedInPeriod->count() . "\n";
        foreach ($resCreatedInPeriod as $r) {
            echo "  ID {$r->id}: date_debut={$r->date_debut}, created_at={$r->created_at->format('Y-m-d H:i')}\n";
        }

        $recCreatedInPeriod = Reclamation::whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->selectRaw('id, reservation_id, title, status, created_at')
            ->get();
        echo "Reclamations created: " . $recCreatedInPeriod->count() . "\n";
        foreach ($recCreatedInPeriod as $r) {
            $res = $r->reservation;
            echo "  ID {$r->id}: res_id={$r->reservation_id}, res_date_debut={$res->date_debut}, created_at={$r->created_at->format('Y-m-d H:i')}\n";
        }

        $incCreatedInPeriod = Incident::whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->selectRaw('id, reclamation_id, type_probleme, created_at')
            ->get();
        echo "Incidents created: " . $incCreatedInPeriod->count() . "\n";
        foreach ($incCreatedInPeriod as $i) {
            echo "  ID {$i->id}: rec_id={$i->reclamation_id}, type={$i->type_probleme}, created_at={$i->created_at->format('Y-m-d H:i')}\n";
        }

        // Just verify some data exists
        $this->assertTrue(Reservation::count() > 0, 'Database should have reservations');
    }
}
