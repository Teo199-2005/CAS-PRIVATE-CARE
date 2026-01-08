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
            $table->boolean('has_hha')->default(false)->after('training_certificate')->comment('Home Health Aide certification');
            $table->string('hha_number')->nullable()->after('has_hha')->comment('HHA certification number');
            $table->boolean('has_cna')->default(false)->after('hha_number')->comment('Certified Nursing Assistant certification');
            $table->string('cna_number')->nullable()->after('has_cna')->comment('CNA certification number');
            $table->boolean('has_rn')->default(false)->after('cna_number')->comment('Registered Nurse license');
            $table->string('rn_number')->nullable()->after('has_rn')->comment('RN license number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caregivers', function (Blueprint $table) {
            $table->dropColumn(['has_hha', 'hha_number', 'has_cna', 'cna_number', 'has_rn', 'rn_number']);
        });
    }
};
