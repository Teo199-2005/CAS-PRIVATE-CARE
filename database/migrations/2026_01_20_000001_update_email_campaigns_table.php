<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns to email_campaigns table
        Schema::table('email_campaigns', function (Blueprint $table) {
            // Add preview_text column if it doesn't exist
            if (!Schema::hasColumn('email_campaigns', 'preview_text')) {
                $table->string('preview_text')->nullable()->after('content');
            }
            
            // Add filters column if it doesn't exist
            if (!Schema::hasColumn('email_campaigns', 'filters')) {
                $table->json('filters')->nullable()->after('selected_client_ids');
            }
        });

        // Modify the type column to support additional values
        // Using raw SQL since Laravel doesn't support modifying enums easily
        DB::statement("ALTER TABLE email_campaigns MODIFY COLUMN type VARCHAR(50) DEFAULT 'custom'");
        
        // Modify the target_audience column to support additional values
        DB::statement("ALTER TABLE email_campaigns MODIFY COLUMN target_audience VARCHAR(50) DEFAULT 'all'");
        
        // Modify the status column to support additional values like 'sending'
        DB::statement("ALTER TABLE email_campaigns MODIFY COLUMN status VARCHAR(50) DEFAULT 'draft'");
    }

    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('email_campaigns', 'preview_text')) {
                $table->dropColumn('preview_text');
            }
            if (Schema::hasColumn('email_campaigns', 'filters')) {
                $table->dropColumn('filters');
            }
        });
    }
};
