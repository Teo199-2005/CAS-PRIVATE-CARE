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
        Schema::create('time_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caregiver_id')->constrained('caregivers')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->datetime('clock_in_time');
            $table->datetime('clock_out_time')->nullable();
            $table->decimal('hours_worked', 8, 2)->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('active'); // active, completed
            $table->date('work_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_trackings');
    }
};
