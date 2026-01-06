<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Adding Stripe columns to payments table...\n\n";

try {
    Schema::table('payments', function (Blueprint $table) {
        if (!Schema::hasColumn('payments', 'stripe_payment_intent_id')) {
            $table->string('stripe_payment_intent_id')->nullable()->after('transaction_id');
            echo "✅ Added 'stripe_payment_intent_id' column\n";
        } else {
            echo "⏭️  'stripe_payment_intent_id' column already exists\n";
        }
        
        if (!Schema::hasColumn('payments', 'stripe_customer_id')) {
            $table->string('stripe_customer_id')->nullable()->after('stripe_payment_intent_id');
            echo "✅ Added 'stripe_customer_id' column\n";
        } else {
            echo "⏭️  'stripe_customer_id' column already exists\n";
        }
    });
    
    echo "\n✅ Payments table updated successfully!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
}
