<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add assignment order tracking for multiple caregivers on same booking.
     * This determines which caregiver is currently "active" for time tracking.
     * 
     * Example: 
     * - Booking needs 3 caregivers for 45 days
     * - Caregiver 1: assignment_order = 1, active days 1-15
     * - Caregiver 2: assignment_order = 2, active days 16-30
     * - Caregiver 3: assignment_order = 3, active days 31-45
     */
    public function up(): void
    {
        Schema::table('booking_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_assignments', 'assignment_order')) {
                $table->integer('assignment_order')->default(1)->after('status')
                    ->comment('Order of assignment (1st, 2nd, 3rd caregiver for this booking)');
            }
            
            if (!Schema::hasColumn('booking_assignments', 'is_active')) {
                $table->boolean('is_active')->default(false)->after('assignment_order')
                    ->comment('Is this caregiver currently active for time tracking?');
            }
            
            if (!Schema::hasColumn('booking_assignments', 'start_date')) {
                $table->date('start_date')->nullable()->after('is_active')
                    ->comment('When this caregiver starts their portion of the booking');
            }
            
            if (!Schema::hasColumn('booking_assignments', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date')
                    ->comment('When this caregiver ends their portion of the booking');
            }
            
            if (!Schema::hasColumn('booking_assignments', 'expected_days')) {
                $table->integer('expected_days')->nullable()->after('end_date')
                    ->comment('Expected number of days for this caregiver (usually 15 days per caregiver)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_assignments', function (Blueprint $table) {
            $table->dropColumn([
                'assignment_order',
                'is_active',
                'start_date',
                'end_date',
                'expected_days'
            ]);
        });
    }
};
