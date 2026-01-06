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
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();
        
        // Helper function to check if index exists
        $indexExists = function($table, $indexName) use ($connection, $dbName) {
            $result = $connection->select("
                SELECT COUNT(*) as count
                FROM information_schema.statistics
                WHERE table_schema = '$dbName'
                AND table_name = '$table'
                AND index_name = '$indexName'
            ");
            return $result[0]->count > 0;
        };
        
        // Helper function to check if column exists
        $columnExists = function($table, $columnName) use ($connection, $dbName) {
            $result = $connection->select("
                SELECT COUNT(*) as count
                FROM information_schema.columns
                WHERE table_schema = '$dbName'
                AND table_name = '$table'
                AND column_name = '$columnName'
            ");
            return $result[0]->count > 0;
        };

        // Add indexes to bookings table
        Schema::table('bookings', function (Blueprint $table) use ($indexExists, $columnExists) {
            if (!$indexExists('bookings', 'bookings_status_index')) {
                $table->index('status');
            }
            if (!$indexExists('bookings', 'bookings_client_id_index')) {
                $table->index('client_id');
            }
            if ($columnExists('bookings', 'payment_status') && !$indexExists('bookings', 'bookings_payment_status_index')) {
                $table->index('payment_status');
            }
            if (!$indexExists('bookings', 'bookings_created_at_index')) {
                $table->index('created_at');
            }
        });

        // Add indexes to time_trackings table
        Schema::table('time_trackings', function (Blueprint $table) use ($indexExists, $columnExists) {
            if (!$indexExists('time_trackings', 'time_trackings_caregiver_id_index')) {
                $table->index('caregiver_id');
            }
            if ($columnExists('time_trackings', 'payment_status') && !$indexExists('time_trackings', 'time_trackings_payment_status_index')) {
                $table->index('payment_status');
            }
            if (!$indexExists('time_trackings', 'time_trackings_clock_in_time_index')) {
                $table->index('clock_in_time');
            }
            if ($columnExists('time_trackings', 'clock_out_time') && !$indexExists('time_trackings', 'time_trackings_clock_out_time_index')) {
                $table->index('clock_out_time');
            }
        });

        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('users', 'users_user_type_index')) {
                $table->index('user_type');
            }
            if (!$indexExists('users', 'users_status_index')) {
                $table->index('status');
            }
            if (!$indexExists('users', 'users_created_at_index')) {
                $table->index('created_at');
            }
        });

        // Add indexes to booking_assignments table
        if (Schema::hasTable('booking_assignments')) {
            Schema::table('booking_assignments', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('booking_assignments', 'booking_assignments_booking_id_index')) {
                    $table->index('booking_id');
                }
                if (!$indexExists('booking_assignments', 'booking_assignments_caregiver_id_index')) {
                    $table->index('caregiver_id');
                }
                if (!$indexExists('booking_assignments', 'booking_assignments_status_index')) {
                    $table->index('status');
                }
            });
        }

        // Add indexes to sessions table
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('sessions', 'sessions_user_id_index')) {
                    $table->index('user_id');
                }
                if (!$indexExists('sessions', 'sessions_last_activity_index')) {
                    $table->index('last_activity');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['client_id']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'client_id']);
            $table->dropIndex(['payment_status', 'status']);
        });

        // Drop indexes from time_trackings table
        Schema::table('time_trackings', function (Blueprint $table) {
            $table->dropIndex(['caregiver_id']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['clock_in_time']);
            $table->dropIndex(['clock_out_time']);
            $table->dropIndex(['caregiver_id', 'payment_status']);
            $table->dropIndex(['payment_status', 'clock_in_time']);
        });

        // Drop indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['user_type']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });

        // Drop indexes from booking_assignments table
        if (Schema::hasTable('booking_assignments')) {
            Schema::table('booking_assignments', function (Blueprint $table) {
                $table->dropIndex(['booking_id']);
                $table->dropIndex(['caregiver_id']);
                $table->dropIndex(['status']);
                $table->dropIndex(['booking_id', 'caregiver_id']);
            });
        }

        // Drop indexes from sessions table
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
                $table->dropIndex(['last_activity']);
            });
        }
    }
};
