<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$columns = DB::select('SHOW COLUMNS FROM bookings');

echo "📋 BOOKINGS TABLE COLUMNS:\n\n";

foreach ($columns as $column) {
    echo sprintf("%-30s %s\n", $column->Field, $column->Type);
}

// Check specifically for payment columns
echo "\n\n🔍 PAYMENT-RELATED COLUMNS:\n";
$paymentColumns = array_filter($columns, function($col) {
    return str_contains(strtolower($col->Field), 'payment') || 
           str_contains(strtolower($col->Field), 'stripe');
});

if (empty($paymentColumns)) {
    echo "❌ NO payment_status, payment_intent_id, or payment_date columns found!\n";
    echo "⚠️  These columns need to be added via migration.\n";
} else {
    foreach ($paymentColumns as $column) {
        echo sprintf("✅ %-30s %s\n", $column->Field, $column->Type);
    }
}
