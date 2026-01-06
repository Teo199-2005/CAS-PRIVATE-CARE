<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING ADMIN REPORT CONTROLLER ===\n\n";

// Check if controller exists
$controllerPath = app_path('Http/Controllers/AdminReportController.php');
if (file_exists($controllerPath)) {
    echo "✅ AdminReportController.php exists\n";
    echo "   Location: {$controllerPath}\n\n";
} else {
    echo "❌ AdminReportController.php NOT FOUND\n";
    echo "   Expected: {$controllerPath}\n\n";
    exit(1);
}

// Check if class can be loaded
try {
    $controller = new \App\Http\Controllers\AdminReportController();
    echo "✅ AdminReportController class loaded successfully\n\n";
} catch (\Exception $e) {
    echo "❌ Error loading AdminReportController:\n";
    echo "   " . $e->getMessage() . "\n\n";
    exit(1);
}

// Check if method exists
if (method_exists($controller, 'generateFinancialReport')) {
    echo "✅ generateFinancialReport method exists\n\n";
} else {
    echo "❌ generateFinancialReport method NOT FOUND\n\n";
    exit(1);
}

// Test data retrieval
echo "=== TESTING DATA RETRIEVAL ===\n\n";

use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\User;
use App\Models\Caregiver;

$payments = Payment::count();
$bookings = Booking::count();
$users = User::count();
$caregivers = Caregiver::count();

echo "Payments in database: {$payments}\n";
echo "Bookings in database: {$bookings}\n";
echo "Users in database: {$users}\n";
echo "Caregivers in database: {$caregivers}\n\n";

echo "✅ All checks passed! Controller is ready to use.\n";
echo "\n📄 Test the PDF at: /api/admin/financial-report/pdf\n";
echo "   (Must be logged in as admin)\n";
