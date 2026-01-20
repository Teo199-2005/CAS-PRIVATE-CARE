<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if an index exists on a table (Laravel 12 compatible)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Run the migrations.
     * 
     * This migration adds all missing columns identified in the comprehensive audit:
     * 1. bookings.assignment_status - For tracking caregiver assignment status
     * 2. clients.address - For client address storage
     * 3. Additional indexes for performance
     */
    public function up(): void
    {
        // Add assignment_status to bookings table
        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'assignment_status')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('assignment_status')->nullable()->default('unassigned')
                    ->after('status')
                    ->comment('Tracks caregiver assignment: unassigned, partially_assigned, fully_assigned');
            });
            
            // Add index separately to handle if it already exists
            if (!$this->indexExists('bookings', 'bookings_assignment_status_index')) {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->index('assignment_status');
                });
            }
        }

        // Add address to clients table
        if (Schema::hasTable('clients') && !Schema::hasColumn('clients', 'address')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('address')->nullable()->after('user_id');
                $table->string('city')->nullable()->after('address');
                $table->string('state')->nullable()->default('NY')->after('city');
                $table->string('zip_code', 10)->nullable()->after('state');
                $table->string('country')->nullable()->default('US')->after('zip_code');
            });
        }

        // Add missing indexes for performance optimization
        if (Schema::hasTable('time_trackings')) {
            if (Schema::hasColumn('time_trackings', 'work_date') && 
                !$this->indexExists('time_trackings', 'time_trackings_work_date_index')) {
                Schema::table('time_trackings', function (Blueprint $table) {
                    $table->index('work_date');
                });
            }
            
            if (Schema::hasColumn('time_trackings', 'created_at') && 
                !$this->indexExists('time_trackings', 'time_trackings_created_at_index')) {
                Schema::table('time_trackings', function (Blueprint $table) {
                    $table->index('created_at');
                });
            }
        }

        // Add session management columns for security
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'last_activity_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_activity_at')->nullable()->after('remember_token');
                $table->string('last_ip_address', 45)->nullable()->after('last_activity_at');
                $table->boolean('two_factor_enabled')->default(false)->after('last_ip_address');
                $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            });
        }

        // Add API versioning support
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'api_version')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('api_version')->nullable()->default('v1')->after('email_verified_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'assignment_status')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropIndex(['assignment_status']);
                $table->dropColumn('assignment_status');
            });
        }

        if (Schema::hasTable('clients')) {
            $columns = ['address', 'city', 'state', 'zip_code', 'country'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('clients', $column)) {
                    Schema::table('clients', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }

        if (Schema::hasTable('users')) {
            $columns = ['last_activity_at', 'last_ip_address', 'two_factor_enabled', 
                       'two_factor_secret', 'two_factor_confirmed_at', 'api_version'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    Schema::table('users', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
};
