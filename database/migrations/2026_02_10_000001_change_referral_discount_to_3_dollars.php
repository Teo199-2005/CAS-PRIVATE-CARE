<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change referral discount from $5/hr to $3/hr.
     * New referral codes get 3.00 from app code; this updates existing rows.
     */
    public function up(): void
    {
        DB::table('referral_codes')->update(['discount_per_hour' => 3.00]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('referral_codes')->update(['discount_per_hour' => 5.00]);
    }
};
