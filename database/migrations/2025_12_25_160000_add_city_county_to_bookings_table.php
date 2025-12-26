<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('bookings', 'city')) {
                $table->string('city')->nullable()->after('borough');
            }
            if (!Schema::hasColumn('bookings', 'county')) {
                $table->string('county')->nullable()->after('city');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('bookings', 'county')) {
                $table->dropColumn('county');
            }
        });
    }
};

