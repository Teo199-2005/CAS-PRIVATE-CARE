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
            // Remove EIN column
            $table->dropColumn('ein');
            
            // Add SSN and ITIN columns
            $table->string('ssn')->nullable();
            $table->string('itin')->nullable();
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