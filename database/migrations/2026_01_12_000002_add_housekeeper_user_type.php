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
        // Ensure user_type column can accept 'housekeeper' value
        // If user_type is varchar, no change needed
        // If it's enum, we need to modify it
        
        // Check current column type
        $columnType = DB::select("SHOW COLUMNS FROM users WHERE Field = 'user_type'");
        
        if (!empty($columnType)) {
            $type = $columnType[0]->Type;
            
            // If it's an enum, we need to modify it to include 'housekeeper'
            if (strpos($type, 'enum') !== false) {
                // Convert to varchar to support any user type
                DB::statement("ALTER TABLE users MODIFY COLUMN user_type VARCHAR(50) DEFAULT 'client'");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback not needed - varchar is more flexible than enum
    }
};
