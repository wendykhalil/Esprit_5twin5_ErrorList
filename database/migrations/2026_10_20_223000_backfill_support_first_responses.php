<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $adminIds = DB::table('users')->where('role', 'admin')->pluck('id');

        if ($adminIds->isEmpty()) {
            return;
        }

        DB::table('ticket_replies')
            ->select('support_ticket_id', 'created_at')
            ->where('is_internal', false)
            ->whereIn('user_id', $adminIds)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->groupBy('support_ticket_id')
            ->each(function ($replies, $ticketId) {
                DB::table('support_tickets')
                    ->where('id', $ticketId)
                    ->whereNull('first_response_at')
                    ->update([
                        'first_response_at' => $replies->first()->created_at,
                    ]);
            });
    }

    public function down(): void
    {
        // Les premières réponses enregistrées ensuite utilisent la même colonne.
    }
};
