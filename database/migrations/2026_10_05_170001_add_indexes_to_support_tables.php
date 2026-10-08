<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->index('status');
            $table->index('priority');
            $table->index(['user_id', 'status']);
        });

        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->index(['support_ticket_id', 'created_at']);
            $table->index('is_internal');
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->dropIndex(['support_ticket_id', 'created_at']);
            $table->dropIndex(['is_internal']);
        });
    }
};
