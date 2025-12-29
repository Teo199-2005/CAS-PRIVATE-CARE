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
        Schema::table('users', function (Blueprint $table) {
            // Check if EIN column exists before dropping
            if (Schema::hasColumn('users', 'ein')) {
                $table->dropColumn('ein');
            }
            
            // Add SSN and ITIN columns if they don't exist
            if (!Schema::hasColumn('users', 'ssn')) {
                $table->string('ssn')->nullable();
            }
            if (!Schema::hasColumn('users', 'itin')) {
                $table->string('itin')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove SSN and ITIN columns
            $table->dropColumn(['ssn', 'itin']);
            
            // Add back EIN column
            $table->string('ein')->nullable();
        });
    }
};