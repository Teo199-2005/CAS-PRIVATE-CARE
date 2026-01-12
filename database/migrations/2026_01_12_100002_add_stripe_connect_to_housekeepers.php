<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds Stripe Connect columns to housekeepers table (same as caregivers)
     */
    public function up(): void
    {
        Schema::table('housekeepers', function (Blueprint $table) {
            if (!Schema::hasColumn('housekeepers', 'stripe_connect_id')) {
                $table->string('stripe_connect_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('housekeepers', 'stripe_charges_enabled')) {
                $table->boolean('stripe_charges_enabled')->default(false)->after('stripe_connect_id');
            }
            if (!Schema::hasColumn('housekeepers', 'stripe_payouts_enabled')) {
                $table->boolean('stripe_payouts_enabled')->default(false)->after('stripe_charges_enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housekeepers', function (Blueprint $table) {
            $table->dropColumn(['stripe_connect_id', 'stripe_charges_enabled', 'stripe_payouts_enabled']);
        });
    }
};
