<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\NYLocationService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\BookingAssignment;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Api\UtilityApiController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\LocationController;
use App\Services\ZipCodeService;

// ============================================
// STRIPE WEBHOOK (No Auth, No Rate Limit)
// ============================================
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handleWebhook']);

// ============================================
// WEB VITALS (No Auth, for performance monitoring)
// ============================================
Route::post('/web-vitals', [\App\Http\Controllers\Api\WebVitalsController::class, 'store']);

// ============================================
// CSP REPORT (No Auth, for security monitoring)
// ============================================
Route::post('/csp-report', [\App\Http\Controllers\Api\CspReportController::class, 'store'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ============================================
// HEALTH CHECK ENDPOINTS (No Auth, for monitoring)
// ============================================
Route::prefix('health')->group(function () {
    Route::get('/ping', [\App\Http\Controllers\Api\HealthController::class, 'ping']);
    Route::get('/live', [\App\Http\Controllers\Api\HealthController::class, 'live']);
    Route::get('/ready', [\App\Http\Controllers\Api\HealthController::class, 'ready']);
    Route::get('/check', [\App\Http\Controllers\Api\HealthController::class, 'check']);
    Route::get('/version', [\App\Http\Controllers\Api\HealthController::class, 'version']);
});

// ============================================
// CLIENT ERROR LOGGING (No Auth, for error tracking)
// ============================================
Route::post('/client-errors', [\App\Http\Controllers\Api\ClientErrorController::class, 'store'])
    ->middleware(['throttle:30,1']);

// Frontend Error Logging (enhanced version)
Route::post('/errors/log', [\App\Http\Controllers\ErrorLoggingController::class, 'log'])
    ->middleware(['throttle:30,1']);
Route::get('/errors/health', [\App\Http\Controllers\ErrorLoggingController::class, 'health']);

// ============================================
// PUBLIC API ROUTES (Rate Limited - 60/min)
// ============================================
Route::middleware(['throttle:60,1'])->group(function () {

// ============================================
// Public ZIP lookup (NY-only, no guessing)
// Source of truth for all ZIP place indicators.
// ============================================
Route::get('/zipcode-lookup/{zip}', [UtilityApiController::class, 'lookupZipCode']);

// Application Status Endpoints (for checking approval status)
Route::get('/caregiver/application-status', [UtilityApiController::class, 'caregiverApplicationStatus']);
Route::get('/marketing/application-status', [UtilityApiController::class, 'marketingApplicationStatus']);
Route::get('/housekeeper/application-status', [UtilityApiController::class, 'housekeeperApplicationStatus']);
Route::get('/training/application-status', [UtilityApiController::class, 'applicationStatus']);

// Payout Method Management Routes
Route::delete('/caregiver/payout-method', [\App\Http\Controllers\StripeController::class, 'removePayoutMethod']);
Route::delete('/housekeeper/payout-method', [\App\Http\Controllers\StripeController::class, 'removeHousekeeperPayoutMethod']);
Route::delete('/marketing/payout-method', [\App\Http\Controllers\StripeController::class, 'removeMarketingPayoutMethod']);
Route::delete('/training/payout-method', [\App\Http\Controllers\StripeController::class, 'removeTrainingPayoutMethod']);
Route::get('/caregiver/payout-methods', [\App\Http\Controllers\StripeController::class, 'getPayoutMethods']);
Route::get('/housekeeper/payout-methods', [\App\Http\Controllers\StripeController::class, 'getHousekeeperPayoutMethods']);

// User Profile Routes - GET /profile and POST /profile/update are in web.php (session auth)
// so dashboards (Caregiver, Housekeeper, etc.) use session and don't get Unauthenticated.
Route::put('/user/{id}/profile', [UserProfileController::class, 'updateProfile']);
Route::post('/profile/change-password', [UserProfileController::class, 'changePassword']);

// NY Location Routes - Moved to LocationController
Route::get('/ny-locations', [LocationController::class, 'getAllLocations']);
Route::get('/ny-counties', [LocationController::class, 'getCounties']);
Route::get('/ny-cities/{county}', [LocationController::class, 'getCitiesForCounty']);

// Apply caching to stats endpoints (5 minute cache)
Route::middleware('cache.api:5')->group(function () {
    Route::get('/caregiver/{id}/stats', [\App\Http\Controllers\DashboardController::class, 'caregiverStats']);
    Route::get('/housekeeper/{id}/stats', [\App\Http\Controllers\HousekeeperController::class, 'stats']);
    Route::get('/admin/stats', [\App\Http\Controllers\DashboardController::class, 'adminStats']);
    Route::get('/admin/platform-metrics', [\App\Http\Controllers\Api\PlatformMetricsController::class, 'index']);
    Route::get('/admin/quick-caregivers', [\App\Http\Controllers\DashboardController::class, 'quickCaregivers']);
});

// Housekeeper-specific API routes
Route::get('/housekeeper/available-clients', [\App\Http\Controllers\HousekeeperController::class, 'getAvailableClients']);
Route::post('/housekeeper/apply-client/{id}', [\App\Http\Controllers\HousekeeperController::class, 'applyForClient']);
Route::get('/housekeeper/{id}/earnings', [\App\Http\Controllers\HousekeeperController::class, 'getEarningsReport']);

// Admin: Get all housekeepers
Route::get('/admin/housekeepers', [\App\Http\Controllers\Admin\UserAdminController::class, 'getHousekeepers']);
Route::get('/admin/housekeepers/{userId}', [\App\Http\Controllers\Admin\UserAdminController::class, 'getHousekeeperProfile']);

Route::post('/admin/platform-metrics/clear-cache', [\App\Http\Controllers\Api\PlatformMetricsController::class, 'clearCache']);
// Admin: get all bookings (full details)
Route::get('/admin/bookings', [\App\Http\Controllers\Admin\BookingAdminController::class, 'getAllBookings']);

// Admin: get client payments
Route::get('/admin/client-payments', [\App\Http\Controllers\Admin\ReportAdminController::class, 'getClientPayments']);

// Admin: get platform payouts (Stripe transactions)
Route::get('/admin/platform-payouts', [\App\Http\Controllers\Admin\ReportAdminController::class, 'getPlatformPayouts']);

// Admin: get company account info (Stripe Connect)
Route::get('/admin/company-account', [\App\Http\Controllers\Admin\ReportAdminController::class, 'getCompanyAccount']);

// Admin: get recent announcements
Route::get('/admin/announcements/recent', [\App\Http\Controllers\Admin\ReportAdminController::class, 'getRecentAnnouncements']);

// Admin: send announcement
Route::post('/admin/announcements/send', [\App\Http\Controllers\Admin\ReportAdminController::class, 'sendAnnouncement']);

// Booking Maintenance Mode - Public endpoint to check status
Route::get('/booking-maintenance-status', [\App\Http\Controllers\Admin\BookingAdminController::class, 'getBookingMaintenanceStatus']);

// Admin: Toggle booking maintenance mode
Route::post('/admin/booking-maintenance/toggle', [\App\Http\Controllers\Admin\BookingAdminController::class, 'toggleBookingMaintenance']);

// Get single booking details
Route::get('/bookings/{id}', [\App\Http\Controllers\BookingController::class, 'getBooking']);

// Booking payment status update
Route::post('/bookings/update-payment-status', [\App\Http\Controllers\BookingController::class, 'updatePaymentStatus']);

// ============================================
// CLIENT PAYMENT MANAGEMENT (Stripe)
// SECURITY: Strict rate limiting to prevent carding attacks
// - Payment write operations: 5 requests per minute
// - Payment read operations: 30 requests per minute
// ============================================
Route::middleware(['auth', 'throttle:5,1'])->prefix('client/payments')->group(function () {
    Route::post('/setup-intent', [\App\Http\Controllers\ClientPaymentController::class, 'createSetupIntent']);
    Route::post('/attach', [\App\Http\Controllers\ClientPaymentController::class, 'attachPaymentMethod']);
    Route::post('/detach/{pmId}', [\App\Http\Controllers\ClientPaymentController::class, 'detachPaymentMethod']);
});

Route::middleware(['auth', 'throttle:30,1'])->prefix('client/payments')->group(function () {
    Route::get('/methods', [\App\Http\Controllers\ClientPaymentController::class, 'listPaymentMethods']);
});

Route::middleware(['auth', 'throttle:5,1'])->prefix('client/subscriptions')->group(function () {
    Route::post('/', [\App\Http\Controllers\ClientPaymentController::class, 'createSubscription']);
    Route::post('/{id}/cancel', [\App\Http\Controllers\ClientPaymentController::class, 'cancelSubscription']);
});

// Caregiver assignment/unassignment
Route::post('/bookings/{id}/assign', [\App\Http\Controllers\AdminController::class, 'assignCaregivers']);
Route::post('/bookings/{id}/unassign', [\App\Http\Controllers\AdminController::class, 'unassignCaregiver']);
Route::post('/bookings/{id}/assign-housekeepers', [\App\Http\Controllers\AdminController::class, 'assignHousekeepers']);
Route::post('/bookings/{id}/unassign-housekeeper', [\App\Http\Controllers\AdminController::class, 'unassignHousekeeper']);

// Caregiver Schedule Management - refactored to CaregiverScheduleController
Route::get('/bookings/{bookingId}/caregiver/{caregiverId}/schedule', [\App\Http\Controllers\Api\CaregiverScheduleController::class, 'getSchedule']);
Route::post('/bookings/{bookingId}/caregiver/{caregiverId}/schedule', [\App\Http\Controllers\Api\CaregiverScheduleController::class, 'updateSchedule']);
Route::delete('/bookings/{bookingId}/caregiver/{caregiverId}/schedule', [\App\Http\Controllers\Api\CaregiverScheduleController::class, 'deleteSchedule']);

// My Weekly Schedule - for caregivers/housekeepers to see their own schedule
Route::get('/caregiver/{caregiverId}/weekly-schedule', [\App\Http\Controllers\Api\CaregiverScheduleController::class, 'getMyWeeklySchedule']);
Route::get('/housekeeper/{housekeeperId}/weekly-schedule', [\App\Http\Controllers\Api\CaregiverScheduleController::class, 'getHousekeeperWeeklySchedule']);

// Time Tracking PDF Report - refactored to ReportPdfController
Route::post('/reports/time-tracking-pdf', [\App\Http\Controllers\Api\ReportPdfController::class, 'timeTrackingPdf']);

// Payment Reports PDF Export (Caregiver, Marketing, Training)
// TODO: Migrate to ReportPdfController::paymentPdf when time permits
Route::post('/reports/payment-pdf', function (Request $request) {
    try {
        $data = $request->all();
        
        // Extract data with defaults
        $reportType = $data['reportType'] ?? 'Caregiver Payments';
        $period = $data['period'] ?? 'Current Month';
        $statusFilter = $data['statusFilter'] ?? 'All';
        $totalRecords = $data['totalRecords'] ?? '0';
        $totalHours = $data['totalHours'] ?? '0';
        $activeEmployees = $data['activeEmployees'] ?? '0';
        $avgRate = $data['avgRate'] ?? '0.0';
        $paymentData = $data['paymentData'] ?? [];
        $columns = $data['columns'] ?? [];
        
        // Build HTML
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CAS Private Care - ' . htmlspecialchars($reportType) . '</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10pt; line-height: 1.4; color: #000; background: #fff; padding: 30px 40px; }
        .header { border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .header-table { width: 100%; }
        .header-table td { vertical-align: top; }
        .logo-cell { width: 100px; }
        .logo-cell img { width: 90px; height: auto; }
        .company-name { font-size: 16pt; font-weight: bold; letter-spacing: 1px; }
        .company-tagline { font-size: 9pt; font-style: italic; color: #333; }
        .company-address { font-size: 8pt; color: #555; margin-top: 3px; }
        .date-cell { text-align: right; font-size: 9pt; }
        .doc-id { font-size: 7pt; color: #666; margin-top: 5px; }
        .report-title { text-align: center; margin: 20px 0; padding: 12px 0; border-top: 1px solid #000; border-bottom: 1px solid #000; }
        .report-title h1 { font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 3px; }
        .report-title .subtitle { font-size: 9pt; color: #333; }
        .report-info { margin-bottom: 15px; font-size: 9pt; }
        .report-info table { width: 100%; }
        .report-info td { padding: 2px 0; }
        .report-info .label { font-weight: bold; width: 100px; }
        .summary-section { margin-bottom: 20px; padding: 12px; border: 1px solid #000; }
        .summary-title { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #ccc; }
        .summary-table { width: 100%; }
        .summary-table td { text-align: center; padding: 8px; width: 25%; }
        .stat-value { font-size: 16pt; font-weight: bold; }
        .stat-label { font-size: 7pt; text-transform: uppercase; color: #555; }
        .data-section { margin-bottom: 20px; }
        .section-title { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 2px solid #000; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 8pt; }
        .data-table th { background-color: #e0e0e0; border: 1px solid #000; padding: 6px 4px; text-align: left; font-weight: bold; text-transform: uppercase; font-size: 7pt; }
        .data-table td { border: 1px solid #000; padding: 5px 4px; }
        .data-table tr:nth-child(even) { background-color: #f5f5f5; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .signature-section { margin-top: 25px; }
        .signature-table { width: 100%; }
        .signature-table td { width: 45%; padding-top: 35px; }
        .signature-line { border-top: 1px solid #000; padding-top: 4px; font-size: 8pt; }
        .footer { margin-top: 25px; padding-top: 12px; border-top: 2px solid #000; font-size: 7pt; }
        .footer-table { width: 100%; }
        .footer-table td { vertical-align: top; }
        .footer-left { text-align: left; width: 33%; }
        .footer-center { text-align: center; width: 34%; }
        .footer-right { text-align: right; width: 33%; }
        .confidential { text-align: center; font-size: 7pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-top: 12px; padding: 4px; border: 1px solid #000; }
    </style>
</head>
<body>';

        // Logo
        $logoPath = public_path('logo.png');
        $logoHtml = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoHtml = '<img src="data:image/png;base64,' . $logoData . '" alt="CAS Logo">';
        }

        $html .= '
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">' . $logoHtml . '</td>
                <td>
                    <div class="company-name">CAS PRIVATE CARE LLC</div>
                    <div class="company-tagline">Comfort & Support Healthcare Services</div>
                    <div class="company-address">Licensed Healthcare Provider | New York</div>
                </td>
                <td class="date-cell">
                    <strong>Report Date:</strong><br>
                    ' . date('F j, Y') . '<br>
                    ' . date('g:i A') . '
                    <div class="doc-id">Doc ID: RPT-' . date('Ymd-His') . '</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">
        <h1>' . htmlspecialchars($reportType) . '</h1>
        <div class="subtitle">Official Payment Records Documentation</div>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td class="label">Report Period:</td>
                <td>' . htmlspecialchars($period) . '</td>
                <td class="label" style="padding-left: 20px;">Status Filter:</td>
                <td>' . htmlspecialchars($statusFilter) . '</td>
            </tr>
            <tr>
                <td class="label">Generated By:</td>
                <td>System Administrator</td>
                <td class="label" style="padding-left: 20px;">Report Type:</td>
                <td>' . htmlspecialchars($reportType) . '</td>
            </tr>
        </table>
    </div>

    <div class="summary-section">
        <div class="summary-title">Executive Summary</div>
        <table class="summary-table">
            <tr>
                <td>
                    <div class="stat-value">' . htmlspecialchars($totalRecords) . '</div>
                    <div class="stat-label">Total Records</div>
                </td>
                <td>
                    <div class="stat-value">' . htmlspecialchars($totalHours) . '</div>
                    <div class="stat-label">Total Hours</div>
                </td>
                <td>
                    <div class="stat-value">' . htmlspecialchars($activeEmployees) . '</div>
                    <div class="stat-label">Active Employees</div>
                </td>
                <td>
                    <div class="stat-value">$' . htmlspecialchars($avgRate) . '</div>
                    <div class="stat-label">Avg Rate/HR</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="data-section">
        <div class="section-title">Detailed Payment Records</div>
        <table class="data-table">
            <thead>
                <tr>';

        // Add column headers
        foreach ($columns as $col) {
            $html .= '<th>' . htmlspecialchars($col) . '</th>';
        }

        $html .= '
                </tr>
            </thead>
            <tbody>';

        if (empty($paymentData)) {
            $html .= '<tr><td colspan="' . count($columns) . '" class="text-center" style="padding: 15px;">No payment records found for the selected period.</td></tr>';
        } else {
            foreach ($paymentData as $row) {
                $html .= '<tr>';
                foreach ($row as $value) {
                    $html .= '<td>' . htmlspecialchars($value) . '</td>';
                }
                $html .= '</tr>';
            }
        }

        $html .= '</tbody>
        </table>
    </div>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td><div class="signature-line">Prepared By</div></td>
                <td style="width: 10%;"></td>
                <td><div class="signature-line">Approved By</div></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    <strong>CAS Private Care LLC</strong><br>
                    &copy; ' . date('Y') . ' All Rights Reserved
                </td>
                <td class="footer-center">
                    This is an official document<br>
                    Generated: ' . date('M j, Y g:i A') . '
                </td>
                <td class="footer-right">
                    Page 1 of 1<br>
                    Ref: RPT-' . date('Ymd') . '
                </td>
            </tr>
        </table>
        <div class="confidential">Confidential - For Internal Use Only</div>
    </div>
</body>
</html>';

        // Generate PDF with DomPDF
        if (class_exists('Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="CAS-' . str_replace(' ', '-', $reportType) . '-Report-' . date('Y-m-d') . '.pdf"'
            ]);
        } else {
            return response($html, 200, [
                'Content-Type' => 'text/html'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
    }
});

// Client Analytics PDF Export
Route::post('/reports/client-analytics-pdf', function (Request $request) {
    try {
        $data = $request->all();
        
        // Extract data with defaults
        $clientName = $data['clientName'] ?? 'Client';
        $totalSpent = $data['totalSpent'] ?? 0;
        $thisMonth = $data['thisMonth'] ?? 0;
        $avgPerMonth = $data['avgPerMonth'] ?? 0;
        $totalBookings = $data['totalBookings'] ?? 0;
        $totalHours = $data['totalHours'] ?? 0;
        $activeCaregivers = $data['activeCaregivers'] ?? 0;
        $selectedYear = $data['selectedYear'] ?? date('Y');
        $period = $data['period'] ?? 'month';
        
        // Get logo
        $logoPath = public_path('logo.png');
        $logoHtml = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoHtml = '<img src="data:image/png;base64,' . $logoData . '" alt="CAS Logo">';
        }
        
        // Build HTML for PDF
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CAS Private Care - Client Analytics Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10pt; line-height: 1.4; color: #000; background: #fff; padding: 30px 40px; }
        .header { border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .header-table { width: 100%; }
        .header-table td { vertical-align: top; }
        .logo-cell { width: 100px; }
        .logo-cell img { width: 90px; height: auto; }
        .company-name { font-size: 16pt; font-weight: bold; letter-spacing: 1px; }
        .company-tagline { font-size: 9pt; font-style: italic; color: #333; }
        .company-address { font-size: 8pt; color: #555; margin-top: 3px; }
        .date-cell { text-align: right; font-size: 9pt; }
        .doc-id { font-size: 7pt; color: #666; margin-top: 5px; }
        .report-title { text-align: center; margin: 20px 0; padding: 12px 0; border-top: 1px solid #000; border-bottom: 1px solid #000; }
        .report-title h1 { font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 3px; }
        .report-title .subtitle { font-size: 9pt; color: #333; }
        .report-info { margin-bottom: 15px; font-size: 9pt; }
        .report-info table { width: 100%; }
        .report-info td { padding: 2px 0; }
        .report-info .label { font-weight: bold; width: 100px; }
        .summary-section { margin-bottom: 20px; padding: 12px; border: 1px solid #000; }
        .summary-title { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #ccc; }
        .summary-table { width: 100%; }
        .summary-table td { text-align: center; padding: 8px; width: 33%; }
        .stat-value { font-size: 16pt; font-weight: bold; }
        .stat-label { font-size: 7pt; text-transform: uppercase; color: #555; }
        .data-section { margin-bottom: 20px; }
        .section-title { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 2px solid #000; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 8pt; }
        .data-table th { background-color: #e0e0e0; border: 1px solid #000; padding: 6px 4px; text-align: left; font-weight: bold; text-transform: uppercase; font-size: 7pt; }
        .data-table td { border: 1px solid #000; padding: 5px 4px; }
        .data-table tr:nth-child(even) { background-color: #f5f5f5; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .signature-section { margin-top: 25px; }
        .signature-table { width: 100%; }
        .signature-table td { width: 45%; padding-top: 35px; }
        .signature-line { border-top: 1px solid #000; padding-top: 4px; font-size: 8pt; }
        .footer { margin-top: 25px; padding-top: 12px; border-top: 2px solid #000; font-size: 7pt; }
        .footer-table { width: 100%; }
        .footer-table td { vertical-align: top; }
        .footer-left { text-align: left; width: 33%; }
        .footer-center { text-align: center; width: 34%; }
        .footer-right { text-align: right; width: 33%; }
        .confidential { text-align: center; font-size: 7pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-top: 12px; padding: 4px; border: 1px solid #000; }
        .spending-breakdown { margin-top: 20px; }
        .spending-breakdown td { padding: 8px 12px; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">' . $logoHtml . '</td>
                <td>
                    <div class="company-name">CAS PRIVATE CARE LLC</div>
                    <div class="company-tagline">Comfort & Support Healthcare Services</div>
                    <div class="company-address">Licensed Healthcare Provider | New York</div>
                </td>
                <td class="date-cell">
                    <strong>Report Date:</strong><br>
                    ' . date('F j, Y') . '<br>
                    ' . date('g:i A') . '
                    <div class="doc-id">Doc ID: CAR-' . date('Ymd-His') . '</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">
        <h1>Client Analytics Report</h1>
        <div class="subtitle">Financial Summary & Service Statistics</div>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td class="label">Client Name:</td>
                <td>' . htmlspecialchars($clientName) . '</td>
                <td class="label" style="padding-left: 20px;">Report Period:</td>
                <td>' . htmlspecialchars($selectedYear) . '</td>
            </tr>
            <tr>
                <td class="label">Generated By:</td>
                <td>Client Portal</td>
                <td class="label" style="padding-left: 20px;">Report Type:</td>
                <td>Analytics Summary</td>
            </tr>
        </table>
    </div>

    <div class="summary-section">
        <div class="summary-title">Financial Summary</div>
        <table class="summary-table">
            <tr>
                <td>
                    <div class="stat-value">$' . number_format($totalSpent, 2) . '</div>
                    <div class="stat-label">Total Spent</div>
                </td>
                <td>
                    <div class="stat-value">$' . number_format($thisMonth, 2) . '</div>
                    <div class="stat-label">This Month</div>
                </td>
                <td>
                    <div class="stat-value">$' . number_format($avgPerMonth, 2) . '</div>
                    <div class="stat-label">Avg per Month</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="summary-section">
        <div class="summary-title">Service Statistics</div>
        <table class="summary-table">
            <tr>
                <td>
                    <div class="stat-value">' . htmlspecialchars($totalBookings) . '</div>
                    <div class="stat-label">Total Bookings</div>
                </td>
                <td>
                    <div class="stat-value">' . htmlspecialchars($totalHours) . ' hrs</div>
                    <div class="stat-label">Total Hours</div>
                </td>
                <td>
                    <div class="stat-value">' . htmlspecialchars($activeCaregivers) . '</div>
                    <div class="stat-label">Active Caregivers</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="data-section">
        <div class="section-title">Spending Breakdown</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Category</th>
                    <th style="width: 30%;" class="text-center">Value</th>
                    <th style="width: 30%;" class="text-center">Notes</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Amount Spent</td>
                    <td class="text-center font-bold">$' . number_format($totalSpent, 2) . '</td>
                    <td class="text-center">All Time</td>
                </tr>
                <tr>
                    <td>Current Month Spending</td>
                    <td class="text-center font-bold">$' . number_format($thisMonth, 2) . '</td>
                    <td class="text-center">' . date('F Y') . '</td>
                </tr>
                <tr>
                    <td>Average Monthly Spending</td>
                    <td class="text-center font-bold">$' . number_format($avgPerMonth, 2) . '</td>
                    <td class="text-center">Based on history</td>
                </tr>
                <tr>
                    <td>Total Care Hours Received</td>
                    <td class="text-center font-bold">' . htmlspecialchars($totalHours) . ' hours</td>
                    <td class="text-center">Completed services</td>
                </tr>
                <tr>
                    <td>Number of Bookings</td>
                    <td class="text-center font-bold">' . htmlspecialchars($totalBookings) . '</td>
                    <td class="text-center">All bookings</td>
                </tr>
                <tr>
                    <td>Caregivers Worked With</td>
                    <td class="text-center font-bold">' . htmlspecialchars($activeCaregivers) . '</td>
                    <td class="text-center">Unique caregivers</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td><div class="signature-line">Client Acknowledgment</div></td>
                <td style="width: 10%;"></td>
                <td><div class="signature-line">Prepared By</div></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    <strong>CAS Private Care LLC</strong><br>
                    &copy; ' . date('Y') . ' All Rights Reserved
                </td>
                <td class="footer-center">
                    This is an official document<br>
                    Generated: ' . date('M j, Y g:i A') . '
                </td>
                <td class="footer-right">
                    Page 1 of 1<br>
                    Ref: CAR-' . date('Ymd') . '
                </td>
            </tr>
        </table>
        <div class="confidential">Confidential - Client Financial Information</div>
    </div>
</body>
</html>';

        // Generate PDF with DomPDF
        if (class_exists('Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="CAS-Client-Analytics-' . date('Y-m-d') . '.pdf"'
            ]);
        } else {
            return response($html, 200, [
                'Content-Type' => 'text/html'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
    }
});

// Time Tracking History - refactored to TimeTrackingApiController
Route::get('/time-tracking/history', [\App\Http\Controllers\Api\TimeTrackingApiController::class, 'history']);

// Payment Methods API Routes
Route::prefix('payment-methods')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\PaymentMethodController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\PaymentMethodController::class, 'store']);
    Route::put('/{id}', [App\Http\Controllers\Api\PaymentMethodController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\PaymentMethodController::class, 'destroy']);
    Route::post('/{id}/set-default', [App\Http\Controllers\Api\PaymentMethodController::class, 'setDefault']);
});

// Reviews & Ratings API Routes
Route::prefix('reviews')->group(function () {
    Route::get('/', [App\Http\Controllers\ReviewController::class, 'index']); // Admin only
    Route::get('/my-reviews', [App\Http\Controllers\ReviewController::class, 'getClientReviews']);
    Route::get('/caregiver/{caregiverId}', [App\Http\Controllers\ReviewController::class, 'getCaregiverReviews']);
    Route::get('/booking/{bookingId}/can-review', [App\Http\Controllers\ReviewController::class, 'canReview']);
    Route::post('/', [App\Http\Controllers\ReviewController::class, 'store']);
    Route::put('/{id}', [App\Http\Controllers\ReviewController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\ReviewController::class, 'destroy']);
});

}); // End throttle:60,1 middleware group

// ============================================
// CRITICAL PAYMENT ROUTES (Stricter Rate Limit)
// SECURITY: 3 requests per minute for payment creation
// This prevents carding attacks and abuse
// ============================================
Route::middleware(['auth', 'throttle:3,1'])->group(function () {
    Route::post('/stripe/process-payment/{id}', [App\Http\Controllers\StripeController::class, 'processPayment']);
    Route::post('/stripe/create-payment-intent', [\App\Http\Controllers\ClientPaymentController::class, 'createPaymentIntent']);
    Route::post('/stripe/charge-saved-method', [\App\Http\Controllers\ClientPaymentController::class, 'chargeSavedMethod']);
    Route::post('/stripe/setup-intent', [App\Http\Controllers\StripeController::class, 'createSetupIntent']);
});

// Payment method management (moderate rate limit)
Route::middleware(['auth', 'throttle:10,1'])->group(function () {
    Route::get('/stripe/payment-methods', [App\Http\Controllers\StripeController::class, 'getPaymentMethods']);
    Route::delete('/stripe/payment-methods/{paymentMethodId}', [App\Http\Controllers\StripeController::class, 'deletePaymentMethod']);
    Route::post('/stripe/attach-payment-method', [App\Http\Controllers\StripeController::class, 'savePaymentMethod']);
});

// Admin booking approval - using BookingController::approve
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/admin/bookings/{id}/approve', [\App\Http\Controllers\BookingController::class, 'approve']);
});


// Admin: get all users
Route::get('/admin/users', [\App\Http\Controllers\Admin\UserAdminController::class, 'getUsers']);

// Admin: Marketing Staff Management
Route::get('/admin/marketing-staff', [\App\Http\Controllers\Admin\StaffAdminController::class, 'getMarketingStaff']);
Route::post('/admin/marketing-staff', [\App\Http\Controllers\Admin\StaffAdminController::class, 'storeMarketingStaff']);
Route::put('/admin/marketing-staff/{id}', [\App\Http\Controllers\Admin\StaffAdminController::class, 'updateMarketingStaff']);
Route::delete('/admin/marketing-staff/{id}', [\App\Http\Controllers\Admin\StaffAdminController::class, 'deleteMarketingStaff']);

// Admin: Admin Staff Management
Route::get('/admin/admin-staff', [\App\Http\Controllers\Admin\StaffAdminController::class, 'getAdminStaff']);
Route::post('/admin/admin-staff', [\App\Http\Controllers\Admin\StaffAdminController::class, 'storeAdminStaff']);
Route::put('/admin/admin-staff/{id}', [\App\Http\Controllers\Admin\StaffAdminController::class, 'updateAdminStaff']);
Route::delete('/admin/admin-staff/{id}', [\App\Http\Controllers\Admin\StaffAdminController::class, 'deleteAdminStaff']);
Route::get('/admin/admin-staff/permissions', [\App\Http\Controllers\Admin\StaffAdminController::class, 'getAdminStaffPermissions']);

// Admin: create/update/delete users (used by AdminDashboard modals)
Route::post('/admin/users', [\App\Http\Controllers\Admin\UserAdminController::class, 'storeUser']);
Route::put('/admin/users/{id}', [\App\Http\Controllers\Admin\UserAdminController::class, 'updateUser']);
Route::patch('/admin/users/{id}', [\App\Http\Controllers\Admin\UserAdminController::class, 'updateUser']);
Route::delete('/admin/users/{id}', [\App\Http\Controllers\Admin\UserAdminController::class, 'deleteUser']);

// Note: Applications routes are defined in web.php with proper auth middleware
// They are accessible at /api/admin/applications/* through the 'api' prefixed group in web.php

// Note: Password Resets routes are defined in web.php with proper auth middleware

// Admin: get training commissions
Route::get('/admin/training-commissions', [\App\Http\Controllers\Admin\ReportAdminController::class, 'getTrainingCommissions']);

// Admin: caregivers list (minimal payload) used by AdminDashboard caregivers table
Route::get('/admin/caregivers', [\App\Http\Controllers\Admin\UserAdminController::class, 'getCaregivers']);

// Admin: housekeepers list (minimal payload) used by AdminDashboard housekeepers table
Route::get('/housekeepers', [\App\Http\Controllers\DashboardController::class, 'housekeepers']);

// Admin: single caregiver full profile for the details modal
Route::get('/admin/caregivers/{userId}', [\App\Http\Controllers\Admin\UserAdminController::class, 'getCaregiverProfile']);

// Admin: Training Centers Management
Route::get('/admin/training-centers', [\App\Http\Controllers\Admin\StaffAdminController::class, 'getTrainingCenters']);
Route::post('/admin/training-centers', [\App\Http\Controllers\Admin\StaffAdminController::class, 'storeTrainingCenter']);
Route::put('/admin/training-centers/{id}', [\App\Http\Controllers\Admin\StaffAdminController::class, 'updateTrainingCenter']);
Route::delete('/admin/training-centers/{id}', [\App\Http\Controllers\Admin\StaffAdminController::class, 'deleteTrainingCenter']);

// Public list of training centers (used by caregiver & admin caregiver forms)
Route::get('/training-centers', [\App\Http\Controllers\Admin\StaffAdminController::class, 'getTrainingCenters']);
