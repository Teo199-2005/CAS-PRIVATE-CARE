<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('password_resets_custom') && !Schema::hasColumn('password_resets_custom', 'token')) {
            Schema::table('password_resets_custom', function (Blueprint $table) {
                // Add token column used to store hashed token
                $table->string('token', 255)->nullable()->after('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('password_resets_custom') && Schema::hasColumn('password_resets_custom', 'token')) {
            Schema::table('password_resets_custom', function (Blueprint $table) {
                $table->dropColumn('token');
            });
        }
    }
};
