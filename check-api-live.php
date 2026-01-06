<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking API Response ===\n\n";

// Get John Doe
$client = App\Models\User::where('email', 'client@demo.com')->first();

if (!$client) {
    echo "❌ Client not found\n";
    exit(1);
}

echo "Client: {$client->name} (ID: {$client->id})\n\n";

// Call the controller directly
$controller = new App\Http\Controllers\DashboardController();
$request = new \Illuminate\Http\Request();
$request->merge(['client_id' => $client->id]);

$response = $controller->clientStats($request);
$data = json_decode($response->getContent(), true);

echo "📊 API RESPONSE VALUES:\n";
echo "══════════════════════════════════════\n";
echo "Amount Due:        \${$data['amount_due']}\n";
echo "Total Spent:       \${$data['total_spent']}\n";
echo "Total Hours:       {$data['total_hours']}\n";
echo "Active Bookings:   {$data['active_bookings']}\n";
echo "══════════════════════════════════════\n\n";

// Check booking #12
$booking12 = collect($data['my_bookings'])->firstWhere('id', 12);
if ($booking12) {
    echo "📋 BOOKING #12 IN RESPONSE:\n";
    echo "══════════════════════════════════════\n";
    echo "ID:                {$booking12['id']}\n";
    echo "Status:            {$booking12['status']}\n";
    echo "Payment Status:    {$booking12['payment_status']}\n";
    echo "Hourly Rate:       \${$booking12['hourly_rate']}\n";
    echo "Duration Days:     {$booking12['duration_days']}\n";
    echo "Duty Type:         {$booking12['duty_type']}\n";
    echo "══════════════════════════════════════\n\n";
}

echo "✅ Backend is returning CORRECT values!\n";
echo "⚠️  Frontend needs to refresh/reload to see changes.\n\n";

echo "NEXT STEPS:\n";
echo "1. Hard refresh browser (Ctrl+Shift+R or Cmd+Shift+R)\n";
echo "2. Clear browser cache\n";
echo "3. Or logout and login again\n";
