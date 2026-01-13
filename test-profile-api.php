<?php
/**
 * Test Profile API for Housekeeper
 */
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

// Test with user ID 16 (the housekeeper)
$userId = 16;
$user = User::find($userId);

echo "Testing user ID: $userId\n";
echo "User type: " . $user->user_type . "\n";
echo "Has housekeeper: " . ($user->housekeeper ? 'Yes, ID=' . $user->housekeeper->id : 'No') . "\n";
echo "Has caregiver: " . ($user->caregiver ? 'Yes, ID=' . $user->caregiver->id : 'No') . "\n\n";

// Simulate the getProfile logic
$caregiver = $user->caregiver;
$housekeeper = $user->housekeeper;

$housekeeperData = null;
if ($housekeeper) {
    $housekeeperData = [
        'id' => $housekeeper->id,
        'years_experience' => $housekeeper->years_experience,
        'bio' => $housekeeper->bio,
    ];
}

$caregiverData = null;
if ($caregiver) {
    $caregiverData = [
        'id' => $caregiver->id,
    ];
}

// Return housekeeper data in caregiver field for compatibility
// If user is a housekeeper, prioritize housekeeper data
$isHousekeeper = strtolower($user->user_type ?? '') === 'housekeeper';
$compatCaregiverData = $isHousekeeper ? ($housekeeperData ?? $caregiverData) : ($caregiverData ?? $housekeeperData);

$responseData = [
    'caregiver' => $compatCaregiverData,
    'housekeeper' => $housekeeperData,
];

echo "API Response would include:\n";
echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";

echo "data.caregiver.id would be: " . ($responseData['caregiver']['id'] ?? 'null') . "\n";
