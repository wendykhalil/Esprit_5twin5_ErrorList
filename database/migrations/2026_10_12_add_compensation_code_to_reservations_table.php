<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Purpose: Add compensation code support to reservations table.
     * This allows clients to apply compensation codes (from approved reclamations)
     * to receive discounts on future reservations.
     *
     * New columns:
     * - compensation_code_id: FK to compensation_codes table (nullable)
     * - base_price: Original price before discount (decimal)
     * - discount_amount: Discount applied in TND (decimal, >= 0)
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Foreign key to compensation_codes table
            // Nullable because not all reservations use a compensation code
            $table->foreignId('compensation_code_id')
                ->nullable()
                ->constrained('compensation_codes')
                ->onDelete('set null')
                ->after('prix_total');

            // Store the original price before discount for audit trail
            // Allows recalculation without modifying historical data
            $table->decimal('base_price', 8, 2)
                ->nullable()
                ->after('compensation_code_id')
                ->comment('Original price before compensation code discount');

            // Store the discount amount applied (in TND)
            // Immutable record of what discount was actually given
            // Never changes, even if CompensationCode rules later change
            $table->decimal('discount_amount', 8, 2)
                ->default(0)
                ->after('base_price')
                ->comment('Discount amount applied (in TND), immutable');

            // Add index for efficient queries on discounted reservations
            $table->index('compensation_code_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['compensation_code_id']);
            $table->dropForeignIdFor('compensation_codes', 'compensation_code_id');
            $table->dropColumn(['compensation_code_id', 'base_price', 'discount_amount']);
        });
    }
};
