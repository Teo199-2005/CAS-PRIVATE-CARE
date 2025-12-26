<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;

echo "=== Testing API with client_id=31 (PAET) ===\n\n";

$controller = new DashboardController();
$request = Request::create('/api/client/stats', 'GET', ['client_id' => 31]);
$response = $controller->clientStats($request);
$data = json_decode($response->getContent(), true);

echo "Amount Due: $" . number_format($data['amount_due'] ?? 0) . "\n";
echo "Ongoing Contracts: " . ($data['ongoing_contracts'] ?? 0) . "\n";
echo "Total Bookings: " . ($data['total_bookings'] ?? 0) . "\n";
echo "Active Bookings: " . ($data['active_bookings'] ?? 0) . "\n";
echo "My Bookings Count: " . count($data['my_bookings'] ?? []) . "\n\n";

if (!empty($data['my_bookings'])) {
    echo "=== PAET's Bookings ===\n";
    foreach ($data['my_bookings'] as $b) {
        echo "#{$b['id']}: {$b['service_type']}, Status: {$b['status']}, Date: {$b['service_date']}\n";
    }
}

echo "\n=== Debug Info ===\n";
print_r($data['debug'] ?? []);
