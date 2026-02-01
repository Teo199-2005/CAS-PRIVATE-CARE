<?php
/**
 * Test script to check Stripe account and bank details
 * Run: php test_stripe_account.php
 */

require __DIR__ . '/vendor/autoload.php';

// Load Laravel environment
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $stripeSecret = config('stripe.secret');
    
    if (empty($stripeSecret)) {
        echo "ERROR: Stripe secret not configured\n";
        exit(1);
    }
    
    echo "Using Stripe Key: " . substr($stripeSecret, 0, 12) . "...\n\n";
    
    $stripe = new \Stripe\StripeClient($stripeSecret);
    
    // Get account
    echo "=== ACCOUNT INFO ===\n";
    $account = $stripe->accounts->retrieve();
    echo "Account ID: " . $account->id . "\n";
    echo "Business Name: " . ($account->business_profile->name ?? 'N/A') . "\n";
    echo "Charges Enabled: " . ($account->charges_enabled ? 'Yes' : 'No') . "\n";
    echo "Payouts Enabled: " . ($account->payouts_enabled ? 'Yes' : 'No') . "\n";
    echo "Country: " . ($account->country ?? 'N/A') . "\n";
    
    // Check external accounts
    echo "\n=== EXTERNAL ACCOUNTS ===\n";
    if (isset($account->external_accounts) && !empty($account->external_accounts->data)) {
        foreach ($account->external_accounts->data as $ext) {
            echo "Type: " . $ext->object . "\n";
            echo "Bank Name: " . ($ext->bank_name ?? 'N/A') . "\n";
            echo "Last4: " . ($ext->last4 ?? 'N/A') . "\n";
            echo "Routing: " . ($ext->routing_number ?? 'N/A') . "\n";
        }
    } else {
        echo "No external accounts directly on account object\n";
        
        // Try fetching separately
        try {
            $externalAccounts = $stripe->accounts->allExternalAccounts(
                $account->id,
                ['object' => 'bank_account', 'limit' => 5]
            );
            echo "\nFetched separately:\n";
            if (!empty($externalAccounts->data)) {
                foreach ($externalAccounts->data as $ext) {
                    echo "Bank Name: " . ($ext->bank_name ?? 'N/A') . "\n";
                    echo "Last4: " . ($ext->last4 ?? 'N/A') . "\n";
                }
            } else {
                echo "Still no external accounts found\n";
            }
        } catch (\Exception $e) {
            echo "Could not fetch external accounts: " . $e->getMessage() . "\n";
        }
    }
    
    // Check payouts
    echo "\n=== RECENT PAYOUTS ===\n";
    try {
        $payouts = $stripe->payouts->all(['limit' => 3, 'expand' => ['data.destination']]);
        if (!empty($payouts->data)) {
            foreach ($payouts->data as $payout) {
                echo "Payout ID: " . $payout->id . "\n";
                echo "Amount: $" . ($payout->amount / 100) . "\n";
                echo "Status: " . $payout->status . "\n";
                echo "Destination: " . (is_object($payout->destination) ? 
                    ($payout->destination->bank_name ?? 'object') . ' ••••' . ($payout->destination->last4 ?? '') : 
                    $payout->destination) . "\n\n";
            }
        } else {
            echo "No payouts found\n";
        }
    } catch (\Exception $e) {
        echo "Could not fetch payouts: " . $e->getMessage() . "\n";
    }
    
    // Get balance
    echo "\n=== BALANCE ===\n";
    $balance = $stripe->balance->retrieve();
    foreach ($balance->available as $funds) {
        echo "Available (" . $funds->currency . "): $" . ($funds->amount / 100) . "\n";
    }
    foreach ($balance->pending as $funds) {
        echo "Pending (" . $funds->currency . "): $" . ($funds->amount / 100) . "\n";
    }
    
    echo "\n=== DONE ===\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
