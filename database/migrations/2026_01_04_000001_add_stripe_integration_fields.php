<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add Stripe integration fields to existing tables
     * 
     * This migration adds:
     * - Stripe Customer IDs for clients
     * - Stripe Connect Account IDs for caregivers, marketing, training
     * - Stripe transaction IDs for time_trackings
     * - Payment tracking flags
     */
    public function up(): void
    {
        // Add Stripe fields to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable()->after('email')
                    ->comment('Stripe Customer ID for clients');
            }
            
            if (!Schema::hasColumn('users', 'stripe_connect_id')) {
                $table->string('stripe_connect_id')->nullable()->after('stripe_customer_id')
                    ->comment('Stripe Connect Account ID for service providers');
            }
        });

        // Add Stripe fields to caregivers table
        Schema::table('caregivers', function (Blueprint $table) {
            if (!Schema::hasColumn('caregivers', 'stripe_connect_id')) {
                $table->string('stripe_connect_id')->nullable()->after('user_id')
                    ->comment('Stripe Connect Account ID');
            }
            
            if (!Schema::hasColumn('caregivers', 'stripe_charges_enabled')) {
                $table->boolean('stripe_charges_enabled')->default(false)->after('stripe_connect_id')
                    ->comment('Whether caregiver can receive transfers');
            }
            
            if (!Schema::hasColumn('caregivers', 'stripe_payouts_enabled')) {
                $table->boolean('stripe_payouts_enabled')->default(false)->after('stripe_charges_enabled')
                    ->comment('Whether caregiver onboarding is complete');
            }
        });

        // Add Stripe fields to time_trackings table
        Schema::table('time_trackings', function (Blueprint $table) {
            if (!Schema::hasColumn('time_trackings', 'stripe_charge_id')) {
                $table->string('stripe_charge_id')->nullable()->after('total_client_charge')
                    ->comment('Stripe PaymentIntent ID for client charge');
            }
            
            if (!Schema::hasColumn('time_trackings', 'client_charged_at')) {
                $table->timestamp('client_charged_at')->nullable()->after('stripe_charge_id')
                    ->comment('When client was charged');
            }
            
            if (!Schema::hasColumn('time_trackings', 'stripe_transfer_id')) {
                $table->string('stripe_transfer_id')->nullable()->after('client_charged_at')
                    ->comment('Stripe Transfer ID for caregiver payout');
            }
            
            if (!Schema::hasColumn('time_trackings', 'marketing_paid')) {
                $table->boolean('marketing_paid')->default(false)->after('marketing_partner_commission')
                    ->comment('Whether marketing commission has been paid');
            }
            
            if (!Schema::hasColumn('time_trackings', 'training_paid')) {
                $table->boolean('training_paid')->default(false)->after('training_center_commission')
                    ->comment('Whether training commission has been paid');
            }
            
            if (!Schema::hasColumn('time_trackings', 'scheduled_clock_in')) {
                $table->timestamp('scheduled_clock_in')->nullable()->after('clock_in_time')
                    ->comment('When caregiver was scheduled to clock in (for late calculation)');
            }
            
            if (!Schema::hasColumn('time_trackings', 'late_minutes')) {
                $table->integer('late_minutes')->default(0)->after('scheduled_clock_in')
                    ->comment('How many minutes late the caregiver was');
            }
        });

        // Add Stripe fields to bookings table (for storing payment method intent)
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'stripe_setup_intent_id')) {
                $table->string('stripe_setup_intent_id')->nullable()->after('status')
                    ->comment('Stripe Setup Intent for saving payment method');
            }
            
            if (!Schema::hasColumn('bookings', 'stripe_payment_method_id')) {
                $table->string('stripe_payment_method_id')->nullable()->after('stripe_setup_intent_id')
                    ->comment('Saved payment method ID');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['stripe_customer_id', 'stripe_connect_id']);
        });

        Schema::table('caregivers', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_connect_id',
                'stripe_charges_enabled',
                'stripe_payouts_enabled'
            ]);
        });

        Schema::table('time_trackings', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_charge_id',
                'client_charged_at',
                'stripe_transfer_id',
                'marketing_paid',
                'training_paid',
                'scheduled_clock_in',
                'late_minutes'
            ]);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['stripe_setup_intent_id', 'stripe_payment_method_id']);
        });
    }
};
