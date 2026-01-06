<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING API ENDPOINT: /api/available-clients ===\n\n";

// Simulate the API call
$controller = new \App\Http\Controllers\CaregiverController();
$request = new \Illuminate\Http\Request();

try {
    $response = $controller->getAvailableClients($request);
    $data = json_decode($response->getContent(), true);
    
    echo "✅ API Response:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    
    if (empty($data)) {
        echo "✅ CORRECT: No available bookings (all fully staffed)\n";
    } else {
        echo "📊 Available Bookings:\n";
        foreach ($data as $job) {
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "Booking ID: {$job['bookingId']}\n";
            echo "Client: {$job['clientName']}\n";
            echo "Duty Type: {$job['dutyType']}\n";
            echo "Hours/Day: {$job['hoursPerDay']}\n";
            echo "Caregivers Needed: {$job['caregiversNeeded']}\n";
            echo "Currently Assigned: {$job['assignedCount']}\n";
            echo "Spots Remaining: {$job['spotsRemaining']}\n";
            echo "Display Text: \"{$job['spotsRemaining']} of {$job['caregiversNeeded']} spots open\"\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

?>
