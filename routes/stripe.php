<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Stripe\PaymentMethodController;
use App\Http\Controllers\Stripe\ConnectController;
use App\Http\Controllers\Stripe\AdminPaymentController;

/*
|--------------------------------------------------------------------------
| Stripe API Routes
|--------------------------------------------------------------------------
|
| These routes are organized by controller for better maintainability.
| All routes require authentication except webhooks.
|
*/

// Payment Methods (Clients)
Route::middleware(['auth:sanctum'])->prefix('stripe/payment-methods')->group(function () {
    Route::post('/setup-intent', [PaymentMethodController::class, 'createSetupIntent']);
    Route::get('/', [PaymentMethodController::class, 'index']);
    Route::post('/', [PaymentMethodController::class, 'store']);
    Route::delete('/{paymentMethodId}', [PaymentMethodController::class, 'destroy']);
    Route::put('/{paymentMethodId}/default', [PaymentMethodController::class, 'setDefault']);
});

// Stripe Connect (Caregivers/Housekeepers)
Route::middleware(['auth:sanctum'])->prefix('stripe/connect')->group(function () {
    Route::post('/onboard', [ConnectController::class, 'createOnboardingLink']);
    Route::get('/status', [ConnectController::class, 'checkStatus']);
    Route::post('/bank-account', [ConnectController::class, 'connectBankAccount']);
    Route::post('/session', [ConnectController::class, 'createAccountSession']);
    Route::get('/payout-methods', [ConnectController::class, 'getPayoutMethods']);
    Route::get('/balance', [ConnectController::class, 'getBalance']);
});

// Admin Payment Operations
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin/stripe')->group(function () {
    Route::post('/process-payment', [AdminPaymentController::class, 'processPayment']);
    Route::post('/process-payouts', [AdminPaymentController::class, 'processWeeklyPayouts']);
    Route::get('/payment-history/{userId}', [AdminPaymentController::class, 'getPaymentHistory']);
    Route::get('/payout-history/{userId}', [AdminPaymentController::class, 'getPayoutHistory']);
    Route::post('/refund', [AdminPaymentController::class, 'processRefund']);
});

// ============================================
// NEW ARCHITECTURE ROUTES (v2) - January 28, 2026
// ============================================
// These routes use the new Service Pattern architecture with focused controllers.
// The v2 prefix allows gradual migration without breaking existing integrations.

use App\Http\Controllers\Stripe\ClientStripeController;
use App\Http\Controllers\Stripe\CaregiverStripeController;
use App\Http\Controllers\Stripe\HousekeeperStripeController;
use App\Http\Controllers\Stripe\AdminStripeController;

// Client Stripe Routes (v2)
Route::middleware(['auth:sanctum'])->prefix('v2/stripe')->group(function () {
    Route::post('/create-setup-intent', [ClientStripeController::class, 'createSetupIntent']);
    Route::get('/payment-methods', [ClientStripeController::class, 'getPaymentMethods']);
    Route::post('/save-payment-method', [ClientStripeController::class, 'savePaymentMethod']);
    Route::delete('/payment-methods/{paymentMethodId}', [ClientStripeController::class, 'deletePaymentMethod']);
    Route::post('/process-payment', [ClientStripeController::class, 'processPayment']);
});

// Caregiver Stripe Connect Routes (v2)
Route::middleware(['auth:sanctum'])->prefix('v2/caregiver/stripe')->group(function () {
    Route::post('/onboard', [CaregiverStripeController::class, 'startOnboarding']);
    Route::get('/status', [CaregiverStripeController::class, 'getStatus']);
    Route::get('/dashboard', [CaregiverStripeController::class, 'getDashboardLink']);
    Route::get('/balance', [CaregiverStripeController::class, 'getBalance']);
});

// Housekeeper Stripe Connect Routes (v2)
Route::middleware(['auth:sanctum'])->prefix('v2/housekeeper/stripe')->group(function () {
    Route::post('/onboard', [HousekeeperStripeController::class, 'startOnboarding']);
    Route::get('/status', [HousekeeperStripeController::class, 'getStatus']);
    Route::get('/dashboard', [HousekeeperStripeController::class, 'getDashboardLink']);
    Route::get('/balance', [HousekeeperStripeController::class, 'getBalance']);
});

// Admin Stripe Routes (v2)
Route::middleware(['auth:sanctum', 'user.type:admin,adminstaff'])->prefix('v2/admin/stripe')->group(function () {
    Route::get('/payments', [AdminStripeController::class, 'listPayments']);
    Route::get('/payments/{paymentIntentId}', [AdminStripeController::class, 'getPayment']);
    Route::post('/refund', [AdminStripeController::class, 'processRefund']);
    Route::get('/accounts', [AdminStripeController::class, 'listConnectedAccounts']);
    Route::get('/accounts/{accountId}', [AdminStripeController::class, 'getConnectedAccount']);
    Route::post('/accounts/{accountId}/sync', [AdminStripeController::class, 'syncAccountStatus']);
    Route::get('/balance', [AdminStripeController::class, 'getPlatformBalance']);
    Route::get('/transfers', [AdminStripeController::class, 'listTransfers']);
    Route::get('/stats', [AdminStripeController::class, 'getDashboardStats']);
});

// Stripe Connect Callback Routes (Web)
Route::middleware(['auth'])->group(function () {
    Route::get('/caregiver/stripe/callback', [CaregiverStripeController::class, 'handleCallback'])
        ->name('caregiver.stripe.callback');
    Route::get('/caregiver/stripe/refresh', [CaregiverStripeController::class, 'handleRefresh'])
        ->name('caregiver.stripe.refresh');
    Route::get('/housekeeper/stripe/callback', [HousekeeperStripeController::class, 'handleCallback'])
        ->name('housekeeper.stripe.callback');
    Route::get('/housekeeper/stripe/refresh', [HousekeeperStripeController::class, 'handleRefresh'])
        ->name('housekeeper.stripe.refresh');
});
