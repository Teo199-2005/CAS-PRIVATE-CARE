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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings');
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('caregiver_id')->nullable()->constrained('caregivers');
            $table->decimal('amount', 10, 2);
            $table->decimal('platform_fee', 10, 2)->default(0.00);
            $table->decimal('caregiver_amount', 10, 2)->nullable();
            $table->enum('payment_method', ['credit_card', 'debit_card', 'bank_transfer', 'paypal', 'cash']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
