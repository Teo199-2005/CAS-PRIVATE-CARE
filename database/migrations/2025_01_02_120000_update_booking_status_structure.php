<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if bookings table exists
        if (!Schema::hasTable('bookings')) {
            return;
        }
        
        // Database-agnostic approach: only run MODIFY on MySQL, skip on SQLite
        $connection = Schema::getConnection()->getDriverName();
        
        if ($connection === 'mysql') {
            // First, change status to varchar to allow updates (MySQL only)
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('status', 20)->default('pending')->change();
            });
            
            // Update existing data to match new enum values
            DB::table('bookings')->where('status', 'confirmed')->update(['status' => 'approved']);
            DB::table('bookings')->where('status', 'completed')->update(['status' => 'approved']);
            DB::table('bookings')->where('status', 'cancelled')->update(['status' => 'rejected']);
            
            Schema::table('bookings', function (Blueprint $table) {
                // Change status back to enum with new values including completed (MySQL only)
                $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending')->change();
            });
        }
        
        // Add assignment_status column for partial/assigned tracking if it doesn't exist
        if (!Schema::hasColumn('bookings', 'assignment_status')) {
            Schema::table('bookings', function (Blueprint $table) use ($connection) {
                if ($connection === 'mysql') {
                    $table->enum('assignment_status', ['unassigned', 'partial', 'assigned'])->default('unassigned')->after('status');
                } else {
                    // SQLite doesn't support AFTER or ENUM, use string
                    $table->string('assignment_status', 20)->default('unassigned');
                }
            });
        }
    }

    public function down(): void
    {
        $connection = Schema::getConnection()->getDriverName();
        
        if ($connection === 'mysql') {
            Schema::table('bookings', function (Blueprint $table) {
                // Revert status back to original values (MySQL only)
                $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending')->change();
            });
        }
        
        if (Schema::hasColumn('bookings', 'assignment_status')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('assignment_status');
            });
        }
    }
};