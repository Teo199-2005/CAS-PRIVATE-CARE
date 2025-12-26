<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caregiver_id')->constrained('caregivers')->onDelete('cascade');
            $table->enum('type', ['payment', 'payout', 'bonus', 'refund']);
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['completed', 'pending', 'failed'])->default('completed');
            $table->string('method')->default('Bank Transfer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
