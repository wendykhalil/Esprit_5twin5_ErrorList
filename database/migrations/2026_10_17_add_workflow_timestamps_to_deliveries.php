<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds timestamp fields for the 5-step delivery workflow:
     * - prepared_at: When admin marks delivery "À préparer"
     * - ready_at: When admin marks delivery "Prête"
     * - client_received_at: When client confirms receipt (separate from actual_delivery_date for clarity)
     */
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // Add new workflow timestamps after status column
            $table->dateTime('prepared_at')->nullable()->after('status');
            $table->dateTime('ready_at')->nullable()->after('prepared_at');
            $table->dateTime('client_received_at')->nullable()->after('ready_at');
            
            // Add index on prepared_at and ready_at for queries
            $table->index('prepared_at');
            $table->index('ready_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropIndex(['prepared_at']);
            $table->dropIndex(['ready_at']);
            $table->dropColumn(['prepared_at', 'ready_at', 'client_received_at']);
        });
    }
};
