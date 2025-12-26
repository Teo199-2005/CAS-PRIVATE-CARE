<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Referral Code Pricing Structure:
     * - With Referral: Client pays $40/hr (Caregiver $28 + Agency $10.50 + Marketing $1 + Training $0.50)
     * - Without Referral: Client pays $45/hr (Caregiver $28 + Agency $16.50 + Training $0.50)
     * - Marketing Associate earns $1.00/hr commission for each referred client's hours
     */
    public function up(): void
    {
        // Create referral_codes table
        Schema::create('referral_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Marketing staff user
            $table->string('code', 20)->unique(); // Unique referral code like "STAFF-247"
            $table->decimal('discount_per_hour', 8, 2)->default(5.00); // Client discount: $5/hr
            $table->decimal('commission_per_hour', 8, 2)->default(1.00); // Marketing earns: $1/hr
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0); // How many times used
            $table->decimal('total_commission_earned', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // Add referral tracking to bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('referral_code_id')->nullable()->after('status')->constrained('referral_codes')->onDelete('set null');
            $table->decimal('referral_discount_applied', 8, 2)->nullable()->after('referral_code_id'); // Discount amount per hour
        });

        // Add referred_by to clients table to track which marketing staff referred them
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('referred_by_user_id')->nullable()->after('user_id')->constrained('users')->onDelete('set null');
            $table->string('referral_code_used')->nullable()->after('referred_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['referral_code_id']);
            $table->dropColumn(['referral_code_id', 'referral_discount_applied']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['referred_by_user_id']);
            $table->dropColumn(['referred_by_user_id', 'referral_code_used']);
        });

        Schema::dropIfExists('referral_codes');
    }
};
