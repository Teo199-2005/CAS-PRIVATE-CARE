<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Caregiver;
use App\Models\BookingAssignment;

echo "=== Checking Caregiver Contact Info ===\n\n";

// Get all caregivers with their user info
$caregivers = Caregiver::with('user')->get();

foreach ($caregivers as $cg) {
    echo "Caregiver ID: {$cg->id}\n";
    echo "  User ID: " . ($cg->user->id ?? 'N/A') . "\n";
    echo "  Name: " . ($cg->user->name ?? 'N/A') . "\n";
    echo "  Email: " . ($cg->user->email ?? 'N/A') . "\n";
    echo "  User Phone: " . ($cg->user->phone ?? 'N/A') . "\n";
    echo "  CG Phone: " . ($cg->phone ?? 'N/A') . "\n";
    echo "\n";
}

echo "=== Checking Booking Assignments ===\n\n";
$assignments = BookingAssignment::with(['caregiver.user', 'booking'])->get();
foreach ($assignments as $a) {
    echo "Assignment ID: {$a->id}\n";
    echo "  Booking ID: {$a->booking_id}\n";
    echo "  Caregiver: " . ($a->caregiver->user->name ?? 'N/A') . "\n";
    echo "  Email: " . ($a->caregiver->user->email ?? 'N/A') . "\n";
    echo "  Phone: " . ($a->caregiver->user->phone ?? $a->caregiver->phone ?? 'N/A') . "\n";
    echo "\n";
}
