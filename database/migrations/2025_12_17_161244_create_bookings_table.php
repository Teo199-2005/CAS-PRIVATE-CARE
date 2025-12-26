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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users');
            $table->string('service_type');
            $table->string('duty_type');
            $table->string('borough');
            $table->date('service_date');
            $table->integer('duration_days')->default(15);
            $table->enum('gender_preference', ['male', 'female', 'no_preference'])->default('no_preference');
            $table->json('specific_skills')->nullable();
            $table->integer('client_age')->nullable();
            $table->enum('mobility_level', ['independent', 'assisted', 'wheelchair', 'bedridden'])->nullable();
            $table->json('medical_conditions')->nullable();
            $table->boolean('transportation_needed')->default(false);
            $table->string('street_address');
            $table->string('apartment_unit')->nullable();
            $table->text('special_instructions')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
