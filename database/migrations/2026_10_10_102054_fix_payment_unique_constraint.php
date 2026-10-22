<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("
                CREATE UNIQUE INDEX unique_active_payment_per_reservation
                ON payments (
                    CASE
                        WHEN status IN ('pending', 'paid')
                        THEN reservation_id
                        ELSE NULL
                    END
                )
            ");

            return;
        }

        DB::statement("
            ALTER TABLE payments
            ADD COLUMN active_reservation_id BIGINT UNSIGNED
            GENERATED ALWAYS AS (
                CASE
                    WHEN status IN ('pending', 'paid')
                    THEN reservation_id
                    ELSE NULL
                END
            ) VIRTUAL
        ");

        DB::statement("
            CREATE UNIQUE INDEX unique_active_payment_per_reservation
            ON payments (active_reservation_id)
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("
                DROP INDEX IF EXISTS unique_active_payment_per_reservation
            ");

            return;
        }

        DB::statement("
            DROP INDEX unique_active_payment_per_reservation
            ON payments
        ");

        DB::statement("
            ALTER TABLE payments
            DROP COLUMN active_reservation_id
        ");
    }
};
