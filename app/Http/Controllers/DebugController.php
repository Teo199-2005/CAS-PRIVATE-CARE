<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

/**
 * DebugController
 * 
 * Contains debug and development routes.
 * These routes should only be accessible in local/development environments.
 */
class DebugController extends Controller
{
    /**
     * Debug recurring bookings for a client
     */
    public function clientRecurring(Request $request)
    {
        $user = auth()->user();

        $bookings = Booking::where('client_id', $user?->id ?: $request->query('client_id'))
            ->whereIn('status', ['completed', 'approved', 'confirmed', 'in_progress'])
            ->whereNotNull('payment_status')
            ->where('payment_status', 'paid')
            ->orderBy('created_at', 'desc')
            ->get();

        $filtered = $bookings->filter(function($b) {
            return $b->recurring_service 
                && $b->auto_pay_enabled 
                && ($b->recurring_status === 'active' || $b->recurring_status === 'paused');
        })->values();

        return response()->json([
            'authenticated' => (bool) $user,
            'user' => $user ? [
                'id' => $user->id, 
                'email' => $user->email ?? null, 
                'name' => $user->name ?? null
            ] : null,
            'all_found_count' => $bookings->count(),
            'all_ids' => $bookings->pluck('id'),
            'filtered_count' => $filtered->count(),
            'filtered_ids' => $filtered->pluck('id'),
            'sample' => $filtered->take(5)->map(function($b) {
                return [
                    'id' => $b->id,
                    'status' => $b->status,
                    'payment_status' => $b->payment_status,
                    'recurring_service' => (int)$b->recurring_service,
                    'auto_pay_enabled' => (int)$b->auto_pay_enabled,
                    'recurring_status' => $b->recurring_status,
                ];
            })
        ]);
    }

    /**
     * Update booking status via artisan command
     */
    public function updateBookingStatus()
    {
        \Artisan::call('bookings:update-status');
        
        return response()->json([
            'success' => true,
            'message' => 'Booking status update completed',
            'output' => \Artisan::output()
        ]);
    }

    /**
     * Reseed bookings
     */
    public function reseedBookings()
    {
        try {
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            \App\Models\BookingAssignment::truncate();
            \App\Models\Booking::truncate();
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            (new \Database\Seeders\BookingSeeder())->run();
            
            return 'Bookings and assignments reseeded successfully!';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Migrate first/last name columns
     */
    public function migrateNames()
    {
        try {
            \Illuminate\Support\Facades\Schema::table('clients', function ($table) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('clients', 'first_name')) {
                    $table->string('first_name')->nullable()->after('user_id');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('clients', 'last_name')) {
                    $table->string('last_name')->nullable()->after('first_name');
                }
            });
            
            \Illuminate\Support\Facades\Schema::table('caregivers', function ($table) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('caregivers', 'first_name')) {
                    $table->string('first_name')->nullable()->after('user_id');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('caregivers', 'last_name')) {
                    $table->string('last_name')->nullable()->after('first_name');
                }
            });
            
            return 'First name and last name columns added!';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Migrate status column
     */
    public function migrateStatus()
    {
        try {
            \Illuminate\Support\Facades\Schema::table('users', function ($table) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
                    $table->string('status')->default('Active')->after('user_type');
                }
            });
            
            return 'Status column added to users table!';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
