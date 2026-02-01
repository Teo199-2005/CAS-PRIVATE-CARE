<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure users table has city and county columns (for profile address).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city', 100)->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'county')) {
                $table->string('county', 100)->nullable()->after('city');
            }
            if (!Schema::hasColumn('users', 'borough')) {
                $table->string('borough', 100)->nullable()->after('county');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Do not drop columns; other migrations may have created them.
    }
};
