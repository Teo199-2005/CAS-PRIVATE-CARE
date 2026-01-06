<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;

echo "=== Checking Booking #11 Schedule ===\n\n";

$b = Booking::find(11);

if ($b) {
    echo "Duty Type: " . $b->duty_type . "\n";
    echo "Recurring Service: " . ($b->recurring_service ? 'Yes' : 'No') . "\n";
    echo "Recurring Schedule: " . ($b->recurring_schedule ?? 'NULL') . "\n";
    echo "Duration Days: " . $b->duration_days . "\n";
    
    // Check all columns
    echo "\n=== All Booking Fields ===\n";
    foreach ($b->getAttributes() as $key => $value) {
        if (is_string($value) || is_numeric($value)) {
            echo "$key: $value\n";
        } elseif (is_array($value) || is_object($value)) {
            echo "$key: " . json_encode($value) . "\n";
        } else {
            echo "$key: " . var_export($value, true) . "\n";
        }
    }
}
