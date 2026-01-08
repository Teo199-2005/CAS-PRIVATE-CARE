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
        Schema::table('caregivers', function (Blueprint $table) {
            // Add salary range columns after has_rn and rn_number
            $table->decimal('preferred_hourly_rate_min', 8, 2)->nullable()->after('rn_number')->default(20.00);
            $table->decimal('preferred_hourly_rate_max', 8, 2)->nullable()->after('preferred_hourly_rate_min')->default(50.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caregivers', function (Blueprint $table) {
            $table->dropColumn(['preferred_hourly_rate_min', 'preferred_hourly_rate_max']);
        });
    }
};
