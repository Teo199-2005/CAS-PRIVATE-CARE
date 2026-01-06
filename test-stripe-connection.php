<?php

require __DIR__ . '/vendor/autoload.php';

echo "🔍 Testing Stripe Connection...\n\n";

// Load environment
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'STRIPE_SECRET=') === 0) {
            $secretKey = trim(str_replace('STRIPE_SECRET=', '', $line));
            echo "✅ Found STRIPE_SECRET in .env file\n";
            echo "📝 Key starts with: " . substr($secretKey, 0, 20) . "...\n\n";
            
            // Test the key
            try {
                \Stripe\Stripe::setApiKey($secretKey);
                
                echo "🧪 Testing API connection...\n";
                $customer = \Stripe\Customer::create([
                    'email' => 'test@casprivatecare.com',
                    'name' => 'Test Customer',
                    'description' => 'Test customer created on ' . date('Y-m-d H:i:s')
                ]);
                
                echo "✅ SUCCESS! Stripe connection is working!\n\n";
                echo "📊 Test Results:\n";
                echo "   - Customer ID: " . $customer->id . "\n";
                echo "   - Email: " . $customer->email . "\n";
                echo "   - Created: " . date('Y-m-d H:i:s', $customer->created) . "\n\n";
                
                echo "🎉 Your Stripe integration is READY!\n\n";
                echo "✅ You can now:\n";
                echo "   - Process payments\n";
                echo "   - Create customers\n";
                echo "   - Transfer to caregivers (once they connect banks)\n\n";
                
                echo "📝 Next steps:\n";
                echo "   1. Get STRIPE_KEY (publishable key) for client payment forms\n";
                echo "   2. Get STRIPE_CLIENT_ID for caregiver bank connections\n";
                echo "   3. Check HOW_TO_GET_STRIPE_KEYS.md for instructions\n\n";
                
            } catch (\Exception $e) {
                echo "❌ ERROR: " . $e->getMessage() . "\n\n";
                echo "💡 Possible issues:\n";
                echo "   - Check if the secret key is correct\n";
                echo "   - Make sure you're using test mode key (starts with sk_test_)\n";
                echo "   - Verify your Stripe account is active\n\n";
            }
            break;
        }
    }
} else {
    echo "❌ .env file not found\n";
}
