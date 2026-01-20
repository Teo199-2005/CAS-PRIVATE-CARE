<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Http\Controllers\DashboardController;

echo "=== Testing Admin Stats API ===\n\n";

$controller = new DashboardController();
$response = $controller->adminStats();
$data = json_decode($response->getContent(), true);

echo "=== Real Analytics Data ===\n";
echo "avg_client_spending: $" . number_format($data['avg_client_spending'] ?? 0, 2) . "\n";
echo "avg_caregiver_earnings: $" . number_format($data['avg_caregiver_earnings'] ?? 0, 2) . "\n";
echo "avg_housekeeper_earnings: $" . number_format($data['avg_housekeeper_earnings'] ?? 0, 2) . "\n";
echo "new_clients_this_week: " . ($data['new_clients_this_week'] ?? 'N/A') . "\n";
echo "available_caregivers: " . ($data['available_caregivers'] ?? 'N/A') . "\n";
echo "top_rated_caregivers: " . ($data['top_rated_caregivers'] ?? 'N/A') . "\n";
echo "total_revenue: $" . number_format($data['total_revenue'] ?? 0, 2) . "\n";
echo "total_caregiver_earnings: $" . number_format($data['total_caregiver_earnings'] ?? 0, 2) . "\n";
echo "total_housekeeper_earnings: $" . number_format($data['total_housekeeper_earnings'] ?? 0, 2) . "\n";
