<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Set default discount_per_hour to 3.00 for new referral codes.
     * Existing rows were updated by change_referral_discount_to_3_dollars.
     * This ensures any future insert without discount_per_hour gets $3/hr.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE referral_codes MODIFY COLUMN discount_per_hour DECIMAL(8,2) NOT NULL DEFAULT 3.00');
        }
        // SQLite/others: leave default as-is; app always sets discount_per_hour explicitly
    }

    /**
     * Reverse: restore default to 5.00 (legacy).
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE referral_codes MODIFY COLUMN discount_per_hour DECIMAL(8,2) NOT NULL DEFAULT 5.00');
        }
    }
};
