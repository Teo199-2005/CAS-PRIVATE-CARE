<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Pricing breakdown:
     * 
     * WITH REFERRAL CODE:
     * - Caregiver: $28.00/hr
     * - Agency (net): $10.50/hr (or $11.00 if no training center)
     * - Marketing Associate: $1.00/hr
     * - Training Center: $0.50/hr (if caregiver has training center)
     * Total: $40/hr
     * 
     * WITHOUT REFERRAL CODE:
     * - Caregiver: $28.00/hr
     * - Agency: $16.50/hr (or $17.00 if no training center)
     * - Training Center: $0.50/hr (if caregiver has training center)
     * Total: $45/hr
     * 
     * Note: If caregiver doesn't have a training center, the $0.50 goes to agency instead.
     */
    public function up(): void
    {
        // Add training center tracking to caregivers
        Schema::table('caregivers', function (Blueprint $table) {
            if (!Schema::hasColumn('caregivers', 'training_center_id')) {
                $table->foreignId('training_center_id')->nullable()->after('user_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('caregivers', 'has_training_center')) {
                $table->boolean('has_training_center')->default(false)->after('training_center_id');
            }
        });

        // Add training center commission tracking to bookings
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'training_center_commission')) {
                $table->decimal('training_center_commission', 8, 2)->nullable()->after('referral_discount_applied');
            }
            if (!Schema::hasColumn('bookings', 'training_center_paid')) {
                $table->boolean('training_center_paid')->default(false)->after('training_center_commission');
            }
        });

        // Add training center commission to time_trackings for per-hour calculation
        Schema::table('time_trackings', function (Blueprint $table) {
            if (!Schema::hasColumn('time_trackings', 'training_center_commission')) {
                $table->decimal('training_center_commission', 8, 2)->nullable()->after('client_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caregivers', function (Blueprint $table) {
            $table->dropForeign(['training_center_id']);
            $table->dropColumn(['training_center_id', 'has_training_center']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['training_center_commission', 'training_center_paid']);
        });

        Schema::table('time_trackings', function (Blueprint $table) {
            $table->dropColumn('training_center_commission');
        });
    }
};
