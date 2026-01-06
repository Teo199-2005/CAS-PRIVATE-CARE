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
        // Add Stripe fields to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable()->after('email')
                    ->comment('Stripe Customer ID for clients');
            }
            if (!Schema::hasColumn('users', 'stripe_account_id')) {
                $table->string('stripe_account_id')->nullable()->after('stripe_customer_id')
                    ->comment('Stripe Connect Account ID for caregivers/partners');
            }
            if (!Schema::hasColumn('users', 'stripe_onboarding_complete')) {
                $table->boolean('stripe_onboarding_complete')->default(false)->after('stripe_account_id')
                    ->comment('Whether Stripe Connect onboarding is complete');
            }
        });

        // Add Stripe payment tracking to time_trackings table
        Schema::table('time_trackings', function (Blueprint $table) {
            if (!Schema::hasColumn('time_trackings', 'stripe_charge_id')) {
                $table->string('stripe_charge_id')->nullable()->after('payment_status')
                    ->comment('Stripe Charge ID for client payment');
            }
            if (!Schema::hasColumn('time_trackings', 'stripe_transfer_id')) {
                $table->string('stripe_transfer_id')->nullable()->after('stripe_charge_id')
                    ->comment('Stripe Transfer ID for caregiver payout');
            }
            if (!Schema::hasColumn('time_trackings', 'actual_minutes_worked')) {
                $table->integer('actual_minutes_worked')->nullable()->after('hours_worked')
                    ->comment('Exact minutes worked (for late-check prevention)');
            }
            if (!Schema::hasColumn('time_trackings', 'scheduled_minutes')) {
                $table->integer('scheduled_minutes')->nullable()->after('actual_minutes_worked')
                    ->comment('Scheduled shift length in minutes');
            }
            if (!Schema::hasColumn('time_trackings', 'is_late')) {
                $table->boolean('is_late')->default(false)->after('scheduled_minutes')
                    ->comment('Whether caregiver clocked in/out late');
            }
            if (!Schema::hasColumn('time_trackings', 'minutes_difference')) {
                $table->integer('minutes_difference')->nullable()->after('is_late')
                    ->comment('Difference between actual and scheduled minutes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_customer_id',
                'stripe_account_id',
                'stripe_onboarding_complete'
            ]);
        });

        Schema::table('time_trackings', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_charge_id',
                'stripe_transfer_id',
                'actual_minutes_worked',
                'scheduled_minutes',
                'is_late',
                'minutes_difference'
            ]);
        });
    }
};
