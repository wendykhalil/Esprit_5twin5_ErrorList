<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Data\AdminMockData;
use App\Models\Payment;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        $rentals = AdminMockData::getRentals();
        $equipments = AdminMockData::getEquipments();

        // Payment Statistics
        $totalPayments = Payment::count();
        $paidPayments = Payment::where('status', 'paid')->count();
        $pendingPayments = Payment::where('status', 'pending')->count();
        $totalPaidAmount = Payment::where('status', 'paid')->sum('amount');

        // Transaction Statistics
        $totalTransactions = Transaction::count();
        $completedTransactions = Transaction::where('status', 'completed')->count();

        return view('backend.dashboard', [
            'rentals' => array_slice($rentals, 0, 5),
            'equipments' => array_slice($equipments, 0, 5),
            'totalPayments' => $totalPayments,
            'paidPayments' => $paidPayments,
            'pendingPayments' => $pendingPayments,
            'totalPaidAmount' => $totalPaidAmount,
            'totalTransactions' => $totalTransactions,
            'completedTransactions' => $completedTransactions,
        ]);
    }

    /**
     * Get real-time statistics for Dashboard (JSON)
     * Used by AJAX polling
     */
    public function stats()
    {
        // Payment Statistics
        $totalPayments = Payment::count();
        $paidPayments = Payment::where('status', 'paid')->count();
        $revenue = Payment::where('status', 'paid')->sum('amount');

        // Transaction Statistics
        $totalTransactions = Transaction::count();
        $completedTransactions = Transaction::where('status', 'completed')->count();

        return response()->json([
            'revenue' => (float) $revenue,
            'payments' => $totalPayments,
            'paidPayments' => $paidPayments,
            'transactions' => $totalTransactions,
            'completedTransactions' => $completedTransactions,
        ]);
    }
}
