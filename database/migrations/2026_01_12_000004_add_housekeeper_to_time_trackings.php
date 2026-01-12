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
        Schema::table('time_trackings', function (Blueprint $table) {
            // For housekeeper time tracking, caregiver_id must be nullable.
            // (Existing schema required caregiver_id, causing inserts to fail.)
            $table->unsignedBigInteger('caregiver_id')->nullable()->change();

            // Add housekeeper_id column (nullable)
            $table->foreignId('housekeeper_id')->nullable()->after('caregiver_id')->constrained('housekeepers')->onDelete('cascade');
            
            // Add provider_type to distinguish between caregiver and housekeeper
            $table->enum('provider_type', ['caregiver', 'housekeeper'])->default('caregiver')->after('housekeeper_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_trackings', function (Blueprint $table) {
            $table->dropForeign(['housekeeper_id']);
            $table->dropColumn(['housekeeper_id', 'provider_type']);

            // Best-effort revert. If there are existing rows with NULL caregiver_id,
            // changing back to NOT NULL may fail.
            $table->unsignedBigInteger('caregiver_id')->nullable(false)->change();
        });
    }
};
