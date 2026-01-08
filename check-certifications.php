<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Caregiver Certifications ===\n\n";

// Get the caregiver for Caregiver1@gmail.com
$user = DB::table('users')
    ->where('email', 'Caregiver1@gmail.com')
    ->first();

if (!$user) {
    echo "❌ User not found!\n";
    exit;
}

echo "User Found:\n";
echo "ID: {$user->id}\n";
echo "Name: {$user->name}\n";
echo "Email: {$user->email}\n\n";

$caregiver = DB::table('caregivers')
    ->where('user_id', $user->id)
    ->first();

if (!$caregiver) {
    echo "❌ Caregiver record not found!\n";
    exit;
}

echo "Caregiver ID: {$caregiver->id}\n";
echo "Years Experience: {$caregiver->years_experience}\n";
echo "Bio: {$caregiver->bio}\n\n";

echo "=== CERTIFICATIONS ===\n";
echo "HHA: " . ($caregiver->has_hha ? '✅ YES' : '❌ NO') . "\n";
echo "HHA Number: " . ($caregiver->hha_number ?? 'NULL') . "\n\n";

echo "CNA: " . ($caregiver->has_cna ? '✅ YES' : '❌ NO') . "\n";
echo "CNA Number: " . ($caregiver->cna_number ?? 'NULL') . "\n\n";

echo "RN: " . ($caregiver->has_rn ? '✅ YES' : '❌ NO') . "\n";
echo "RN Number: " . ($caregiver->rn_number ?? 'NULL') . "\n\n";

// Now test the API response
echo "=== Testing API Response ===\n";
$apiResponse = DB::table('caregivers')
    ->select('id', 'has_hha', 'hha_number', 'has_cna', 'cna_number', 'has_rn', 'rn_number')
    ->where('user_id', $user->id)
    ->first();

echo "API would return:\n";
echo json_encode([
    'has_hha' => (bool)$apiResponse->has_hha,
    'hha_number' => $apiResponse->hha_number,
    'has_cna' => (bool)$apiResponse->has_cna,
    'cna_number' => $apiResponse->cna_number,
    'has_rn' => (bool)$apiResponse->has_rn,
    'rn_number' => $apiResponse->rn_number
], JSON_PRETTY_PRINT);
