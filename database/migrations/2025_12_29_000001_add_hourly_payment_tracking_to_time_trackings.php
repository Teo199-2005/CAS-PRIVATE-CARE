<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds comprehensive hourly payment tracking to time_trackings table.
     * 
     * HOURLY RATE STRUCTURE:
     * - Caregiver: $28.00/hr
     * - Marketing Partner: $1.00/hr (if booking used referral code)
     * - Training Center: $0.50/hr (if caregiver has training center)
     * - Agency: Remainder ($10.50 with referral, $16.50 without referral)
     * 
     * With Referral: $28 + $1 + $0.50 + $10.50 = $40/hr total
     * Without Referral: $28 + $0 + $0.50 + $16.50 = $45/hr total
     */
    public function up(): void
    {
        Schema::table('time_trackings', function (Blueprint $table) {
            // Earnings breakdown per hour worked
            if (!Schema::hasColumn('time_trackings', 'caregiver_earnings')) {
                $table->decimal('caregiver_earnings', 8, 2)->nullable()->after('hours_worked')
                    ->comment('Caregiver earnings: hours_worked × $28.00');
            }
            
            if (!Schema::hasColumn('time_trackings', 'marketing_partner_id')) {
                $table->foreignId('marketing_partner_id')->nullable()->after('caregiver_earnings')
                    ->constrained('users')->onDelete('set null')
                    ->comment('Marketing partner who referred the client');
            }
            
            if (!Schema::hasColumn('time_trackings', 'marketing_partner_commission')) {
                $table->decimal('marketing_partner_commission', 8, 2)->nullable()->after('marketing_partner_id')
                    ->comment('Marketing commission: hours_worked × $1.00 (if referral used)');
            }
            
            if (!Schema::hasColumn('time_trackings', 'training_center_user_id')) {
                $table->foreignId('training_center_user_id')->nullable()->after('marketing_partner_commission')
                    ->constrained('users')->onDelete('set null')
                    ->comment('Training center associated with caregiver');
            }
            
            // Note: training_center_commission already exists from previous migration
            // But we'll ensure it's properly documented
            
            if (!Schema::hasColumn('time_trackings', 'agency_commission')) {
                $table->decimal('agency_commission', 8, 2)->nullable()->after('training_center_commission')
                    ->comment('Agency commission: remainder after caregiver, marketing, training splits');
            }
            
            if (!Schema::hasColumn('time_trackings', 'total_client_charge')) {
                $table->decimal('total_client_charge', 8, 2)->nullable()->after('agency_commission')
                    ->comment('Total charged to client: hours_worked × ($40 or $45 depending on referral)');
            }
            
            // Payment tracking
            if (!Schema::hasColumn('time_trackings', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('total_client_charge')
                    ->comment('When this time entry was paid out');
            }
            
            if (!Schema::hasColumn('time_trackings', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'processing', 'paid', 'failed'])
                    ->default('pending')->after('paid_at');
            }
            
            // Link to booking for commission calculation
            if (!Schema::hasColumn('time_trackings', 'booking_id')) {
                $table->foreignId('booking_id')->nullable()->after('payment_status')
                    ->constrained('bookings')->onDelete('set null')
                    ->comment('Associated booking for commission tracking');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_trackings', function (Blueprint $table) {
            $table->dropForeign(['marketing_partner_id']);
            $table->dropForeign(['training_center_user_id']);
            $table->dropForeign(['booking_id']);
            
            $table->dropColumn([
                'caregiver_earnings',
                'marketing_partner_id',
                'marketing_partner_commission',
                'training_center_user_id',
                'agency_commission',
                'total_client_charge',
                'paid_at',
                'payment_status',
                'booking_id'
            ]);
        });
    }
};
