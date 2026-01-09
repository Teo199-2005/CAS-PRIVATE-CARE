<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds parent_booking_id to track recurring booking chains
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'parent_booking_id')) {
                $table->unsignedBigInteger('parent_booking_id')->nullable()->after('id');
                $table->foreign('parent_booking_id')
                      ->references('id')
                      ->on('bookings')
                      ->onDelete('set null');
            }
            
            if (!Schema::hasColumn('bookings', 'recurring_count')) {
                $table->integer('recurring_count')->default(0)->after('recurring_schedule');
            }
            
            if (!Schema::hasColumn('bookings', 'last_recurring_charge_date')) {
                $table->timestamp('last_recurring_charge_date')->nullable()->after('next_payment_date');
            }
            
            if (!Schema::hasColumn('bookings', 'recurring_failed_attempts')) {
                $table->integer('recurring_failed_attempts')->default(0)->after('last_recurring_charge_date');
            }
            
            if (!Schema::hasColumn('bookings', 'recurring_status')) {
                $table->enum('recurring_status', ['active', 'paused', 'cancelled', 'failed'])->default('active')->after('recurring_failed_attempts');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['parent_booking_id']);
            $table->dropColumn([
                'parent_booking_id',
                'recurring_count',
                'last_recurring_charge_date',
                'recurring_failed_attempts',
                'recurring_status'
            ]);
        });
    }
};
