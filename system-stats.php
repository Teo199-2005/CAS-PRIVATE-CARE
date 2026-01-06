<?php
/**
 * System Statistics
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "═══════════════════════════════════════\n";
echo "    CAS PRIVATE CARE - SYSTEM STATS    \n";
echo "═══════════════════════════════════════\n\n";

echo "👥 Users:\n";
echo "  Clients: " . App\Models\User::where('user_type', 'client')->count() . "\n";
echo "  Caregivers: " . App\Models\User::where('user_type', 'caregiver')->count() . "\n";
try {
    echo "  Admins: " . App\Models\User::where('is_admin', 1)->count() . "\n";
} catch (Exception $e) {
    echo "  Admins: Check user_type='admin' or role column\n";
}
echo "  Marketing: " . App\Models\User::where('user_type', 'marketing')->count() . "\n\n";

echo "📅 Bookings:\n";
echo "  Total: " . App\Models\Booking::count() . "\n";
echo "  Pending: " . App\Models\Booking::where('status', 'pending')->count() . "\n";
echo "  Approved: " . App\Models\Booking::where('status', 'approved')->count() . "\n";
echo "  Completed: " . App\Models\Booking::where('status', 'completed')->count() . "\n\n";

echo "💰 Payments:\n";
echo "  Total Payments: " . App\Models\Payment::count() . "\n";
echo "  Completed: " . App\Models\Payment::where('status', 'completed')->count() . "\n";
echo "  Total Amount: $" . number_format(App\Models\Payment::where('status', 'completed')->sum('amount'), 2) . "\n\n";

echo "⏰ Time Tracking:\n";
echo "  Total Entries: " . App\Models\TimeTracking::count() . "\n";
echo "  Total Hours: " . number_format(App\Models\TimeTracking::sum('hours_worked'), 2) . " hrs\n";
echo "  Total Earned: $" . number_format(App\Models\TimeTracking::sum('caregiver_earnings'), 2) . "\n";
echo "  Paid Out: $" . number_format(App\Models\TimeTracking::where('payment_status', 'paid')->sum('caregiver_earnings'), 2) . "\n\n";

echo "⭐ Reviews:\n";
echo "  Total Reviews: " . App\Models\Review::count() . "\n";
$avg = App\Models\Review::avg('rating');
echo "  Average Rating: " . ($avg ? number_format($avg, 2) : '0') . "/5\n\n";

// Show actual user accounts
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "EXISTING ACCOUNTS:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$clients = App\Models\User::where('user_type', 'client')->get();
foreach($clients as $user) {
    echo "Client: {$user->name} ({$user->email})\n";
}

$caregivers = App\Models\User::where('user_type', 'caregiver')->get();
foreach($caregivers as $user) {
    echo "Caregiver: {$user->name} ({$user->email}) - Status: {$user->status}\n";
}

try {
    $admins = App\Models\User::where('is_admin', 1)->get();
    foreach($admins as $user) {
        echo "Admin: {$user->name} ({$user->email})\n";
    }
} catch (Exception $e) {
    // No is_admin column, check for admin user_type
    $admins = App\Models\User::where('user_type', 'admin')->get();
    foreach($admins as $user) {
        echo "Admin: {$user->name} ({$user->email})\n";
    }
}

echo "\n";
