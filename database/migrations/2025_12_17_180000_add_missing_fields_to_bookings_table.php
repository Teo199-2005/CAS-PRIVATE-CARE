<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->time('start_time')->after('service_date');
            $table->decimal('hourly_rate', 8, 2)->nullable()->after('duration_days');
            $table->decimal('total_budget', 10, 2)->nullable()->after('hourly_rate');
            $table->string('payment_method')->nullable()->after('total_budget');
            $table->string('language_preference')->default('english')->after('gender_preference');
            $table->enum('background_check_level', ['standard', 'enhanced'])->default('standard')->after('language_preference');
            $table->boolean('recurring_service')->default(false)->after('transportation_needed');
            $table->string('recurring_schedule')->nullable()->after('recurring_service');
            $table->enum('urgency_level', ['asap', 'today', 'within_24h', 'scheduled'])->default('scheduled')->after('recurring_schedule');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'start_time', 'hourly_rate', 'total_budget', 'payment_method',
                'language_preference', 'background_check_level', 'recurring_service',
                'recurring_schedule', 'urgency_level'
            ]);
        });
    }
};