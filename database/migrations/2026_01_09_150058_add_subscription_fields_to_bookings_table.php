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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->after('stripe_payment_intent_id');
            $table->enum('payment_type', ['one-time', 'recurring'])->default('one-time')->after('stripe_subscription_id');
            $table->boolean('auto_pay_enabled')->default(false)->after('payment_type');
            $table->timestamp('next_payment_date')->nullable()->after('auto_pay_enabled');
            $table->string('stripe_price_id')->nullable()->after('next_payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_subscription_id',
                'payment_type',
                'auto_pay_enabled',
                'next_payment_date',
                'stripe_price_id'
            ]);
        });
    }
};
