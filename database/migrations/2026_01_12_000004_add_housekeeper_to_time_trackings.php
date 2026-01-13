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
        $connection = Schema::getConnection()->getDriverName();
        
        Schema::table('time_trackings', function (Blueprint $table) use ($connection) {
            // For housekeeper time tracking, caregiver_id must be nullable.
            // (Existing schema required caregiver_id, causing inserts to fail.)
            // Only run column modification on MySQL (SQLite doesn't support MODIFY)
            if ($connection === 'mysql') {
                $table->unsignedBigInteger('caregiver_id')->nullable()->change();
            }
        });
        
        // Add housekeeper_id column if not exists
        if (!Schema::hasColumn('time_trackings', 'housekeeper_id')) {
            Schema::table('time_trackings', function (Blueprint $table) use ($connection) {
                if ($connection === 'mysql') {
                    $table->foreignId('housekeeper_id')->nullable()->after('caregiver_id')->constrained('housekeepers')->onDelete('cascade');
                } else {
                    // SQLite doesn't support AFTER, add without position
                    $table->unsignedBigInteger('housekeeper_id')->nullable();
                }
            });
        }
        
        // Add provider_type to distinguish between caregiver and housekeeper
        if (!Schema::hasColumn('time_trackings', 'provider_type')) {
            Schema::table('time_trackings', function (Blueprint $table) use ($connection) {
                if ($connection === 'mysql') {
                    $table->enum('provider_type', ['caregiver', 'housekeeper'])->default('caregiver')->after('housekeeper_id');
                } else {
                    // SQLite doesn't support ENUM or AFTER, use string
                    $table->string('provider_type', 20)->default('caregiver');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = Schema::getConnection()->getDriverName();
        
        Schema::table('time_trackings', function (Blueprint $table) use ($connection) {
            if (Schema::hasColumn('time_trackings', 'housekeeper_id')) {
                if ($connection === 'mysql') {
                    $table->dropForeign(['housekeeper_id']);
                }
                $table->dropColumn('housekeeper_id');
            }
            
            if (Schema::hasColumn('time_trackings', 'provider_type')) {
                $table->dropColumn('provider_type');
            }

            // Best-effort revert. If there are existing rows with NULL caregiver_id,
            // changing back to NOT NULL may fail. Only run on MySQL.
            if ($connection === 'mysql') {
                $table->unsignedBigInteger('caregiver_id')->nullable(false)->change();
            }
        });
    }
};
