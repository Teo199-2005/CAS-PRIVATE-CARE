<?php

require __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    echo "Testing Stripe API Connection...\n\n";
    
    $stripeSecret = $_ENV['STRIPE_SECRET'] ?? null;
    
    if (!$stripeSecret) {
        echo "ERROR: STRIPE_SECRET not found in .env file\n";
        exit(1);
    }
    
    echo "Stripe Secret Key: " . substr($stripeSecret, 0, 10) . "..." . substr($stripeSecret, -4) . "\n";
    
    $stripe = new \Stripe\StripeClient($stripeSecret);
    
    echo "Creating test customer...\n";
    
    $customer = $stripe->customers->create([
        'email' => 'test@example.com',
        'name' => 'Test User',
        'metadata' => [
            'test' => 'true'
        ]
    ]);
    
    echo "✓ SUCCESS: Customer created\n";
    echo "  Customer ID: " . $customer->id . "\n";
    echo "  Email: " . $customer->email . "\n";
    
    echo "\nCreating SetupIntent...\n";
    
    $intent = $stripe->setupIntents->create([
        'customer' => $customer->id,
        'usage' => 'off_session',
        'payment_method_types' => ['card']
    ]);
    
    echo "✓ SUCCESS: SetupIntent created\n";
    echo "  Intent ID: " . $intent->id . "\n";
    echo "  Client Secret: " . substr($intent->client_secret, 0, 20) . "...\n";
    
    echo "\nDeleting test customer...\n";
    $stripe->customers->delete($customer->id);
    echo "✓ Test customer deleted\n";
    
    echo "\n✓ ALL TESTS PASSED - Stripe API is working correctly!\n";
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    echo "\n✗ STRIPE API ERROR:\n";
    echo "  Type: " . get_class($e) . "\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "  Code: " . $e->getStripeCode() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n✗ ERROR:\n";
    echo "  Type: " . get_class($e) . "\n";
    echo "  Message: " . $e->getMessage() . "\n";
    exit(1);
}
