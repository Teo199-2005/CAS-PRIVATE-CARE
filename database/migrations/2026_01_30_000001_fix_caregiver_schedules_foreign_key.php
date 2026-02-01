<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration fixes the caregiver_schedules table to remove the strict
     * foreign key constraint that was referencing users.id, since the actual
     * caregiver_id values come from the caregivers table.
     */
    public function up(): void
    {
        // Get existing foreign keys
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'caregiver_schedules' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
            AND COLUMN_NAME = 'caregiver_id'
        ");
        
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Drop each found foreign key
        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE caregiver_schedules DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: We're not adding back the foreign key as it was incorrectly configured
    }
};
