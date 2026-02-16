<?php

/**
 * ========================================================================
 * CAS Private Care - Web Routes
 * ========================================================================
 * 
 * This file contains all web routes for the application.
 * Routes are organized by category and follow Laravel best practices:
 * - Routes only define URL → Controller mappings
 * - All business logic is in controllers
 * - Static pages use Route::view() where possible
 * 
 * @see https://laravel.com/docs/routing
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\BookingAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\StaffAdminController;
use App\Http\Controllers\Admin\ReportAdminController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CaregiverController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\PaymentPageController;
use App\Http\Controllers\PublicApiController;
use App\Http\Controllers\ApplicationStatusController;
use App\Http\Controllers\CaregiverDataController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\MarketingStaffController;
use App\Http\Controllers\TrainingCenterController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\FeaturedPostController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Api\UserProfileController;

// ============================================
// HEALTH CHECK ROUTES (for monitoring & DevOps)
// ============================================
Route::get('/health', [HealthController::class, 'index'])->name('health');
Route::get('/health/detailed', [HealthController::class, 'detailed'])->name('health.detailed');
Route::get('/health/ready', [HealthController::class, 'ready'])->name('health.ready');
Route::get('/health/live', [HealthController::class, 'live'])->name('health.live');

// ============================================
// OFFLINE PAGE (for PWA/Service Worker)
// ============================================
Route::view('/offline', 'offline')->name('offline');

// ============================================
// PUBLIC ROUTES
// ============================================

// CSRF token refresh (for login and other forms when token may be stale)
Route::get('/csrf-token', fn () => response()->json(['token' => csrf_token()]))->middleware('web');

// Landing & Core Pages
Route::get('/', [LandingController::class, 'index']);
Route::get('/api/landing/stats', [LandingController::class, 'stats']);
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);

// Static SEO Pages
Route::get('/caregiver-new-york', [PageController::class, 'caregiverNewYork'])->name('caregiver-new-york');
Route::get('/housekeeper-new-york', [PageController::class, 'housekeeperNewYork'])->name('housekeeper-new-york');
Route::get('/hire-caregiver-new-york', [PageController::class, 'hireCaregiverNewYork'])->name('hire-caregiver-new-york');
Route::get('/caregiver-brooklyn', [PageController::class, 'caregiverBrooklyn'])->name('caregiver-brooklyn');
Route::get('/caregiver-manhattan', [PageController::class, 'caregiverManhattan'])->name('caregiver-manhattan');
Route::get('/caregiver-queens', [PageController::class, 'caregiverQueens'])->name('caregiver-queens');
Route::get('/caregiver-bronx', [PageController::class, 'caregiverBronx'])->name('caregiver-bronx');
Route::get('/caregiver-staten-island', [PageController::class, 'caregiverStatenIsland'])->name('caregiver-staten-island');
Route::get('/contractor-partner', [PageController::class, 'contractorPartner'])->name('contractor-partner');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/housekeeping-personal-assistant', [PageController::class, 'housekeepingPersonalAssistant'])->name('housekeeping-personal-assistant');
Route::get('/housekeeping-new-york', [PageController::class, 'housekeepingNewYork'])->name('housekeeping-new-york');
Route::get('/personal-assistant-new-york', [PageController::class, 'personalAssistantNewYork'])->name('personal-assistant-new-york');
Route::get('/training-center', [PageController::class, 'trainingCenter'])->name('training-center');
Route::get('/services', [PageController::class, 'services'])->name('services');
Route::get('/contractors', [PageController::class, 'contractors'])->name('contractors');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/blog/category/{category}', [BlogController::class, 'category'])->name('blog.category');

// Contact (with reCAPTCHA protection)
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->middleware(['throttle:3,1', 'verify.recaptcha:contact'])->name('contact.submit');

// Book: send to client dashboard (booking is done there) or login
Route::get('/book', function () {
    if (auth()->check()) {
        return redirect('/client/dashboard');
    }
    return redirect('/login?redirect=/client/dashboard');
})->name('book');

// Authentication (with smart rate limiting and reCAPTCHA for security)
// Smart throttle adjusts limits based on environment: 50/min in testing, 10/min in production
Route::get('/login', [PageController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware(['smart.throttle:login', 'verify.recaptcha:login']);
Route::get('/register', [PageController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware(['smart.throttle:register', 'verify.recaptcha:register']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware(['smart.throttle:password-reset', 'verify.recaptcha:reset'])->name('password.update');
Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->middleware(['smart.throttle:password-reset', 'verify.recaptcha:forgot']);

// OAuth
Route::get('/auth/{provider}', [AuthController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

// Email Verification
Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail'])
    ->middleware('auth')
    ->name('verification.send');
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

// OTP Verification (authenticated)
Route::middleware(['auth'])->prefix('api/auth')->group(function () {
    Route::post('/send-otp', [AuthController::class, 'sendOTP']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOTP']);
    Route::get('/verification-status', [PublicApiController::class, 'verificationStatus']);
});

// ============================================
// SESSION MANAGEMENT ROUTES (Admin Single Session Enforcement)
// ============================================
Route::middleware(['auth'])->prefix('api/session')->group(function () {
    Route::post('/generate-token', [\App\Http\Controllers\SessionController::class, 'generateSessionToken']);
    Route::get('/validate', [\App\Http\Controllers\SessionController::class, 'validateSession']);
    Route::get('/heartbeat', [\App\Http\Controllers\SessionController::class, 'heartbeat']);
    Route::post('/clear', [\App\Http\Controllers\SessionController::class, 'clearSession']);
});

// ============================================
// TWO-FACTOR AUTHENTICATION ROUTES (Admin/AdminStaff)
// ============================================

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/2fa/verify', [\App\Http\Controllers\Admin\TwoFactorController::class, 'show'])
        ->name('admin.2fa.verify');
    Route::post('/2fa/verify', [\App\Http\Controllers\Admin\TwoFactorController::class, 'verify'])
        ->name('admin.2fa.verify.submit');
    Route::post('/2fa/send-otp', [\App\Http\Controllers\Admin\TwoFactorController::class, 'sendOTP'])
        ->name('admin.2fa.send-otp');
    Route::post('/2fa/resend', [\App\Http\Controllers\Admin\TwoFactorController::class, 'resendOTP'])
        ->name('admin.2fa.resend');
});

// ============================================
// PUBLIC API ROUTES (No authentication)
// ============================================

Route::prefix('api')->middleware(['web'])->withoutMiddleware([\Illuminate\Auth\Middleware\Authenticate::class])->group(function () {
    Route::get('/zipcode-lookup/{zip}', [PublicApiController::class, 'zipCodeLookup']);
    Route::get('/location-data', [PublicApiController::class, 'locationData']);
    Route::get('/check-email-exists/{email}', [PublicApiController::class, 'checkEmailExists']);
});

// ============================================
// PROTECTED DASHBOARD ROUTES
// ============================================

Route::middleware(['auth'])->group(function () {
    // Client Dashboards
    Route::get('/client/dashboard', [DashboardRedirectController::class, 'clientDashboard']);
    Route::get('/client/dashboard-vue', [DashboardRedirectController::class, 'clientDashboard'])->name('client.dashboard');
    Route::get('/client/payment-setup', [DashboardRedirectController::class, 'clientPaymentSetup'])->name('client.payment.setup');
    
    // Payment Pages
    Route::get('/payment', [PaymentPageController::class, 'show'])->name('payment');
    Route::get('/payment-success', [PaymentPageController::class, 'success'])->name('payment.success');
    
    // Caregiver Dashboards
    Route::get('/caregiver/dashboard', [DashboardRedirectController::class, 'caregiverDashboard']);
    Route::get('/caregiver/dashboard-vue', [DashboardRedirectController::class, 'caregiverDashboardVue'])->name('caregiver.dashboard');
    
    // Housekeeper Dashboard
    Route::get('/housekeeper/dashboard-vue', [DashboardRedirectController::class, 'housekeeperDashboardVue'])->name('housekeeper.dashboard');
    
    // Admin Dashboards
    Route::get('/admin/dashboard-vue', [DashboardRedirectController::class, 'adminDashboardVue'])->name('admin.dashboard');
    Route::get('/admin-staff/dashboard-vue', [DashboardRedirectController::class, 'adminStaffDashboardVue'])->name('admin-staff.dashboard');
    Route::get('/admin/settings', [AdminController::class, 'settings'])->middleware('user.type:admin,adminstaff');
    Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->middleware('user.type:admin,adminstaff');
    
    // Marketing Dashboard
    Route::get('/marketing/dashboard-vue', [DashboardRedirectController::class, 'marketingDashboardVue'])->name('marketing.dashboard');
    
    // Training Dashboard
    Route::get('/training/dashboard-vue', [DashboardRedirectController::class, 'trainingDashboardVue'])->name('training.dashboard');
    
    // Bank Connection Pages
    Route::get('/connect-bank-account', [DashboardRedirectController::class, 'connectBankAccount'])->name('connect.bank.account');
    Route::get('/connect-bank-account-housekeeper', [DashboardRedirectController::class, 'connectBankAccountHousekeeper'])->name('connect.bank.account.housekeeper');
    Route::get('/connect-bank-account-marketing', [DashboardRedirectController::class, 'connectBankAccountMarketing'])->name('marketing.connect.bank');
    Route::get('/connect-bank-account-training', [DashboardRedirectController::class, 'connectBankAccountTraining'])->name('training.connect.bank');
    
    // Payment Method Pages
    Route::get('/link-payment-method', [DashboardRedirectController::class, 'linkPaymentMethod'])->name('link.payment.method');
    Route::get('/connect-payment-method', [DashboardRedirectController::class, 'connectPaymentMethod'])->name('connect.payment.method');
    Route::get('/stripe-connect-onboarding', [DashboardRedirectController::class, 'stripeConnectOnboarding'])->name('stripe.connect.onboarding');
    
    // API Test Page (dev)
    Route::get('/api-test', [PageController::class, 'apiTest'])->name('api.test');
    
    // Booking (form removed; clients book from client dashboard)
    Route::get('/book-service', function () {
        return redirect('/client/dashboard');
    });
    Route::post('/bookings', [BookingController::class, 'store']);
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    
    // Account Deletion & Data Export (GDPR Compliance)
    Route::get('/account/delete', [\App\Http\Controllers\AccountDeletionController::class, 'show'])->name('account.delete');
    Route::post('/account/delete', [\App\Http\Controllers\AccountDeletionController::class, 'requestDeletion'])->name('account.delete.confirm');
    Route::get('/account/export-data', [\App\Http\Controllers\AccountDeletionController::class, 'exportData'])->name('account.export');
    
    // Legacy: available-clients Blade page removed; caregivers use dashboard "Job Listings" section
    Route::get('/available-clients', function () {
        return redirect('/caregiver/dashboard-vue');
    });
    
    // Avatar Upload
    Route::post('/api/user/{id}/avatar', [AvatarController::class, 'upload']);
});

// ============================================
// AUTHENTICATED API ROUTES
// ============================================

Route::prefix('api')->middleware(['web', 'auth'])->group(function () {
    
    // Application Status
    Route::get('/caregiver/application-status', [ApplicationStatusController::class, 'caregiverStatus']);
    Route::get('/marketing/application-status', [ApplicationStatusController::class, 'marketingStatus']);
    Route::get('/training/application-status', [ApplicationStatusController::class, 'trainingStatus']);
    
    // Bookings
    Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::get('/bookings/{booking}/json', function (\App\Models\Booking $booking) {
        return response()->json(['booking' => $booking->load(['client', 'assignedCaregiver'])]);
    });
    Route::put('/bookings/{booking}', [BookingController::class, 'update']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);
    Route::get('/bookings/{id}/assignments', [BookingController::class, 'getAssignments']);
    Route::get('/bookings-with-assignments', [BookingController::class, 'indexWithAssignments']);
    Route::post('/bookings/{id}/approve', [BookingController::class, 'approve']);
    Route::post('/bookings/{id}/add-payment-data', [PricingController::class, 'addPaymentData']);
    
    // Featured Posts (client dashboard widget)
    Route::get('/featured-posts', [FeaturedPostController::class, 'index']);

    // Client Data
    Route::get('/client/stats', [\App\Http\Controllers\DashboardController::class, 'clientStats']);
    Route::get('/client/available-years', [\App\Http\Controllers\DashboardController::class, 'clientAvailableYears']);
    Route::get('/client/spending-data', [\App\Http\Controllers\DashboardController::class, 'clientSpendingData']);
    Route::get('/client/top-caregivers', [CaregiverDataController::class, 'topCaregivers']);
    
    // Recurring Bookings
    Route::prefix('client/recurring')->group(function() {
        Route::get('/', [\App\Http\Controllers\RecurringBookingController::class, 'index']);
        Route::get('/upcoming-renewals', [\App\Http\Controllers\RecurringBookingController::class, 'getUpcomingRenewals']);
        Route::get('/{bookingId}', [\App\Http\Controllers\RecurringBookingController::class, 'show']);
        Route::post('/{bookingId}/enable', [\App\Http\Controllers\RecurringBookingController::class, 'enableAutoPay']);
        Route::post('/{bookingId}/cancel', [\App\Http\Controllers\RecurringBookingController::class, 'cancelRecurring']);
        Route::post('/{bookingId}/pause', [\App\Http\Controllers\RecurringBookingController::class, 'pauseRecurring']);
        Route::post('/{bookingId}/resume', [\App\Http\Controllers\RecurringBookingController::class, 'resumeRecurring']);
        Route::get('/{bookingId}/next-charge', [\App\Http\Controllers\RecurringBookingController::class, 'getNextChargeDate']);
    });
    
    // Admin Recurring Bookings
    Route::get('/admin/recurring-bookings', [\App\Http\Controllers\RecurringBookingController::class, 'adminIndex']);
    
    // Caregiver Data
    Route::get('/caregivers', [\App\Http\Controllers\DashboardController::class, 'caregivers']);
    Route::get('/housekeepers', [\App\Http\Controllers\DashboardController::class, 'housekeepers']);
    Route::get('/caregiver/{id}/stats', [\App\Http\Controllers\DashboardController::class, 'caregiverStats']);
    Route::get('/caregiver/{id}/earnings-report', [CaregiverController::class, 'getEarningsReport']);
    Route::post('/caregiver/earnings-report-pdf', [CaregiverController::class, 'generateEarningsReportPdf']);
    Route::get('/available-clients', [CaregiverController::class, 'getAvailableClients']);
    Route::post('/apply-client/{id}', [CaregiverController::class, 'applyForClient']);
    Route::get('/caregiver/payment-data', [CaregiverDataController::class, 'paymentData']);
    Route::get('/caregiver/past-bookings', [CaregiverDataController::class, 'pastBookings']);
    Route::get('/caregiver/schedule-events', [CaregiverDataController::class, 'scheduleEvents']);
    
    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'delete']);
    Route::delete('/notifications', [\App\Http\Controllers\NotificationController::class, 'deleteAll']);
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
    Route::post('/profile/change-password', [UserProfileController::class, 'changePassword']);

    // Time Tracking
    Route::post('/time-tracking/clock-in', [\App\Http\Controllers\TimeTrackingController::class, 'clockIn']);
    Route::post('/time-tracking/clock-out', [\App\Http\Controllers\TimeTrackingController::class, 'clockOut']);
    Route::get('/time-tracking/current-session/{caregiverId}', [\App\Http\Controllers\TimeTrackingController::class, 'getCurrentSession']);
    Route::get('/time-tracking/weekly-history/{caregiverId}', [\App\Http\Controllers\TimeTrackingController::class, 'getWeeklyHistory']);
    Route::get('/time-tracking/today-summary/{caregiverId}', [\App\Http\Controllers\TimeTrackingController::class, 'getTodaySummary']);
    
    // Housekeeper Time Tracking
    Route::get('/time-tracking/housekeeper/current-session/{housekeeperId}', [\App\Http\Controllers\TimeTrackingController::class, 'getHousekeeperCurrentSession']);
    Route::get('/time-tracking/housekeeper/weekly-history/{housekeeperId}', [\App\Http\Controllers\TimeTrackingController::class, 'getHousekeeperWeeklyHistory']);
    Route::get('/time-tracking/housekeeper/today-summary/{housekeeperId}', [\App\Http\Controllers\TimeTrackingController::class, 'getHousekeeperTodaySummary']);
    
    // Referral Codes
    Route::get('/referral-codes/my-code', [\App\Http\Controllers\ReferralCodeController::class, 'getMyCode']);
    Route::post('/referral-codes/validate', [\App\Http\Controllers\ReferralCodeController::class, 'validateCode']);
    
    // Marketing stats (same auth as my-code so session is always valid; returns only current user's stats)
    Route::get('/marketing/stats', [\App\Http\Controllers\MarketingStaffController::class, 'stats']);
    
    // Training Centers dropdown: handled by api.php GET /api/training-centers (no auth) so caregiver/housekeeper profile always gets the list
    // Receipts
    Route::get('/receipts/payment/{bookingId}', [\App\Http\Controllers\ReceiptController::class, 'generatePaymentReceipt'])->name('receipt.payment');
    Route::get('/receipts/payment/{bookingId}/download', [\App\Http\Controllers\ReceiptController::class, 'downloadPaymentReceipt'])->name('receipt.payment.download');
    Route::get('/receipts/{bookingId}', [\App\Http\Controllers\ReceiptController::class, 'generate']);
    Route::get('/receipts/{bookingId}/download', [\App\Http\Controllers\ReceiptController::class, 'download']);
    
    // Pricing
    Route::get('/pricing/breakdown', [PricingController::class, 'breakdown']);
    Route::get('/pricing/rates', [PricingController::class, 'rates']);
    
    // Payment Status Update
    Route::post('/bookings/update-payment-status', [PaymentPageController::class, 'updatePaymentStatus']);
    Route::get('/client/payment-methods', [\App\Http\Controllers\ClientPaymentController::class, 'getPaymentMethods']);
    
    // ==========================================
    // CONTRACTOR PAYROLL & TAX ROUTES
    // ==========================================
    
    // Tax Documents (Contractors - W9 submission)
    Route::post('/tax/w9', [\App\Http\Controllers\TaxDocumentController::class, 'submitW9']);
    Route::get('/tax/w9-status', [\App\Http\Controllers\TaxDocumentController::class, 'getW9Status']);
    Route::get('/tax/onboarding-status', [\App\Http\Controllers\TaxDocumentController::class, 'getOnboardingStatus']);
    Route::post('/tax/onboarding-step', [\App\Http\Controllers\TaxDocumentController::class, 'updateOnboardingStep']);
    
    // Payroll (Contractors)
    Route::get('/payroll/schedule', [\App\Http\Controllers\PayrollController::class, 'getPayoutSchedule']);
    Route::post('/payroll/preferences', [\App\Http\Controllers\PayrollController::class, 'updatePayoutPreferences']);
    Route::get('/payroll/history', [\App\Http\Controllers\PayrollController::class, 'getPayoutHistory']);
    Route::get('/payroll/onboarding', [\App\Http\Controllers\PayrollController::class, 'getOnboardingStatus']);
    
    // Tax Estimates (Contractors)
    Route::get('/tax/estimate', [\App\Http\Controllers\PayrollController::class, 'getTaxEstimate']);
    Route::get('/tax/compliance', [\App\Http\Controllers\PayrollController::class, 'getComplianceStatus']);
    
    // 1099 Forms (Contractors)
    Route::get('/tax/1099', [\App\Http\Controllers\Form1099Controller::class, 'getMyForms']);
    Route::get('/tax/1099/{formId}/download', [\App\Http\Controllers\Form1099Controller::class, 'download']);
});

// ============================================
// ADMIN API ROUTES
// ============================================

Route::prefix('api')->middleware(['web', 'auth', 'user.type:admin'])->group(function () {
    Route::get('/admin/csrf-token', fn () => response()->json(['token' => csrf_token()]));
    Route::get('/admin/stats', [\App\Http\Controllers\DashboardController::class, 'adminStats']);
    Route::get('/admin/users', [\App\Http\Controllers\DashboardController::class, 'adminUsers']);
    
    // User Management (UserAdminController)
    Route::post('/admin/users', [UserAdminController::class, 'storeUser']);
    Route::put('/admin/users/{id}', [UserAdminController::class, 'updateUser']);
    Route::put('/admin/users/{id}/status', [UserAdminController::class, 'updateUserStatus']);
    Route::put('/admin/caregivers/{id}/status', [UserAdminController::class, 'updateCaregiverStatus']);
    Route::delete('/admin/users/{id}', [UserAdminController::class, 'deleteUser']);
    Route::get('/admin/applications', [UserAdminController::class, 'getApplications']);
    Route::post('/admin/applications/{id}/approve', [UserAdminController::class, 'approveApplication']);
    Route::post('/admin/applications/{id}/reject', [UserAdminController::class, 'rejectApplication']);
    Route::post('/admin/applications/{id}/unapprove', [UserAdminController::class, 'unapproveApplication']);
    
    // Staff Management (StaffAdminController)
    Route::get('/admin/marketing-staff', [StaffAdminController::class, 'getMarketingStaff']);
    Route::post('/admin/marketing-staff', [StaffAdminController::class, 'storeMarketingStaff']);
    Route::put('/admin/marketing-staff/{id}', [StaffAdminController::class, 'updateMarketingStaff']);
    Route::delete('/admin/marketing-staff/{id}', [StaffAdminController::class, 'deleteMarketingStaff']);
    
    Route::get('/admin/admin-staff', [StaffAdminController::class, 'getAdminStaff']);
    Route::post('/admin/admin-staff', [StaffAdminController::class, 'storeAdminStaff']);
    Route::put('/admin/admin-staff/{id}', [StaffAdminController::class, 'updateAdminStaff']);
    Route::delete('/admin/admin-staff/{id}', [StaffAdminController::class, 'deleteAdminStaff']);
    Route::get('/admin/admin-staff/permissions', [StaffAdminController::class, 'getAdminStaffPermissions']);
    
    // Training Center Management (StaffAdminController)
    Route::get('/admin/training-centers', [StaffAdminController::class, 'getTrainingCenters']);
    Route::post('/admin/training-centers', [StaffAdminController::class, 'storeTrainingCenter']);
    Route::put('/admin/training-centers/{id}', [StaffAdminController::class, 'updateTrainingCenter']);
    Route::delete('/admin/training-centers/{id}', [StaffAdminController::class, 'deleteTrainingCenter']);
    Route::get('/admin/training-centers/{id}/caregivers', [StaffAdminController::class, 'getTrainingCenterCaregivers']);
    
    // Password Resets (UserAdminController)
    Route::get('/admin/password-resets', [UserAdminController::class, 'getPasswordResets']);
    Route::post('/admin/password-resets/{id}/process', [UserAdminController::class, 'processPasswordReset']);
    Route::delete('/admin/password-resets/{id}', [UserAdminController::class, 'deletePasswordReset']);
    
    // Announcements (AdminController - kept in main controller)
    Route::get('/admin/announcements', [AdminController::class, 'getAnnouncements']);
    Route::post('/admin/announcements', [AdminController::class, 'storeAnnouncement']);
    Route::post('/admin/test-email', [AdminController::class, 'sendTestEmail']);

    // Featured Posts (admin: upload, edit, delete)
    Route::get('/admin/featured-posts', [FeaturedPostController::class, 'adminIndex']);
    Route::post('/admin/featured-posts', [FeaturedPostController::class, 'store']);
    Route::put('/admin/featured-posts/{id}', [FeaturedPostController::class, 'update']);
    Route::delete('/admin/featured-posts/{id}', [FeaturedPostController::class, 'destroy']);
    
    // Booking Assignments (BookingAdminController)
    Route::post('/bookings/{id}/assign', [BookingAdminController::class, 'assignCaregivers']);
    Route::post('/bookings/{id}/assign-housekeepers', [BookingAdminController::class, 'assignHousekeepers']);
    Route::post('/bookings/{id}/unassign', [BookingAdminController::class, 'unassignCaregiver']);
    Route::post('/bookings/{id}/unassign-housekeeper', [BookingAdminController::class, 'unassignHousekeeper']);
    Route::get('/bookings/{id}/housekeeper/{housekeeperId}/schedule', [BookingAdminController::class, 'getHousekeeperSchedule']);
    Route::post('/bookings/{id}/housekeeper/{housekeeperId}/schedule', [BookingAdminController::class, 'updateHousekeeperSchedule']);
    
    // Payments & Analytics (ReportAdminController)
    Route::get('/admin/payment-stats', [ReportAdminController::class, 'getPaymentStats']);
    Route::get('/admin/transactions', [ReportAdminController::class, 'getTransactions']);
    Route::get('/admin/client-payments', [ReportAdminController::class, 'getClientPayments']);
    Route::get('/admin/caregiver-salaries', [ReportAdminController::class, 'getCaregiverSalaries']);
    Route::get('/admin/housekeeper-salaries', [ReportAdminController::class, 'getHousekeeperSalaries']);
    Route::post('/admin/pay-caregiver', [ReportAdminController::class, 'payCaregiver']);
    Route::post('/admin/pay-housekeeper', [ReportAdminController::class, 'payHousekeeper']);
    
    // Commissions (ReportAdminController)
    Route::get('/admin/marketing-commissions', [ReportAdminController::class, 'getMarketingCommissions']);
    Route::post('/admin/pay-marketing-commission/{id}', [ReportAdminController::class, 'payMarketingCommission']);
    Route::get('/admin/training-commissions', [ReportAdminController::class, 'getTrainingCommissions']);
    Route::post('/admin/pay-training-commission/{id}', [ReportAdminController::class, 'payTrainingCommission']);
    
    // Payment Monitoring
    Route::get('/admin/money-flow-dashboard', [\App\Http\Controllers\PaymentMonitoringController::class, 'getMoneyFlowDashboard']);
    Route::get('/admin/verify-payout/{id}', [\App\Http\Controllers\PaymentMonitoringController::class, 'verifyPayoutDetails']);
    Route::get('/admin/reconciliation-report', [\App\Http\Controllers\PaymentMonitoringController::class, 'getReconciliationReport']);
    
    // Reports (ReportAdminController)
    Route::get('/admin/financial-report/pdf', [\App\Http\Controllers\AdminReportController::class, 'generateFinancialReport']);
    Route::get('/admin/transactions/export/pdf', [\App\Http\Controllers\AdminReportController::class, 'exportTransactionsPdf']);
    Route::get('/admin/top-performers', [ReportAdminController::class, 'getTopPerformers']);
    Route::get('/admin/recent-activity', [ReportAdminController::class, 'getRecentActivity']);
    Route::get('/admin/bookings', [BookingAdminController::class, 'getAllBookings']);
    Route::get('/admin/booking-stats', [BookingAdminController::class, 'getBookingStats']);
    Route::get('/admin/time-tracking', [\App\Http\Controllers\TimeTrackingController::class, 'getAdminTimeTracking']);
    Route::put('/admin/time-tracking/{id}', [\App\Http\Controllers\TimeTrackingController::class, 'update']);
    Route::delete('/admin/time-tracking/{id}', [\App\Http\Controllers\TimeTrackingController::class, 'destroy']);
    
    // Referral Codes (admin)
    Route::get('/referral-codes', [\App\Http\Controllers\ReferralCodeController::class, 'index']);
    Route::post('/referral-codes', [\App\Http\Controllers\ReferralCodeController::class, 'store']);
    Route::put('/referral-codes/{id}', [\App\Http\Controllers\ReferralCodeController::class, 'update']);
    Route::get('/referral-codes/commission-stats', [\App\Http\Controllers\ReferralCodeController::class, 'getCommissionStats']);
    
    // ==========================================
    // PAYROLL & TAX MANAGEMENT (Admin)
    // ==========================================
    
    // Tax Documents (Admin)
    Route::get('/admin/tax-documents', [\App\Http\Controllers\TaxDocumentController::class, 'adminGetTaxDocuments']);
    Route::post('/admin/tax-documents/{id}/verify', [\App\Http\Controllers\TaxDocumentController::class, 'adminVerifyDocument']);
    Route::post('/admin/tax-documents/{id}/reject', [\App\Http\Controllers\TaxDocumentController::class, 'adminRejectDocument']);
    Route::get('/admin/tax-documents/{id}/download', [\App\Http\Controllers\TaxDocumentController::class, 'downloadDocument']);
    Route::get('/admin/contractor-tax-summary', [\App\Http\Controllers\TaxDocumentController::class, 'adminGetContractorTaxSummary']);
    
    // Payroll Settings & Management (Admin)
    Route::get('/admin/payroll/settings', [\App\Http\Controllers\PayrollController::class, 'adminGetPayoutSettings']);
    Route::post('/admin/payroll/settings', [\App\Http\Controllers\PayrollController::class, 'adminUpdatePayoutSettings']);
    Route::get('/admin/payroll/scheduled-payouts', [\App\Http\Controllers\PayrollController::class, 'adminGetScheduledPayouts']);
    Route::post('/admin/payroll/trigger', [\App\Http\Controllers\PayrollController::class, 'adminTriggerPayout']);
    Route::get('/admin/payroll/{payoutId}', [\App\Http\Controllers\PayrollController::class, 'adminGetPayoutDetails']);
    Route::get('/admin/payroll/pending-contractors', [\App\Http\Controllers\PayrollController::class, 'adminGetContractorsPendingPayout']);
    
    // Compliance (Admin)
    Route::post('/admin/compliance/run-checks', [\App\Http\Controllers\PayrollController::class, 'adminRunComplianceChecks']);
    Route::get('/admin/compliance/summary', [\App\Http\Controllers\PayrollController::class, 'adminGetComplianceSummary']);
    
    // 1099 Forms (Admin)
    Route::get('/admin/1099/summary', [\App\Http\Controllers\Form1099Controller::class, 'adminGetSummary']);
    Route::get('/admin/1099/forms', [\App\Http\Controllers\Form1099Controller::class, 'adminGetForms']);
    Route::post('/admin/1099/generate', [\App\Http\Controllers\Form1099Controller::class, 'adminGenerateForms']);
    Route::get('/admin/1099/preview/{userId}', [\App\Http\Controllers\Form1099Controller::class, 'adminPreviewForm']);
    Route::post('/admin/1099/{formId}/finalize', [\App\Http\Controllers\Form1099Controller::class, 'adminFinalizeForm']);
    Route::post('/admin/1099/{formId}/send', [\App\Http\Controllers\Form1099Controller::class, 'adminSendForm']);
    Route::post('/admin/1099/bulk-finalize', [\App\Http\Controllers\Form1099Controller::class, 'adminBulkFinalize']);
    Route::post('/admin/1099/bulk-send', [\App\Http\Controllers\Form1099Controller::class, 'adminBulkSend']);
    Route::get('/admin/1099/{formId}/download', [\App\Http\Controllers\Form1099Controller::class, 'adminDownloadForm']);
    Route::post('/admin/1099/{formId}/correction', [\App\Http\Controllers\Form1099Controller::class, 'adminCreateCorrection']);
    Route::get('/admin/1099/contractors-requiring', [\App\Http\Controllers\Form1099Controller::class, 'adminGetContractorsRequiring1099']);
});

// ============================================
// MARKETING STAFF API ROUTES (other marketing-only routes can go here)
// ============================================
// Note: /api/marketing/stats is under the general auth group above so it always receives the same session as /api/referral-codes/my-code

// ============================================
// TRAINING CENTER API ROUTES
// ============================================

Route::prefix('api')->middleware(['web', 'auth', 'user.type:training,training_center'])->group(function () {
    Route::get('/training/pending-caregivers', [TrainingCenterController::class, 'pendingCaregivers']);
    Route::post('/training/caregivers/{id}/approve', [TrainingCenterController::class, 'approveCaregiver']);
    Route::post('/training/caregivers/{id}/reject', [TrainingCenterController::class, 'rejectCaregiver']);
    Route::get('/training/stats', [TrainingCenterController::class, 'stats']);
});

// ============================================
// DEVELOPMENT-ONLY ROUTES
// ============================================

if (app()->environment('local', 'development')) {
    Route::get('/api/update-booking-status', [DebugController::class, 'updateBookingStatus']);
    Route::get('/reseed-bookings', [DebugController::class, 'reseedBookings']);
    Route::get('/migrate-names', [DebugController::class, 'migrateNames']);
    Route::get('/migrate-status', [DebugController::class, 'migrateStatus']);
    Route::get('/debug/client/recurring', [DebugController::class, 'clientRecurring'])->middleware('auth');
}

// ============================================
// STRIPE PAYMENT INTEGRATION
// ============================================

Route::middleware(['auth'])->prefix('api/stripe')->group(function () {
    // Client Payment Methods
    Route::post('/create-payment-intent', [\App\Http\Controllers\ClientPaymentController::class, 'createPaymentIntent']);
    Route::post('/create-setup-intent', [\App\Http\Controllers\ClientPaymentController::class, 'createSetupIntent']);
    Route::post('/attach-payment-method', [\App\Http\Controllers\ClientPaymentController::class, 'attachPaymentMethod']);
    Route::post('/charge-saved-method', [\App\Http\Controllers\ClientPaymentController::class, 'chargeSavedMethod']);
    Route::get('/payment-methods', [\App\Http\Controllers\ClientPaymentController::class, 'listPaymentMethods']);
    Route::delete('/delete-payment-method', [\App\Http\Controllers\ClientPaymentController::class, 'deletePaymentMethod']);
    
    // Legacy Payment Methods
    Route::get('/setup-intent', [\App\Http\Controllers\StripeController::class, 'createSetupIntent']);
    Route::post('/setup-intent', [\App\Http\Controllers\StripeController::class, 'processPaymentWithMethod']);
    Route::post('/save-payment-method', [\App\Http\Controllers\StripeController::class, 'savePaymentMethod']);
    
    // Caregiver Bank Connection
    Route::post('/create-onboarding-link', [\App\Http\Controllers\StripeController::class, 'createOnboardingLink']);
    Route::post('/create-account-session', [\App\Http\Controllers\StripeController::class, 'createAccountSession']);
    Route::post('/connect-bank-account', [\App\Http\Controllers\StripeController::class, 'connectBankAccount']);
    Route::post('/connect-payout-method', [\App\Http\Controllers\StripeController::class, 'connectPayoutMethod']);
    Route::get('/connection-status', [\App\Http\Controllers\StripeController::class, 'getConnectionStatus']);
    
    // Housekeeper Bank Connection
    Route::post('/housekeeper/create-onboarding-link', [\App\Http\Controllers\StripeController::class, 'createHousekeeperOnboardingLink']);
    Route::post('/housekeeper/connect-payout-method', [\App\Http\Controllers\StripeController::class, 'connectHousekeeperPayoutMethod']);
    Route::get('/housekeeper/onboarding-status', [\App\Http\Controllers\StripeController::class, 'checkHousekeeperOnboardingStatus']);
    Route::get('/housekeeper/connection-status', [\App\Http\Controllers\StripeController::class, 'getHousekeeperConnectionStatus']);
    
    // Marketing & Training Bank Connection
    Route::post('/connect-bank-account-marketing', [\App\Http\Controllers\StripeController::class, 'connectMarketingBankAccount']);
    Route::post('/connect-bank-account-training', [\App\Http\Controllers\StripeController::class, 'connectTrainingBankAccount']);
    
    // Admin Payment Processing
    Route::post('/process-payment/{timeTrackingId}', [\App\Http\Controllers\StripeController::class, 'processPayment'])
        ->middleware('user.type:admin,adminstaff');
    Route::get('/payment-preview/{timeTrackingId}', [\App\Http\Controllers\StripeController::class, 'getPaymentPreview'])
        ->middleware('user.type:admin,adminstaff');
    Route::get('/pending-payments', [\App\Http\Controllers\StripeController::class, 'getPendingPayments'])
        ->middleware('user.type:admin,adminstaff');
    Route::post('/batch-process', [\App\Http\Controllers\StripeController::class, 'batchProcessPayments'])
        ->middleware('user.type:admin,adminstaff');
    
    // Admin Commission Payments
    Route::post('/admin/pay-marketing-commission/{userId}', [\App\Http\Controllers\StripeController::class, 'payMarketingCommission'])
        ->middleware('user.type:admin,adminstaff');
    Route::post('/admin/pay-training-commission/{userId}', [\App\Http\Controllers\StripeController::class, 'payTrainingCommission'])
        ->middleware('user.type:admin,adminstaff');
});

// Stripe Webhook (no auth required)
Route::post('/api/stripe/webhook', [\App\Http\Controllers\StripeController::class, 'webhook']);

// ============================================
// EMAIL MARKETING & TRACKING ROUTES
// ============================================

// Public tracking routes (no auth - for email opens/clicks)
Route::get('/email/track/open/{trackingToken}', [\App\Http\Controllers\Admin\AdminEmailController::class, 'trackOpen'])->name('email.track.open');
Route::get('/email/track/click/{trackingToken}', [\App\Http\Controllers\Admin\AdminEmailController::class, 'trackClick'])->name('email.track.click');

// Admin Email Marketing Routes
Route::prefix('api/admin/email-marketing')->middleware(['web', 'auth', 'user.type:admin'])->group(function () {
    // Dashboard & Stats
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminEmailController::class, 'getDashboardStats']);
    
    // Campaigns
    Route::get('/campaigns', [\App\Http\Controllers\Admin\AdminEmailController::class, 'getCampaigns']);
    Route::get('/campaigns/{id}', [\App\Http\Controllers\Admin\AdminEmailController::class, 'getCampaign']);
    Route::post('/campaigns', [\App\Http\Controllers\Admin\AdminEmailController::class, 'createCampaign']);
    Route::put('/campaigns/{id}', [\App\Http\Controllers\Admin\AdminEmailController::class, 'updateCampaign']);
    Route::delete('/campaigns/{id}', [\App\Http\Controllers\Admin\AdminEmailController::class, 'deleteCampaign']);
    Route::post('/campaigns/{id}/send', [\App\Http\Controllers\Admin\AdminEmailController::class, 'sendCampaign']);
    Route::get('/campaigns/{id}/analytics', [\App\Http\Controllers\Admin\AdminEmailController::class, 'getCampaignAnalytics']);
    
    // Client Targeting
    Route::get('/clients', [\App\Http\Controllers\Admin\AdminEmailController::class, 'getClients']);
    Route::get('/filter-options', [\App\Http\Controllers\Admin\AdminEmailController::class, 'getFilterOptions']);
    
    // Preview & Testing
    Route::post('/preview', [\App\Http\Controllers\Admin\AdminEmailController::class, 'previewCampaign']);
    Route::post('/test-email', [\App\Http\Controllers\Admin\AdminEmailController::class, 'sendTestEmail']);
});

// Contractor Notification Settings Routes
Route::prefix('api/contractor/notifications')->middleware(['web', 'auth'])->group(function () {
    Route::get('/settings', [\App\Http\Controllers\ContractorNotificationController::class, 'getSettings']);
    Route::post('/settings', [\App\Http\Controllers\ContractorNotificationController::class, 'updateSettings']);
});
