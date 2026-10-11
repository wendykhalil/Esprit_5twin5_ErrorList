
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE payments
            ADD COLUMN active_reservation_id BIGINT UNSIGNED
            GENERATED ALWAYS AS (
                CASE
                    WHEN status IN ('pending', 'paid')
                    THEN reservation_id
                    ELSE NULL
                END
            ) PERSISTENT
        ");

        DB::statement("
            CREATE UNIQUE INDEX unique_active_payment_per_reservation
            ON payments (active_reservation_id)
        ");
    }

    public function down(): void
    {
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
