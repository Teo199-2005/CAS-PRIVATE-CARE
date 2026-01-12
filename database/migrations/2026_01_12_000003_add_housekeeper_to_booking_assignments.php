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
        Schema::table('booking_assignments', function (Blueprint $table) {
            // Add housekeeper_id column (nullable since we also have caregiver_id)
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
        Schema::table('booking_assignments', function (Blueprint $table) {
            $table->dropForeign(['housekeeper_id']);
            $table->dropColumn(['housekeeper_id', 'provider_type']);
        });
    }
};
