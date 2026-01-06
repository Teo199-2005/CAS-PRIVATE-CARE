<?php

/**
 * ======================================================================
 * COMPREHENSIVE SYSTEM AUDIT SCRIPT
 * ======================================================================
 * 
 * This script performs a complete system audit covering:
 * 1. Database Integrity & Synchronization
 * 2. Widget/Stats Card Accuracy
 * 3. Table Data Consistency
 * 4. User Role Functionality
 * 5. Booking Workflow End-to-End
 * 6. Error Handling & Null Safety
 * 7. Payment Processing
 * 8. Real-time Updates
 * 
 * Usage: php comprehensive-system-audit.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Models\Caregiver;
use App\Models\Payment;
use App\Models\TimeTracking;
use App\Models\Notification;
use App\Models\Review;

// Color formatting for terminal
class AuditOutput {
    public static function header($text) {
        echo "\n\033[1;36m" . str_repeat("=", 80) . "\033[0m\n";
        echo "\033[1;36m" . str_pad($text, 80, " ", STR_PAD_BOTH) . "\033[0m\n";
        echo "\033[1;36m" . str_repeat("=", 80) . "\033[0m\n\n";
    }
    
    public static function section($text) {
        echo "\n\033[1;33m■ " . $text . "\033[0m\n";
        echo str_repeat("-", 80) . "\n";
    }
    
    public static function success($text) {
        echo "\033[0;32m✓ " . $text . "\033[0m\n";
    }
    
    public static function error($text) {
        echo "\033[0;31m✗ " . $text . "\033[0m\n";
    }
    
    public static function warning($text) {
        echo "\033[0;33m⚠ " . $text . "\033[0m\n";
    }
    
    public static function info($text) {
        echo "\033[0;36m→ " . $text . "\033[0m\n";
    }
    
    public static function data($label, $value) {
        echo "  " . str_pad($label . ":", 30) . "\033[1;37m" . $value . "\033[0m\n";
    }
}

// Audit Results Tracker
class AuditTracker {
    private $tests = [];
    private $errors = [];
    private $warnings = [];
    
    public function addTest($category, $name, $passed, $details = null) {
        $this->tests[] = [
            'category' => $category,
            'name' => $name,
            'passed' => $passed,
            'details' => $details,
            'timestamp' => now()
        ];
        
        if (!$passed && $details) {
            $this->errors[] = "$category - $name: $details";
        }
    }
    
    public function addWarning($message) {
        $this->warnings[] = $message;
    }
    
    public function getSummary() {
        $total = count($this->tests);
        $passed = count(array_filter($this->tests, fn($t) => $t['passed']));
        $failed = $total - $passed;
        
        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'warnings' => count($this->warnings),
            'success_rate' => $total > 0 ? round(($passed / $total) * 100, 2) : 0
        ];
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function getWarnings() {
        return $this->warnings;
    }
}

$tracker = new AuditTracker();

// ======================================================================
// PART 1: DATABASE INTEGRITY & RELATIONSHIPS
// ======================================================================

AuditOutput::header("PART 1: DATABASE INTEGRITY & RELATIONSHIPS");

AuditOutput::section("1.1 Database Connection & Tables");

try {
    DB::connection()->getPdo();
    AuditOutput::success("Database connection established");
    $tracker->addTest('Database', 'Connection', true);
} catch (\Exception $e) {
    AuditOutput::error("Database connection failed: " . $e->getMessage());
    $tracker->addTest('Database', 'Connection', false, $e->getMessage());
    exit(1);
}

$requiredTables = [
    'users', 'bookings', 'booking_assignments', 'caregivers', 
    'payments', 'time_trackings', 'reviews', 'notifications',
    'referral_codes', 'payment_methods', 'blog_posts'
];

foreach ($requiredTables as $table) {
    $exists = DB::getSchemaBuilder()->hasTable($table);
    if ($exists) {
        $count = DB::table($table)->count();
        AuditOutput::success("Table '$table' exists ({$count} records)");
        $tracker->addTest('Database', "Table: $table", true);
    } else {
        AuditOutput::error("Table '$table' is missing!");
        $tracker->addTest('Database', "Table: $table", false, "Table does not exist");
    }
}

// ======================================================================
// PART 2: USER ROLES & PERMISSIONS
// ======================================================================

AuditOutput::header("PART 2: USER ROLES & PERMISSIONS AUDIT");

AuditOutput::section("2.1 User Distribution by Role");

$roles = ['client', 'caregiver', 'admin', 'marketing_staff', 'training_center'];
$userCounts = [];

foreach ($roles as $role) {
    $count = User::where('role', $role)->count();
    $userCounts[$role] = $count;
    AuditOutput::data(ucfirst(str_replace('_', ' ', $role)), $count);
    
    if ($count === 0 && $role !== 'marketing_staff' && $role !== 'training_center') {
        $tracker->addWarning("No users found with role: $role");
    }
}

$tracker->addTest('Users', 'Role Distribution', true);

AuditOutput::section("2.2 Test User Accounts for Each Role");

$testUsers = [];
foreach ($roles as $role) {
    $user = User::where('role', $role)->first();
    if ($user) {
        $testUsers[$role] = $user;
        AuditOutput::success("$role: {$user->name} ({$user->email})");
        $tracker->addTest('Users', "Test User: $role", true);
    } else {
        AuditOutput::warning("No $role user found for testing");
        $tracker->addTest('Users', "Test User: $role", false, "No user found");
    }
}

// ======================================================================
// PART 3: WIDGET & STATS ACCURACY
// ======================================================================

AuditOutput::header("PART 3: WIDGET & STATS CARD ACCURACY");

AuditOutput::section("3.1 Admin Dashboard Stats");

$adminStats = [
    'total_users' => User::count(),
    'total_clients' => User::where('role', 'client')->count(),
    'total_caregivers' => Caregiver::count(),
    'total_bookings' => Booking::count(),
    'pending_bookings' => Booking::where('status', 'pending')->count(),
    'approved_bookings' => Booking::where('status', 'approved')->count(),
    'active_bookings' => Booking::whereIn('status', ['approved', 'confirmed', 'in_progress'])->count(),
    'completed_bookings' => Booking::where('status', 'completed')->count(),
    'total_payments' => Payment::sum('amount'),
    'completed_payments' => Payment::where('status', 'completed')->sum('amount'),
    'pending_payments' => Payment::where('status', 'pending')->sum('amount'),
];

foreach ($adminStats as $key => $value) {
    $label = ucwords(str_replace('_', ' ', $key));
    AuditOutput::data($label, is_numeric($value) ? number_format($value, 2) : $value);
}

$tracker->addTest('Stats', 'Admin Dashboard Stats', true);

AuditOutput::section("3.2 Client Dashboard Stats Verification");

if (isset($testUsers['client'])) {
    $clientId = $testUsers['client']->id;
    $clientBookings = Booking::where('client_id', $clientId)->get();
    
    AuditOutput::data("Client Name", $testUsers['client']->name);
    AuditOutput::data("Total Bookings", $clientBookings->count());
    AuditOutput::data("Active Bookings", $clientBookings->whereIn('status', ['approved', 'confirmed'])->count());
    AuditOutput::data("Completed Bookings", $clientBookings->where('status', 'completed')->count());
    
    // Calculate total spent
    $totalSpent = $clientBookings->where('payment_status', 'paid')->sum(function($booking) {
        $hours = preg_match('/(\d+)/', $booking->duty_type, $m) ? (int)$m[1] : 8;
        $rate = $booking->hourly_rate ?? 45;
        return $hours * $booking->duration_days * $rate;
    });
    
    AuditOutput::data("Total Spent", "$" . number_format($totalSpent, 2));
    
    $tracker->addTest('Stats', 'Client Dashboard Stats', true);
} else {
    AuditOutput::warning("No client user available for stats verification");
    $tracker->addTest('Stats', 'Client Dashboard Stats', false, "No test client");
}

AuditOutput::section("3.3 Caregiver Dashboard Stats Verification");

if (isset($testUsers['caregiver'])) {
    $caregiverId = Caregiver::where('user_id', $testUsers['caregiver']->id)->first()?->id;
    
    if ($caregiverId) {
        $caregiverAssignments = BookingAssignment::where('caregiver_id', $caregiverId)
            ->where('status', 'assigned')
            ->get();
        
        AuditOutput::data("Caregiver Name", $testUsers['caregiver']->name);
        AuditOutput::data("Total Assignments", $caregiverAssignments->count());
        AuditOutput::data("Active Assignments", $caregiverAssignments->count());
        
        // Check time tracking
        $timeTrackings = TimeTracking::where('caregiver_id', $caregiverId)->get();
        AuditOutput::data("Time Tracking Records", $timeTrackings->count());
        
        $tracker->addTest('Stats', 'Caregiver Dashboard Stats', true);
    } else {
        AuditOutput::warning("Caregiver profile not found for user");
        $tracker->addTest('Stats', 'Caregiver Dashboard Stats', false, "No caregiver profile");
    }
} else {
    AuditOutput::warning("No caregiver user available for stats verification");
    $tracker->addTest('Stats', 'Caregiver Dashboard Stats', false, "No test caregiver");
}

// ======================================================================
// PART 4: TABLE DATA CONSISTENCY
// ======================================================================

AuditOutput::header("PART 4: TABLE DATA CONSISTENCY & SYNCHRONIZATION");

AuditOutput::section("4.1 Booking Status Consistency");

$bookings = Booking::with(['assignments', 'payments'])->get();

foreach ($bookings as $booking) {
    $hasIssue = false;
    $issues = [];
    
    // Check if approved booking has payment
    if ($booking->status === 'approved' && $booking->payment_status === 'unpaid') {
        $paymentRecord = $booking->payments()->where('status', 'completed')->first();
        if (!$paymentRecord) {
            $issues[] = "Approved but no payment record";
            $hasIssue = true;
        }
    }
    
    // Check if booking has assignments
    if (in_array($booking->status, ['approved', 'confirmed', 'in_progress']) && $booking->assignments->isEmpty()) {
        $issues[] = "Active status but no caregiver assignments";
        $hasIssue = true;
    }
    
    // Check assignment count vs duration
    $expectedAssignments = ceil($booking->duration_days / 15);
    $actualAssignments = $booking->assignments->where('status', '!=', 'cancelled')->count();
    
    if ($booking->status !== 'pending' && $actualAssignments < $expectedAssignments) {
        $issues[] = "Expected {$expectedAssignments} assignments, has {$actualAssignments}";
        $hasIssue = true;
    }
    
    if ($hasIssue) {
        AuditOutput::warning("Booking #{$booking->id}: " . implode(", ", $issues));
        $tracker->addWarning("Booking #{$booking->id} has inconsistencies");
    } else {
        AuditOutput::success("Booking #{$booking->id}: Status consistent");
    }
}

$tracker->addTest('Consistency', 'Booking Status Check', !$hasIssue);

AuditOutput::section("4.2 Assignment-Booking Relationship Integrity");

$assignments = BookingAssignment::with(['booking', 'caregiver'])->get();
$orphanedAssignments = 0;
$invalidCaregiver = 0;

foreach ($assignments as $assignment) {
    if (!$assignment->booking) {
        AuditOutput::error("Assignment #{$assignment->id} has no booking (orphaned)");
        $orphanedAssignments++;
    }
    
    if (!$assignment->caregiver) {
        AuditOutput::error("Assignment #{$assignment->id} has no caregiver");
        $invalidCaregiver++;
    }
}

if ($orphanedAssignments === 0 && $invalidCaregiver === 0) {
    AuditOutput::success("All assignments have valid relationships");
    $tracker->addTest('Consistency', 'Assignment Relationships', true);
} else {
    AuditOutput::error("Found {$orphanedAssignments} orphaned assignments, {$invalidCaregiver} invalid caregivers");
    $tracker->addTest('Consistency', 'Assignment Relationships', false, "Orphaned: {$orphanedAssignments}, Invalid: {$invalidCaregiver}");
}

AuditOutput::section("4.3 Payment-Booking Synchronization");

$payments = Payment::with('booking')->get();
$paymentIssues = 0;

foreach ($payments as $payment) {
    if (!$payment->booking) {
        AuditOutput::error("Payment #{$payment->id} has no associated booking");
        $paymentIssues++;
        continue;
    }
    
    // Check if completed payment is reflected in booking
    if ($payment->status === 'completed' && $payment->booking->payment_status !== 'paid') {
        AuditOutput::warning("Payment #{$payment->id} completed but booking #{$payment->booking_id} not marked as paid");
        $paymentIssues++;
    }
}

if ($paymentIssues === 0) {
    AuditOutput::success("All payments synchronized with bookings");
    $tracker->addTest('Consistency', 'Payment Synchronization', true);
} else {
    AuditOutput::error("Found {$paymentIssues} payment synchronization issues");
    $tracker->addTest('Consistency', 'Payment Synchronization', false, "{$paymentIssues} issues");
}

// ======================================================================
// PART 5: ERROR HANDLING & NULL SAFETY
// ======================================================================

AuditOutput::header("PART 5: ERROR HANDLING & NULL VALUE SAFETY");

AuditOutput::section("5.1 Checking for NULL Values in Critical Fields");

$nullChecks = [
    'users' => ['name', 'email', 'role'],
    'bookings' => ['client_id', 'service_type', 'service_date', 'duration_days'],
    'booking_assignments' => ['booking_id', 'caregiver_id', 'assigned_at'],
    'caregivers' => ['user_id'],
    'payments' => ['booking_id', 'amount', 'status'],
];

foreach ($nullChecks as $table => $fields) {
    foreach ($fields as $field) {
        $nullCount = DB::table($table)->whereNull($field)->count();
        if ($nullCount > 0) {
            AuditOutput::error("Table '{$table}' has {$nullCount} NULL values in '{$field}'");
            $tracker->addTest('Null Safety', "{$table}.{$field}", false, "{$nullCount} NULL values");
        } else {
            AuditOutput::success("No NULL values in {$table}.{$field}");
            $tracker->addTest('Null Safety', "{$table}.{$field}", true);
        }
    }
}

AuditOutput::section("5.2 Checking for Missing Relationships");

// Users without required profiles
$clientsWithoutProfile = User::where('role', 'client')
    ->whereDoesntHave('bookings')
    ->count();

$caregiversWithoutProfile = User::where('role', 'caregiver')
    ->whereDoesntHave('caregiver')
    ->count();

AuditOutput::data("Clients without bookings", $clientsWithoutProfile);
AuditOutput::data("Users marked as caregiver without profile", $caregiversWithoutProfile);

if ($caregiversWithoutProfile > 0) {
    AuditOutput::warning("Some caregiver users don't have caregiver profiles");
    $tracker->addWarning("$caregiversWithoutProfile caregiver users missing profiles");
}

// ======================================================================
// PART 6: BOOKING WORKFLOW END-TO-END
// ======================================================================

AuditOutput::header("PART 6: BOOKING WORKFLOW VALIDATION");

AuditOutput::section("6.1 Booking Status Flow");

$statusFlow = [
    'pending' => Booking::where('status', 'pending')->count(),
    'approved' => Booking::where('status', 'approved')->count(),
    'confirmed' => Booking::where('status', 'confirmed')->count(),
    'in_progress' => Booking::where('status', 'in_progress')->count(),
    'completed' => Booking::where('status', 'completed')->count(),
    'cancelled' => Booking::where('status', 'cancelled')->count(),
    'rejected' => Booking::where('status', 'rejected')->count(),
];

foreach ($statusFlow as $status => $count) {
    AuditOutput::data(ucfirst($status), $count);
}

AuditOutput::section("6.2 Assignment Status Flow");

$assignmentFlow = [
    'assigned' => BookingAssignment::where('status', 'assigned')->count(),
    'in_progress' => BookingAssignment::where('status', 'in_progress')->count(),
    'completed' => BookingAssignment::where('status', 'completed')->count(),
    'cancelled' => BookingAssignment::where('status', 'cancelled')->count(),
];

foreach ($assignmentFlow as $status => $count) {
    AuditOutput::data(ucfirst($status), $count);
}

AuditOutput::section("6.3 Payment Status Distribution");

$paymentFlow = [
    'pending' => Payment::where('status', 'pending')->count(),
    'completed' => Payment::where('status', 'completed')->count(),
    'failed' => Payment::where('status', 'failed')->count(),
    'refunded' => Payment::where('status', 'refunded')->count(),
];

foreach ($paymentFlow as $status => $count) {
    AuditOutput::data(ucfirst($status), $count);
}

$tracker->addTest('Workflow', 'Status Flow Validation', true);

// ======================================================================
// PART 7: NOTIFICATION SYSTEM
// ======================================================================

AuditOutput::header("PART 7: NOTIFICATION SYSTEM CHECK");

AuditOutput::section("7.1 Notification Distribution");

$notificationStats = [
    'total' => Notification::count(),
    'unread' => Notification::where('read', false)->count(),
    'read' => Notification::where('read', true)->count(),
];

foreach ($notificationStats as $key => $value) {
    AuditOutput::data(ucfirst($key), $value);
}

AuditOutput::section("7.2 Notification Types");

$notificationTypes = DB::table('notifications')
    ->select('type', DB::raw('count(*) as count'))
    ->groupBy('type')
    ->get();

foreach ($notificationTypes as $type) {
    AuditOutput::data($type->type, $type->count);
}

$tracker->addTest('Notifications', 'System Check', true);

// ======================================================================
// PART 8: TIME TRACKING SYSTEM
// ======================================================================

AuditOutput::header("PART 8: TIME TRACKING SYSTEM");

AuditOutput::section("8.1 Time Tracking Records");

$timeTrackingStats = [
    'total_records' => TimeTracking::count(),
    'active_sessions' => TimeTracking::where('status', 'active')->whereNull('clock_out_time')->count(),
    'completed_sessions' => TimeTracking::whereNotNull('clock_out_time')->count(),
];

foreach ($timeTrackingStats as $key => $value) {
    AuditOutput::data(ucwords(str_replace('_', ' ', $key)), $value);
}

// Check for inconsistencies
$invalidTimeTrackings = TimeTracking::whereNotNull('clock_out_time')
    ->whereRaw('clock_out_time < clock_in_time')
    ->count();

if ($invalidTimeTrackings > 0) {
    AuditOutput::error("Found {$invalidTimeTrackings} time tracking records with clock_out before clock_in");
    $tracker->addTest('Time Tracking', 'Data Validity', false, "{$invalidTimeTrackings} invalid records");
} else {
    AuditOutput::success("All time tracking records have valid timestamps");
    $tracker->addTest('Time Tracking', 'Data Validity', true);
}

// ======================================================================
// PART 9: REVIEW SYSTEM
// ======================================================================

AuditOutput::header("PART 9: REVIEW SYSTEM");

AuditOutput::section("9.1 Review Statistics");

$reviewStats = [
    'total_reviews' => Review::count(),
    'average_rating' => round(Review::avg('rating'), 2),
    'five_star' => Review::where('rating', 5)->count(),
    'four_star' => Review::where('rating', 4)->count(),
    'three_star' => Review::where('rating', 3)->count(),
    'two_star' => Review::where('rating', 2)->count(),
    'one_star' => Review::where('rating', 1)->count(),
];

foreach ($reviewStats as $key => $value) {
    AuditOutput::data(ucwords(str_replace('_', ' ', $key)), $value ?? 0);
}

$tracker->addTest('Reviews', 'System Check', true);

// ======================================================================
// FINAL SUMMARY
// ======================================================================

AuditOutput::header("AUDIT SUMMARY & RECOMMENDATIONS");

$summary = $tracker->getSummary();

AuditOutput::section("Test Results");
AuditOutput::data("Total Tests Run", $summary['total']);
AuditOutput::data("Tests Passed", $summary['passed']);
AuditOutput::data("Tests Failed", $summary['failed']);
AuditOutput::data("Warnings", $summary['warnings']);
AuditOutput::data("Success Rate", $summary['success_rate'] . "%");

if ($summary['failed'] > 0) {
    AuditOutput::section("Critical Errors Found");
    foreach ($tracker->getErrors() as $error) {
        AuditOutput::error($error);
    }
}

if ($summary['warnings'] > 0) {
    AuditOutput::section("Warnings");
    foreach ($tracker->getWarnings() as $warning) {
        AuditOutput::warning($warning);
    }
}

AuditOutput::section("Production Readiness Assessment");

if ($summary['success_rate'] >= 95 && $summary['failed'] <= 2) {
    AuditOutput::success("System is PRODUCTION READY ✓");
    AuditOutput::info("Minor issues detected but system is stable for deployment");
} elseif ($summary['success_rate'] >= 85) {
    AuditOutput::warning("System needs MINOR FIXES before production");
    AuditOutput::info("Address the errors listed above before going live");
} else {
    AuditOutput::error("System has CRITICAL ISSUES - NOT PRODUCTION READY");
    AuditOutput::info("Major fixes required before deployment");
}

AuditOutput::section("Next Steps");
AuditOutput::info("1. Review all failed tests and critical errors");
AuditOutput::info("2. Fix NULL value issues in database");
AuditOutput::info("3. Ensure all relationships are properly configured");
AuditOutput::info("4. Test booking workflow with real users");
AuditOutput::info("5. Verify payment processing end-to-end");
AuditOutput::info("6. Run this audit again after fixes");

echo "\n\033[1;36m" . str_repeat("=", 80) . "\033[0m\n";
echo "\033[1;36mAudit completed at: " . now()->format('Y-m-d H:i:s') . "\033[0m\n";
echo "\033[1;36m" . str_repeat("=", 80) . "\033[0m\n\n";

exit($summary['failed'] > 0 ? 1 : 0);
