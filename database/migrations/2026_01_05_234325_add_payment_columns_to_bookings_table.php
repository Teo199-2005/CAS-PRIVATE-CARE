<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Add payment status column if it doesn't exist
            if (!Schema::hasColumn('bookings', 'payment_status')) {
                $table->string('payment_status')->nullable()->default('unpaid')->after('status');
            }
            
            // Add Stripe payment intent ID if it doesn't exist
            if (!Schema::hasColumn('bookings', 'stripe_payment_intent_id')) {
                $table->string('stripe_payment_intent_id')->nullable()->after('payment_status');
            }
            
            // Add payment_intent_id (alias) if it doesn't exist
            if (!Schema::hasColumn('bookings', 'payment_intent_id')) {
                $table->string('payment_intent_id')->nullable()->after('stripe_payment_intent_id');
            }
            
            // Add payment date if it doesn't exist
            if (!Schema::hasColumn('bookings', 'payment_date')) {
                $table->timestamp('payment_date')->nullable()->after('payment_intent_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('bookings', 'stripe_payment_intent_id')) {
                $table->dropColumn('stripe_payment_intent_id');
            }
            if (Schema::hasColumn('bookings', 'payment_intent_id')) {
                $table->dropColumn('payment_intent_id');
            }
            if (Schema::hasColumn('bookings', 'payment_date')) {
                $table->dropColumn('payment_date');
            }
        });
    }
};
