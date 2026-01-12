<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('housekeepers', function (Blueprint $table) {
            // Availability filtering
            $table->index('availability_status', 'idx_housekeepers_availability');
            
            // Rating sorting
            $table->index('rating', 'idx_housekeepers_rating');
        });
        
        Schema::table('booking_assignments', function (Blueprint $table) {
            // Check if column exists before creating index
            if (Schema::hasColumn('booking_assignments', 'housekeeper_id')) {
                // Housekeeper assignment queries
                $table->index(['housekeeper_id', 'status'], 'idx_assignments_housekeeper_status');
            }
            
            if (Schema::hasColumn('booking_assignments', 'provider_type')) {
                $table->index('provider_type', 'idx_assignments_provider_type');
            }
        });
        
        Schema::table('time_trackings', function (Blueprint $table) {
            // Check if column exists before creating index
            if (Schema::hasColumn('time_trackings', 'housekeeper_id')) {
                // Housekeeper time tracking queries
                $table->index(['housekeeper_id', 'clock_in_time'], 'idx_time_tracking_housekeeper');
            }
            
            if (Schema::hasColumn('time_trackings', 'provider_type')) {
                $table->index('provider_type', 'idx_time_tracking_provider_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housekeepers', function (Blueprint $table) {
            $table->dropIndex('idx_housekeepers_availability');
            $table->dropIndex('idx_housekeepers_rating');
        });
        
        Schema::table('booking_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('booking_assignments', 'housekeeper_id')) {
                $table->dropIndex('idx_assignments_housekeeper_status');
            }
            if (Schema::hasColumn('booking_assignments', 'provider_type')) {
                $table->dropIndex('idx_assignments_provider_type');
            }
        });
        
        Schema::table('time_trackings', function (Blueprint $table) {
            if (Schema::hasColumn('time_trackings', 'housekeeper_id')) {
                $table->dropIndex('idx_time_tracking_housekeeper');
            }
            if (Schema::hasColumn('time_trackings', 'provider_type')) {
                $table->dropIndex('idx_time_tracking_provider_type');
            }
        });
    }
};
