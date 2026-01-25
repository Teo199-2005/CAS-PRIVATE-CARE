<?php

/**
 * ========================================================================
 * CAS Private Care - API Routes v1
 * ========================================================================
 * 
 * This file contains all versioned API routes for version 1.
 * All routes are prefixed with /api/v1
 * 
 * For backward compatibility, legacy routes in api.php continue to work.
 * New features should be added here first.
 * 
 * @version 1.0.0
 * @since 2026-01-24
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Services\ZipCodeService;

// ============================================
// PUBLIC API ROUTES v1 (Rate Limited - 60/min)
// ============================================
Route::middleware(['throttle:60,1'])->group(function () {

    /**
     * ZIP Code Lookup
     * Returns city/state information for a given ZIP code
     * 
     * @param string $zip 5-digit ZIP code
     * @return JsonResponse {success, zip, city, state, place, location}
     */
    Route::get('/zipcode-lookup/{zip}', function (string $zip) {
        $zip = preg_replace('/\D+/', '', $zip);
        if (!preg_match('/^\d{5}$/', $zip)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid ZIP code format',
                'message' => 'ZIP code must be 5 digits'
            ], 422);
        }

        $location = ZipCodeService::lookupZipCode($zip);
        if (!$location) {
            return response()->json([
                'success' => false,
                'error' => 'Unknown ZIP code',
                'message' => 'ZIP code not found in our database'
            ], 404);
        }

        [$city, $state] = array_pad(explode(',', $location, 2), 2, '');
        $city = trim((string) $city);
        $state = strtoupper(trim((string) $state)) ?: 'NY';

        return response()->json([
            'success' => true,
            'data' => [
                'zip' => $zip,
                'city' => $city,
                'state' => $state,
                'formatted' => "{$city}, {$state}",
            ],
            // Legacy compatibility fields
            'zip' => $zip,
            'city' => $city,
            'state' => $state,
            'place' => "{$city}, {$state}",
            'location' => "{$city}, {$state}",
        ]);
    })->name('v1.zipcode.lookup');

    /**
     * Health Check
     * Returns API status and version information
     */
    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'version' => '1.0.0',
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
        ]);
    })->name('v1.health');

});

// ============================================
// AUTHENTICATED API ROUTES v1
// ============================================
Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {

    /**
     * Get Current User Profile
     */
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'status' => $user->status,
                'created_at' => $user->created_at->toISOString(),
            ],
        ]);
    })->name('v1.user.profile');

    /**
     * Payment Routes
     */
    Route::prefix('payments')->group(function () {
        Route::post('/create-intent', [StripeController::class, 'createPaymentIntent'])
            ->name('v1.payments.create-intent');
        Route::post('/confirm', [StripeController::class, 'confirmPayment'])
            ->name('v1.payments.confirm');
        Route::get('/methods', [StripeController::class, 'getPaymentMethods'])
            ->name('v1.payments.methods');
    });

    /**
     * Booking Routes
     */
    Route::prefix('bookings')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('v1.bookings.index');
        Route::post('/', [BookingController::class, 'store'])->name('v1.bookings.store');
        Route::get('/{id}', [BookingController::class, 'show'])->name('v1.bookings.show');
        Route::put('/{id}', [BookingController::class, 'update'])->name('v1.bookings.update');
        Route::delete('/{id}', [BookingController::class, 'destroy'])->name('v1.bookings.destroy');
    });

});

// ============================================
// ADMIN API ROUTES v1
// ============================================
Route::middleware(['auth:sanctum', 'user.type:admin', '2fa', 'throttle:120,1'])->prefix('admin')->group(function () {

    /**
     * Admin Dashboard Stats
     */
    Route::get('/stats', [AdminController::class, 'getStats'])->name('v1.admin.stats');
    
    /**
     * User Management
     */
    Route::prefix('users')->group(function () {
        Route::get('/', [AdminController::class, 'getUsers'])->name('v1.admin.users.index');
        Route::post('/', [AdminController::class, 'storeUser'])->name('v1.admin.users.store');
        Route::get('/{id}', [AdminController::class, 'getUser'])->name('v1.admin.users.show');
        Route::put('/{id}', [AdminController::class, 'updateUser'])->name('v1.admin.users.update');
        Route::delete('/{id}', [AdminController::class, 'deleteUser'])->name('v1.admin.users.destroy');
        Route::put('/{id}/status', [AdminController::class, 'updateUserStatus'])->name('v1.admin.users.status');
    });

    /**
     * Application Management
     */
    Route::prefix('applications')->group(function () {
        Route::get('/', [AdminController::class, 'getApplications'])->name('v1.admin.applications.index');
        Route::post('/{id}/approve', [AdminController::class, 'approveApplication'])->name('v1.admin.applications.approve');
        Route::post('/{id}/reject', [AdminController::class, 'rejectApplication'])->name('v1.admin.applications.reject');
    });

});
