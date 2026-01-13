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
        
        // Only run column modification on MySQL (SQLite doesn't support MODIFY)
        if ($connection === 'mysql') {
            Schema::table('time_trackings', function (Blueprint $table) {
                // Allow creating time tracking rows for housekeepers.
                $table->unsignedBigInteger('caregiver_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = Schema::getConnection()->getDriverName();
        
        // Only run column modification on MySQL (SQLite doesn't support MODIFY)
        if ($connection === 'mysql') {
            Schema::table('time_trackings', function (Blueprint $table) {
                // WARNING: this may fail if any rows have NULL caregiver_id.
                $table->unsignedBigInteger('caregiver_id')->nullable(false)->change();
            });
        }
    }
};
