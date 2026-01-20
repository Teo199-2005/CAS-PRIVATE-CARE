<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Stripe Revenue Check ===\n\n";

try {
    \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    $charges = \Stripe\Charge::all(['limit' => 100]);
    $total = 0;
    foreach($charges->data as $c) {
        if($c->status === 'succeeded') {
            $total += $c->amount / 100;
        }
    }
    echo "Total Stripe Revenue (Succeeded Charges): \$" . number_format($total, 2) . "\n";
} catch (\Exception $e) {
    echo "Stripe Error: " . $e->getMessage() . "\n";
}

echo "\n=== Database Payments Summary ===\n\n";

$payments = DB::table('payments')
    ->selectRaw('status, COUNT(*) as count, SUM(amount) as total')
    ->groupBy('status')
    ->get();

if ($payments->isEmpty()) {
    echo "No payments found in the database.\n";
} else {
    foreach($payments as $p) {
        echo $p->status . ': ' . $p->count . ' records, total: $' . number_format($p->total, 2) . "\n";
    }
}

echo "\n=== Total Revenue (completed only) ===\n";
$completed = DB::table('payments')->where('status', 'completed')->sum('amount');
echo "Database completed payments total: \$" . number_format($completed, 2) . "\n";
