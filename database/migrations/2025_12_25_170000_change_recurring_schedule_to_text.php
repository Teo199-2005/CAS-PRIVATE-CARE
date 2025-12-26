<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Change recurring_schedule from string to text to accommodate longer JSON data
            $table->text('recurring_schedule')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Revert back to string (VARCHAR 255)
            $table->string('recurring_schedule')->nullable()->change();
        });
    }
};

