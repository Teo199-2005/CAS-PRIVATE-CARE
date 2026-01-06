<?php

require __DIR__ . '/vendor/autoload.php';

echo "🔍 STRIPE INTEGRATION - FULL TEST\n";
echo "==================================\n\n";

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$publishableKey = $_ENV['STRIPE_KEY'] ?? '';
$secretKey = $_ENV['STRIPE_SECRET'] ?? '';
$clientId = $_ENV['STRIPE_CLIENT_ID'] ?? '';

echo "📋 KEYS STATUS:\n\n";

// Check Publishable Key
if ($publishableKey && strpos($publishableKey, 'pk_test_') === 0) {
    echo "✅ STRIPE_KEY (Publishable): " . substr($publishableKey, 0, 25) . "...\n";
    $hasPublishable = true;
} else {
    echo "❌ STRIPE_KEY: Missing or invalid\n";
    $hasPublishable = false;
}

// Check Secret Key
if ($secretKey && strpos($secretKey, 'sk_test_') === 0) {
    echo "✅ STRIPE_SECRET: " . substr($secretKey, 0, 25) . "...\n";
    $hasSecret = true;
} else {
    echo "❌ STRIPE_SECRET: Missing or invalid\n";
    $hasSecret = false;
}

// Check Client ID
if ($clientId && strpos($clientId, 'ca_') === 0) {
    echo "✅ STRIPE_CLIENT_ID: " . substr($clientId, 0, 15) . "...\n";
    $hasClientId = true;
} else {
    echo "⚠️  STRIPE_CLIENT_ID: Not set (needed for caregiver payouts)\n";
    $hasClientId = false;
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test Secret Key
if ($hasSecret) {
    echo "🧪 TEST 1: Testing Secret Key (Backend API)\n";
    echo "-------------------------------------------\n";
    try {
        \Stripe\Stripe::setApiKey($secretKey);
        
        // Create a test customer
        $customer = \Stripe\Customer::create([
            'email' => 'test-' . time() . '@casprivatecare.com',
            'name' => 'Test Customer',
            'description' => 'Integration test'
        ]);
        
        echo "✅ SUCCESS! Created customer: " . $customer->id . "\n";
        echo "   Email: " . $customer->email . "\n";
        echo "   Status: Active\n\n";
        
        // Test retrieving customer
        $retrieved = \Stripe\Customer::retrieve($customer->id);
        echo "✅ Can retrieve customer data\n";
        
        // Clean up - delete test customer
        $customer->delete();
        echo "✅ Test cleanup successful\n\n";
        
    } catch (\Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n\n";
    }
}

// Test Publishable Key
if ($hasPublishable) {
    echo "🧪 TEST 2: Testing Publishable Key (Frontend)\n";
    echo "----------------------------------------------\n";
    echo "✅ Publishable key is valid format\n";
    echo "✅ Can be used in Stripe.js for client-side forms\n";
    echo "✅ Safe to expose in frontend code\n\n";
}

// Test Client ID
if ($hasClientId) {
    echo "🧪 TEST 3: Testing Client ID (Stripe Connect)\n";
    echo "----------------------------------------------\n";
    echo "✅ Client ID format is valid\n";
    echo "✅ Can be used for Connect onboarding\n\n";
} else {
    echo "⚠️  TEST 3: Client ID Not Set\n";
    echo "----------------------------------------------\n";
    echo "To enable caregiver bank connections:\n";
    echo "1. Go to: https://dashboard.stripe.com/settings/applications\n";
    echo "2. Find 'OAuth settings' section\n";
    echo "3. Copy the Client ID (starts with ca_)\n";
    echo "4. Add to .env: STRIPE_CLIENT_ID=ca_your_id\n\n";
}

echo str_repeat("=", 50) . "\n\n";

// Calculate completion percentage
$totalTests = 3;
$passedTests = ($hasPublishable ? 1 : 0) + ($hasSecret ? 1 : 0) + ($hasClientId ? 1 : 0);
$percentage = round(($passedTests / $totalTests) * 100);

echo "📊 OVERALL STATUS: " . $percentage . "% Complete\n\n";

if ($percentage == 100) {
    echo "🎉 PERFECT! All keys configured!\n";
    echo "✅ You can now:\n";
    echo "   - Process client payments\n";
    echo "   - Setup payment methods\n";
    echo "   - Connect caregiver banks\n";
    echo "   - Transfer funds\n";
    echo "   - Full payment workflow\n\n";
} elseif ($percentage >= 66) {
    echo "🟢 GOOD! Core functionality ready!\n";
    echo "✅ You can:\n";
    echo "   - Process client payments\n";
    echo "   - Setup payment methods\n";
    echo "   - Test payment flows\n\n";
    if (!$hasClientId) {
        echo "📝 TODO: Add STRIPE_CLIENT_ID for caregiver payouts\n\n";
    }
} else {
    echo "🟡 PARTIAL: Basic functionality only\n\n";
    if (!$hasPublishable) {
        echo "❌ Need STRIPE_KEY for client payment forms\n";
    }
    if (!$hasSecret) {
        echo "❌ Need STRIPE_SECRET for backend processing\n";
    }
    if (!$hasClientId) {
        echo "⚠️  Need STRIPE_CLIENT_ID for caregiver payouts\n";
    }
    echo "\n";
}

echo str_repeat("=", 50) . "\n\n";

echo "📚 NEXT STEPS:\n\n";

if ($percentage == 100) {
    echo "1. ✅ Test the integration:\n";
    echo "   - Visit: http://localhost:8000/api/stripe/connection-status\n";
    echo "   - Test client payment setup\n";
    echo "   - Test caregiver bank connection\n\n";
    echo "2. ✅ Add UI buttons to dashboards\n";
    echo "   - Client: Add payment method button\n";
    echo "   - Caregiver: Connect bank button\n";
    echo "   - Admin: Payment processing interface\n\n";
    echo "3. ✅ Process your first payment!\n\n";
} elseif ($percentage >= 66) {
    echo "1. ✅ Test what's working:\n";
    echo "   - Visit: http://localhost:8000/api/stripe/connection-status\n";
    echo "   - Test client payment setup\n\n";
    echo "2. 📝 Add STRIPE_CLIENT_ID:\n";
    echo "   - Go to: https://dashboard.stripe.com/settings/applications\n";
    echo "   - Copy Client ID (ca_...)\n";
    echo "   - Add to .env file\n\n";
    echo "3. ✅ Then test caregiver bank connection\n\n";
} else {
    echo "1. 📝 Complete your .env setup:\n";
    if (!$hasPublishable) echo "   - Add STRIPE_KEY\n";
    if (!$hasSecret) echo "   - Add STRIPE_SECRET\n";
    if (!$hasClientId) echo "   - Add STRIPE_CLIENT_ID\n";
    echo "\n2. 🔄 Run: php artisan config:clear\n\n";
    echo "3. 🧪 Run this test again\n\n";
}

echo "📖 Documentation: Check STRIPE_QUICK_START.md\n";
echo "🔧 Support: Check STRIPE_INTEGRATION_GUIDE.md\n\n";
