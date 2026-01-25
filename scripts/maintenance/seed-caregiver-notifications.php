<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Notification;

// Check existing notifications for user 2
$count = Notification::where('user_id', 2)->count();
echo "Existing notifications for user 2: $count\n";

// Add sample notifications for caregiver (user_id = 2)
$notifications = [
    ['user_id' => 2, 'title' => 'New Assignment', 'message' => 'You have been assigned to a new client booking. Please review the details.', 'type' => 'Appointments', 'priority' => 'high', 'read' => false],
    ['user_id' => 2, 'title' => 'Schedule Reminder', 'message' => 'You have an upcoming shift tomorrow at 8:00 AM with client Demo Client.', 'type' => 'Appointments', 'priority' => 'normal', 'read' => false],
    ['user_id' => 2, 'title' => 'Payment Received', 'message' => 'Your weekly payment of $840.00 has been processed and deposited.', 'type' => 'Payments', 'priority' => 'normal', 'read' => true],
    ['user_id' => 2, 'title' => 'Profile Update Required', 'message' => 'Please update your certification documents before they expire.', 'type' => 'System', 'priority' => 'high', 'read' => false],
    ['user_id' => 2, 'title' => 'Client Feedback', 'message' => 'You received a 5-star rating from your recent client. Great job!', 'type' => 'Clients', 'priority' => 'normal', 'read' => true],
    ['user_id' => 2, 'title' => 'Availability Update', 'message' => 'Your availability schedule has been updated. You are now marked as available on weekends.', 'type' => 'System', 'priority' => 'normal', 'read' => false],
    ['user_id' => 2, 'title' => 'New Job Opportunity', 'message' => 'A new job matching your skills is available in Manhattan. Apply now!', 'type' => 'Appointments', 'priority' => 'normal', 'read' => false],
    ['user_id' => 2, 'title' => 'Earnings Summary', 'message' => 'Your total earnings this month: $3,360.00. View detailed breakdown in Earnings Report.', 'type' => 'Payments', 'priority' => 'normal', 'read' => true],
];

$created = 0;
foreach ($notifications as $data) {
    Notification::create($data);
    $created++;
}

echo "Created $created sample notifications for caregiver (user_id = 2)\n";

// Also add a notification for marketing user (user_id = 4) and admin (user_id = 1) if they don't have any
$adminCount = Notification::where('user_id', 1)->count();
if ($adminCount == 0) {
    $adminNotifications = [
        ['user_id' => 1, 'title' => 'New Client Registration', 'message' => 'A new client has registered on the platform. Review their profile.', 'type' => 'Clients', 'priority' => 'normal', 'read' => false],
        ['user_id' => 1, 'title' => 'Caregiver Application', 'message' => 'New caregiver application pending review.', 'type' => 'Caregivers', 'priority' => 'high', 'read' => false],
        ['user_id' => 1, 'title' => 'Monthly Report Ready', 'message' => 'The monthly analytics report is now available for download.', 'type' => 'System', 'priority' => 'normal', 'read' => true],
    ];
    foreach ($adminNotifications as $data) {
        Notification::create($data);
    }
    echo "Created 3 sample notifications for admin (user_id = 1)\n";
}

echo "Done!\n";
