<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$stripe = new \Stripe\StripeClient(config('stripe.secret'));
$balance = $stripe->balance->retrieve();

echo "=== BALANCE ===\n";
foreach ($balance->available as $f) {
    echo "Available ({$f->currency}): $" . ($f->amount / 100) . "\n";
}

$hasBalance = false;
foreach ($balance->available as $funds) {
    if ($funds->amount > 0) {
        $hasBalance = true;
        break;
    }
}

echo "\nHas Balance > 0: " . ($hasBalance ? "YES" : "NO") . "\n";

if ($hasBalance) {
    echo "\n=== BANK ACCOUNT WOULD BE SET ===\n";
    echo "Bank: STRIPE TEST BANK\n";
    echo "Last4: 6789\n";
    echo "Routing: 110000000\n";
}
