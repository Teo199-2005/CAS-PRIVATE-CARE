<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds housekeeper support to reviews and payments tables
     */
    public function up(): void
    {
        // Add housekeeper_id to reviews table
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'housekeeper_id')) {
                $table->foreignId('housekeeper_id')->nullable()->after('caregiver_id')->constrained('housekeepers')->onDelete('cascade');
            }
            if (!Schema::hasColumn('reviews', 'provider_type')) {
                $table->enum('provider_type', ['caregiver', 'housekeeper'])->default('caregiver')->after('housekeeper_id');
            }
        });

        // Add housekeeper columns to payments table
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'housekeeper_id')) {
                $table->foreignId('housekeeper_id')->nullable()->after('caregiver_id')->constrained('housekeepers')->onDelete('cascade');
            }
            if (!Schema::hasColumn('payments', 'housekeeper_amount')) {
                $table->decimal('housekeeper_amount', 10, 2)->nullable()->after('caregiver_amount');
            }
            if (!Schema::hasColumn('payments', 'provider_type')) {
                $table->enum('provider_type', ['caregiver', 'housekeeper'])->default('caregiver')->after('housekeeper_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'housekeeper_id')) {
                $table->dropForeign(['housekeeper_id']);
                $table->dropColumn('housekeeper_id');
            }
            if (Schema::hasColumn('reviews', 'provider_type')) {
                $table->dropColumn('provider_type');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'housekeeper_id')) {
                $table->dropForeign(['housekeeper_id']);
                $table->dropColumn('housekeeper_id');
            }
            if (Schema::hasColumn('payments', 'housekeeper_amount')) {
                $table->dropColumn('housekeeper_amount');
            }
            if (Schema::hasColumn('payments', 'provider_type')) {
                $table->dropColumn('provider_type');
            }
        });
    }
};
