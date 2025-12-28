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
            if (!Schema::hasColumn('caregivers', 'training_center_approval_status')) {
                $table->enum('training_center_approval_status', ['pending', 'approved', 'rejected'])->nullable()->after('has_training_center');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caregivers', function (Blueprint $table) {
            $table->dropColumn('training_center_approval_status');
        });
    }
};
