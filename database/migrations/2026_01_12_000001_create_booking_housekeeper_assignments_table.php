<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_housekeeper_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('housekeeper_id')->constrained('housekeepers')->cascadeOnDelete();

            $table->datetime('assigned_at');
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'cancelled'])->default('assigned');

            // Rotation/detail fields (mirrors the newer caregiver assignment model fields)
            $table->unsignedInteger('assignment_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('expected_days')->nullable();

            $table->decimal('assigned_hourly_rate', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'housekeeper_id'], 'booking_housekeeper_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_housekeeper_assignments');
    }
};
