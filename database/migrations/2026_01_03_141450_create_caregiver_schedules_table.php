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
        Schema::create('caregiver_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('caregiver_id');
            $table->json('days'); // Array of day names: ['monday', 'tuesday', etc.]
            $table->json('schedules'); // Object with day-specific times: { monday: { start_time: '08:00', end_time: '17:00' } }
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('caregiver_id')->references('id')->on('users')->onDelete('cascade');
            
            // Unique constraint: one schedule per caregiver per booking
            $table->unique(['booking_id', 'caregiver_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caregiver_schedules');
    }
};
