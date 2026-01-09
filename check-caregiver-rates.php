<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Caregiver;

echo "Checking Caregiver Salary Ranges...\n";
echo "====================================\n\n";

$caregivers = Caregiver::all();

echo "Found {$caregivers->count()} caregivers\n\n";

foreach ($caregivers as $caregiver) {
    $user = $caregiver->user;
    echo "Caregiver: {$user->name}\n";
    echo "  Current Range: \${$caregiver->preferred_hourly_rate_min} - \${$caregiver->preferred_hourly_rate_max}/hr\n";
    
    // If they have the default 20-50 range, update with varied realistic ranges
    if ($caregiver->preferred_hourly_rate_min == 20.00 && $caregiver->preferred_hourly_rate_max == 50.00) {
        // Generate varied realistic ranges
        $ranges = [
            ['min' => 18, 'max' => 35],
            ['min' => 20, 'max' => 40],
            ['min' => 22, 'max' => 45],
            ['min' => 25, 'max' => 50],
            ['min' => 15, 'max' => 30],
            ['min' => 30, 'max' => 60],
        ];
        
        $range = $ranges[array_rand($ranges)];
        
        $caregiver->preferred_hourly_rate_min = $range['min'];
        $caregiver->preferred_hourly_rate_max = $range['max'];
        $caregiver->save();
        
        echo "  ✓ Updated to: \${$range['min']} - \${$range['max']}/hr\n";
    } else {
        echo "  ✓ Already has custom range\n";
    }
    
    echo "\n";
}

echo "====================================\n";
echo "Salary range check complete!\n";
