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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['card', 'bank_account'])->default('card');
            
            // Card fields
            $table->string('card_type')->nullable(); // visa, mastercard, amex, etc.
            $table->string('last_four', 4)->nullable();
            $table->string('card_holder_name')->nullable();
            $table->string('expiry_month', 2)->nullable();
            $table->string('expiry_year', 4)->nullable();
            
            // Bank account fields
            $table->string('bank_name')->nullable();
            $table->string('account_type')->nullable(); // checking, savings
            $table->string('account_last_four', 4)->nullable();
            $table->string('routing_last_four', 4)->nullable();
            
            // Common fields
            $table->boolean('is_default')->default(false);
            $table->string('stripe_payment_method_id')->nullable(); // For future Stripe integration
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Create payment_settings table
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('payout_frequency')->default('Weekly'); // Weekly, Bi-weekly, Monthly
            $table->string('payout_method')->default('Bank Transfer'); // Bank Transfer, PayPal, Check
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
        Schema::dropIfExists('payment_methods');
    }
};
