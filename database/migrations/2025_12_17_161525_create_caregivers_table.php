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
        Schema::create('caregivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('gender', ['male', 'female']);
            $table->json('skills')->nullable();
            $table->json('specializations')->nullable();
            $table->integer('years_experience')->default(0);
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->string('license_number')->nullable();
            $table->json('certifications')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('background_check_completed')->default(false);
            $table->boolean('available_for_transport')->default(false);
            $table->enum('availability_status', ['available', 'busy', 'offline'])->default('available');
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('total_reviews')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caregivers');
    }
};
