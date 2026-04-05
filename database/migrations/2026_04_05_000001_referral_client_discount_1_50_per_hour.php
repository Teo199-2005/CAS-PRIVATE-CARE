<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marketing referral client discount is $1.50/hr (was $3.00).
     * Existing referral_codes rows are updated; new rows use DB default.
     */
    public function up(): void
    {
        if (! Schema::hasTable('referral_codes')) {
            return;
        }

        DB::table('referral_codes')->update(['discount_per_hour' => 1.50]);

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE referral_codes MODIFY COLUMN discount_per_hour DECIMAL(8,2) NOT NULL DEFAULT 1.50');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('referral_codes')) {
            return;
        }

        DB::table('referral_codes')->where('discount_per_hour', 1.50)->update(['discount_per_hour' => 3.00]);

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE referral_codes MODIFY COLUMN discount_per_hour DECIMAL(8,2) NOT NULL DEFAULT 3.00');
        }
    }
};
