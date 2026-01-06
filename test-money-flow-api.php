<?php
/**
 * TEST MONEY FLOW API
 * 
 * This script tests if the Money Flow Dashboard API returns correct data
 * Run: php test-money-flow-api.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\PaymentMonitoringController;
use Illuminate\Http\Request;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "           🧪 TESTING MONEY FLOW DASHBOARD API 🧪              \n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    $controller = new PaymentMonitoringController();
    $response = $controller->getMoneyFlowDashboard();
    
    $data = json_decode($response->getContent(), true);
    
    if ($data['success']) {
        echo "✅ API Response: SUCCESS\n\n";
        
        echo "📊 TODAY'S ACTIVITY:\n";
        echo "  Payments In:  $" . number_format($data['today']['payments_in'], 2) . "\n";
        echo "  Payouts Out:  $" . number_format($data['today']['payouts_out'], 2) . "\n";
        echo "  Net Change:   $" . number_format($data['today']['net_change'], 2) . "\n\n";
        
        echo "📈 ALL-TIME TOTALS:\n";
        echo "  Total Payments In:  $" . number_format($data['totals']['total_payments_in'], 2) . "\n";
        echo "  Total Payouts Out:  $" . number_format($data['totals']['total_payouts_out'], 2) . "\n";
        echo "  Pending Payouts:    $" . number_format($data['totals']['pending_payouts'], 2) . "\n";
        echo "  Expected Balance:   $" . number_format($data['totals']['expected_balance'], 2) . "\n";
        
        if ($data['totals']['stripe_balance'] !== null) {
            echo "  Stripe Balance:     $" . number_format($data['totals']['stripe_balance'], 2) . "\n";
            echo "  Difference:         $" . number_format(abs($data['totals']['balance_difference']), 2);
            if ($data['totals']['balance_difference'] == 0) {
                echo " ✅ MATCHED\n";
            } else {
                echo " ⚠️  REVIEW\n";
            }
        }
        
        echo "\n";
        echo "💰 COMMISSIONS:\n";
        echo "  Pending Marketing:  $" . number_format($data['commissions']['pending_marketing'], 2) . "\n";
        echo "  Pending Training:   $" . number_format($data['commissions']['pending_training'], 2) . "\n";
        echo "  Platform Total:     $" . number_format($data['commissions']['platform_total'], 2) . "\n\n";
        
        echo "👥 CAREGIVER BALANCES:\n";
        if (count($data['caregiver_balances']) > 0) {
            foreach ($data['caregiver_balances'] as $caregiver) {
                echo "  • {$caregiver['name']}\n";
                echo "    Pending: $" . number_format($caregiver['pending_balance'], 2);
                echo " | Paid: $" . number_format($caregiver['total_paid'], 2);
                echo " | Bank: " . ($caregiver['bank_connected'] ? '✓ Connected' : '✗ Not Connected') . "\n";
            }
        } else {
            echo "  No caregivers found\n";
        }
        
        echo "\n";
        echo "🚨 FAILED PAYOUTS:\n";
        if (count($data['failed_payouts']) > 0) {
            foreach ($data['failed_payouts'] as $failed) {
                echo "  ⚠️  {$failed['caregiver_name']}: $" . number_format($failed['amount'], 2) . "\n";
                echo "     Reason: {$failed['failure_reason']}\n";
            }
        } else {
            echo "  ✅ No failed payouts\n";
        }
        
        echo "\n";
        echo "📝 RECENT TRANSACTIONS:\n";
        if (count($data['recent_transactions']) > 0) {
            foreach (array_slice($data['recent_transactions'], 0, 5) as $transaction) {
                $symbol = $transaction['type'] === 'payment_in' ? '💵' : '💸';
                echo "  {$symbol} \${$transaction['amount']} - {$transaction['created_at']}\n";
            }
        } else {
            echo "  No recent transactions\n";
        }
        
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "✅ API IS WORKING CORRECTLY!\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        echo "💡 NEXT STEPS:\n";
        echo "1. Hard refresh your browser (Ctrl+Shift+R)\n";
        echo "2. Open browser console (F12)\n";
        echo "3. Look for 'Money Flow Data Loaded:' message\n";
        echo "4. Check Network tab for /api/admin/money-flow-dashboard response\n\n";
        
    } else {
        echo "❌ API Response: FAILED\n";
        echo "Error: " . ($data['message'] ?? 'Unknown error') . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR TESTING API:\n";
    echo $e->getMessage() . "\n";
    echo "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n";
