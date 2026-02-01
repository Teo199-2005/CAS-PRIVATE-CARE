<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('housekeepers', function (Blueprint $table) {
            if (!Schema::hasColumn('housekeepers', 'stripe_account_id')) {
                $table->string('stripe_account_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('housekeepers', 'stripe_status')) {
                $table->string('stripe_status')->nullable()->after('stripe_account_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('housekeepers', function (Blueprint $table) {
            $table->dropColumn(['stripe_account_id', 'stripe_status']);
        });
    }
};
