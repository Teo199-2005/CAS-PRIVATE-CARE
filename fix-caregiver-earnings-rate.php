<?php
/**
 * FIX CAREGIVER EARNINGS RATE
 * ===========================
 * 
 * This script recalculates all caregiver_earnings in time_trackings
 * to use the CORRECT ASSIGNED rate from booking assignments.
 * 
 * RATE STRUCTURE:
 * - Admin assigns hourly rate when assigning caregiver/housekeeper
 * - The assigned rate is stored in booking_assignments.assigned_hourly_rate
 * - If no assignment exists, falls back to PricingService defaults:
 *   * Caregiver: $28.00/hr
 *   * Housekeeper: $20.00/hr
 * 
 * CLIENT PAYS (FIXED):
 * - No referral: $45/hr
 * - With referral: $40/hr
 * 
 * COMMISSIONS (FIXED):
 * - Marketing: $1.00/hr (if referral used)
 * - Training: $0.50/hr (if training center)
 * 
 * Run: php fix-caregiver-earnings-rate.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TimeTracking;
use App\Models\BookingAssignment;
use App\Models\BookingHousekeeperAssignment;
use App\Services\PricingService;
use Illuminate\Support\Facades\DB;

echo "═══════════════════════════════════════════════════════════════\n";
echo "       💰 FIX CAREGIVER EARNINGS RATE - FLEXIBLE RATE SYSTEM 💰\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Get the default rates from PricingService
$defaultCaregiverRate = PricingService::getCaregiverRate(); // $28.00
$defaultHousekeeperRate = PricingService::HOUSEKEEPER_DEFAULT_RATE; // $20.00

echo "📋 Rate Configuration:\n";
echo "   Default Caregiver Rate: \$" . number_format($defaultCaregiverRate, 2) . "/hr\n";
echo "   Default Housekeeper Rate: \$" . number_format($defaultHousekeeperRate, 2) . "/hr\n";
echo "   Client pays: \$45/hr (no referral) or \$40/hr (with referral)\n\n";

// Get all time tracking records
$records = TimeTracking::whereNotNull('hours_worked')
    ->where('hours_worked', '>', 0)
    ->get();

echo "📊 Found " . $records->count() . " time tracking records to review\n\n";

$fixedCount = 0;
$alreadyCorrect = 0;
$errors = [];
$savings = 0;

echo "Record ID | Provider     | Hours    | Current     | Correct     | Rate Used     | Status\n";
echo "-" . str_repeat("-", 100) . "\n";

foreach ($records as $record) {
    $hours = (float)$record->hours_worked;
    $currentEarnings = (float)$record->caregiver_earnings;
    $providerType = 'Caregiver';
    $correctRate = $defaultCaregiverRate;
    
    // Determine provider type and get assigned rate
    if ($record->housekeeper_id) {
        $providerType = 'Housekeeper';
        $correctRate = $defaultHousekeeperRate;
        
        // Check for assigned rate in booking_housekeeper_assignments
        if ($record->booking_id) {
            $assignment = DB::table('booking_housekeeper_assignments')
                ->where('booking_id', $record->booking_id)
                ->where('housekeeper_id', $record->housekeeper_id)
                ->first();
            
            if ($assignment && $assignment->assigned_hourly_rate) {
                $correctRate = (float)$assignment->assigned_hourly_rate;
            }
        }
    } elseif ($record->caregiver_id) {
        $providerType = 'Caregiver';
        $correctRate = $defaultCaregiverRate;
        
        // Check for assigned rate in booking_assignments
        if ($record->booking_id) {
            $assignment = DB::table('booking_assignments')
                ->where('booking_id', $record->booking_id)
                ->where('caregiver_id', $record->caregiver_id)
                ->first();
            
            if ($assignment && $assignment->assigned_hourly_rate) {
                $correctRate = (float)$assignment->assigned_hourly_rate;
            }
        }
    }
    
    $correctEarnings = round($hours * $correctRate, 2);
    $rateInfo = '$' . number_format($correctRate, 2) . '/hr';
    
    if ($record->booking_id) {
        $rateInfo .= ' (assigned)';
    } else {
        $rateInfo .= ' (default)';
    }
    
    if (abs($currentEarnings - $correctEarnings) < 0.01) {
        // Already correct
        $alreadyCorrect++;
        echo sprintf("#%-8d | %-12s | %-8.2f | \$%-9.2f | \$%-9.2f | %-13s | ✅ OK\n",
            $record->id, $providerType, $hours, $currentEarnings, $correctEarnings, $rateInfo);
    } else {
        // Needs fixing
        $difference = $currentEarnings - $correctEarnings;
        $savings += $difference;
        
        $currentRate = $hours > 0 ? round($currentEarnings / $hours, 2) : 0;
        
        echo sprintf("#%-8d | %-12s | %-8.2f | \$%-9.2f | \$%-9.2f | %-13s | ⚠️ WRONG (\$%.2f/hr)\n",
            $record->id, $providerType, $hours, $currentEarnings, $correctEarnings, $rateInfo, $currentRate);
        
        $errors[] = [
            'id' => $record->id,
            'hours' => $hours,
            'current' => $currentEarnings,
            'correct' => $correctEarnings,
            'current_rate' => $currentRate,
            'correct_rate' => $correctRate,
            'difference' => $difference
        ];
    }
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "                        SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ Already Correct: " . $alreadyCorrect . " records\n";
echo "⚠️ Need Fixing: " . count($errors) . " records\n";

if ($savings > 0) {
    echo "💵 Total Overpayment: \$" . number_format($savings, 2) . " (if paid at wrong rate)\n";
} elseif ($savings < 0) {
    echo "💵 Total Underpayment: \$" . number_format(abs($savings), 2) . "\n";
}

if (count($errors) > 0) {
    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "                    APPLYING FIX\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    echo "⏳ Fixing " . count($errors) . " records...\n\n";
    
    DB::beginTransaction();
    try {
        foreach ($errors as $error) {
            TimeTracking::where('id', $error['id'])->update([
                'caregiver_earnings' => $error['correct']
            ]);
            $fixedCount++;
            echo "   Fixed #" . $error['id'] . ": \$" . number_format($error['current'], 2) . 
                 " → \$" . number_format($error['correct'], 2) . 
                 " (rate: \$" . number_format($error['correct_rate'], 2) . "/hr)\n";
        }
        
        DB::commit();
        echo "\n✅ SUCCESS! Fixed " . $fixedCount . " records.\n";
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo "\n❌ ERROR: " . $e->getMessage() . "\n";
        echo "All changes have been rolled back.\n";
    }
} else {
    echo "\n✅ All records are already correct!\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "                    RATE STRUCTURE SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "📋 How Rates Work:\n";
echo "   1. Admin assigns caregiver/housekeeper to booking\n";
echo "   2. Admin sets the assigned_hourly_rate (within their preferred range)\n";
echo "   3. When they clock out, earnings = hours × assigned_hourly_rate\n";
echo "   4. If no booking, default rate is used (\$28 caregiver, \$20 housekeeper)\n\n";

echo "💰 Client Payment (FIXED):\n";
echo "   • Without referral: \$45.00/hr\n";
echo "   • With referral: \$40.00/hr\n\n";

echo "📊 Agency Profit = Client Rate - Provider Rate - Commissions\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "                          DONE!\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
