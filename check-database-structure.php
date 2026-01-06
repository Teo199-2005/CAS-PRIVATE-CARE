<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║           DATABASE STRUCTURE ANALYSIS                         ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Get all tables
$tables = DB::select('SHOW TABLES');
$dbName = env('DB_DATABASE');
$tableKey = "Tables_in_{$dbName}";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "EXISTING TABLES\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$tableList = [];
foreach ($tables as $table) {
    $tableName = $table->$tableKey;
    $tableList[] = $tableName;
    echo "✅ {$tableName}\n";
}

echo "\nTotal tables: " . count($tableList) . "\n";

// Check for critical tables
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "CRITICAL TABLES CHECK\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$criticalTables = [
    'users' => 'User accounts',
    'bookings' => 'Service bookings',
    'payments' => 'Payment records',
    'caregivers' => 'Caregiver profiles',
    'clients' => 'Client profiles',
    'time_tracking' => 'Time tracking (might be named differently)',
    'reviews' => 'Review system',
    'notifications' => 'Notification system',
    'referral_codes' => 'Referral codes',
    'booking_assignments' => 'Caregiver assignments',
];

foreach ($criticalTables as $table => $description) {
    if (in_array($table, $tableList)) {
        echo "✅ {$table} - {$description}\n";
    } else {
        echo "❌ {$table} - {$description} [MISSING]\n";
    }
}

// Check for alternate table names
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "CHECKING ALTERNATE TABLE NAMES\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$alternateNames = [
    'time_trackings' => 'Time tracking (plural)',
    'time_tracking_records' => 'Time tracking records',
    'caregiver_time_tracking' => 'Caregiver time tracking',
];

foreach ($alternateNames as $table => $description) {
    if (in_array($table, $tableList)) {
        echo "✅ FOUND: {$table} - {$description}\n";
    }
}

// Check payments table structure
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "PAYMENTS TABLE STRUCTURE\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if (in_array('payments', $tableList)) {
    $columns = DB::select("DESCRIBE payments");
    
    $hasStripeIntent = false;
    $hasStripeCustomer = false;
    
    foreach ($columns as $column) {
        echo "• {$column->Field} ({$column->Type})";
        
        if ($column->Field === 'stripe_payment_intent_id') {
            $hasStripeIntent = true;
            echo " ✅";
        }
        if ($column->Field === 'stripe_customer_id') {
            $hasStripeCustomer = true;
            echo " ✅";
        }
        
        echo "\n";
    }
    
    echo "\n";
    if (!$hasStripeIntent) {
        echo "⚠️  WARNING: 'stripe_payment_intent_id' column missing\n";
    }
    if (!$hasStripeCustomer) {
        echo "⚠️  WARNING: 'stripe_customer_id' column missing\n";
    }
} else {
    echo "❌ Payments table not found\n";
}

// Check users table structure
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "USERS TABLE STRUCTURE\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if (in_array('users', $tableList)) {
    $columns = DB::select("DESCRIBE users");
    
    $hasRole = false;
    $hasUserType = false;
    
    foreach ($columns as $column) {
        if ($column->Field === 'role') $hasRole = true;
        if ($column->Field === 'user_type') $hasUserType = true;
    }
    
    if ($hasRole) {
        echo "✅ 'role' column exists\n";
    } else {
        echo "❌ 'role' column missing\n";
    }
    
    if ($hasUserType) {
        echo "✅ 'user_type' column exists\n";
    } else {
        echo "❌ 'user_type' column missing\n";
    }
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "RECOMMENDATIONS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if (!in_array('time_tracking', $tableList) && !in_array('time_trackings', $tableList)) {
    echo "⚠️  Create time_tracking table or update model to use existing table name\n";
}

if (in_array('payments', $tableList)) {
    $columns = DB::select("DESCRIBE payments");
    $columnNames = array_column($columns, 'Field');
    
    if (!in_array('stripe_payment_intent_id', $columnNames)) {
        echo "⚠️  Add 'stripe_payment_intent_id' column to payments table\n";
    }
    if (!in_array('stripe_customer_id', $columnNames)) {
        echo "⚠️  Add 'stripe_customer_id' column to payments table\n";
    }
}

echo "\n✅ Database structure analysis complete!\n";
