<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 TESTING RECEIPT ROUTE\n";
echo "========================\n\n";

// Authenticate as client
$user = App\Models\User::find(4);
if (!$user) {
    die("❌ User ID 4 not found\n");
}

Auth::login($user);
echo "✅ Authenticated as: {$user->name}\n\n";

// Test the route
try {
    $controller = new App\Http\Controllers\ReceiptController();
    $response = $controller->generatePaymentReceipt(12);
    
    echo "✅ Receipt generated successfully!\n";
    echo "   Response type: " . get_class($response) . "\n";
    
} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    echo "❌ Booking not found: " . $e->getMessage() . "\n";
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    echo "❌ HTTP Error: " . $e->getStatusCode() . " - " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n📋 Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
