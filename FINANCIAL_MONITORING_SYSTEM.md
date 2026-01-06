# 🔒 FINANCIAL MONITORING & AUDIT SYSTEM

## Critical Financial Controls

This system provides **bulletproof tracking** of every dollar moving through your platform.

---

## 🎯 **KEY FEATURES**

### 1. **Pre-Payment Verification** (Before money leaves)
✅ Verifies caregiver has bank account connected  
✅ Confirms unpaid time records exist  
✅ Validates amount matches calculated earnings  
✅ Checks for duplicate payments  
✅ Enforces safety limits ($10,000 max per payout)  

### 2. **Payout Transaction Logging**
Every payout creates a permanent record with:
- **Who**: Admin user who approved it
- **What**: Exact amount, currency
- **When**: Initiated, completed, or failed timestamps
- **Where**: Stripe transfer ID and destination account
- **Why**: Time tracking record IDs included
- **How**: Status tracking (pending → processing → completed/failed)

### 3. **Double-Entry Bookkeeping**
Every transaction creates two ledger entries:
```
Debit:  Caregiver Payables  -$672.00  (reduces what you owe)
Credit: Bank Account        -$672.00  (money leaving your account)
```

### 4. **Post-Payment Verification**
✅ Confirms all time records marked as paid  
✅ Verifies Stripe transfer ID was recorded  
✅ Checks final status is "completed"  

### 5. **Daily Balance Snapshots**
Automated daily reconciliation showing:
- Total revenue from clients
- Amount owed to caregivers (payables)
- Amount already paid to caregivers
- Marketing & training commissions
- Platform revenue (your cut)
- **Stripe balance comparison** ← Catches discrepancies!

---

## 📊 **HOW TO MONITOR**

### **Daily Checks (Automated)**
Run this command every day at midnight:
```bash
php artisan finance:snapshot
```

Add to scheduler in `app/Console/Kernel.php`:
```php
$schedule->command('finance:snapshot')->daily();
```

### **Manual Reconciliation**
```bash
# Check yesterday's finances
php artisan finance:snapshot

# Check specific date
php artisan finance:snapshot 2026-01-05
```

### **Query Examples**

#### See all payouts today:
```php
use App\Models\PayoutTransaction;

$todayPayouts = PayoutTransaction::whereDate('completed_at', today())
    ->with('caregiver.user')
    ->get();

foreach ($todayPayouts as $payout) {
    echo "{$payout->caregiver->user->name}: \${$payout->amount} - {$payout->stripe_transfer_id}\n";
}
```

#### Find failed payouts:
```php
$failedPayouts = PayoutTransaction::where('status', 'failed')
    ->with('caregiver.user')
    ->get();
```

#### Check if amount matches Stripe:
```php
use App\Models\DailyBalanceSnapshot;

$yesterday = DailyBalanceSnapshot::where('snapshot_date', today()->subDay())->first();

if ($yesterday->discrepancies) {
    echo "⚠️ ALERT: " . $yesterday->discrepancies;
}
```

#### Audit trail for specific payout:
```php
$payout = PayoutTransaction::with(['verifications', 'adminUser', 'caregiver.user'])->find(1);

echo "Payout #{$payout->id}\n";
echo "Amount: \${$payout->amount}\n";
echo "Approved by: {$payout->adminUser->name}\n";
echo "Stripe ID: {$payout->stripe_transfer_id}\n";
echo "Sessions: {$payout->sessions_count}\n";
echo "Hours: {$payout->total_hours}\n";

foreach ($payout->verifications as $verification) {
    echo "Verification: {$verification->verification_type} - " . ($verification->passed ? 'PASSED' : 'FAILED') . "\n";
}
```

---

## 🚨 **ALERT SYSTEM**

### Critical Alerts to Monitor:

1. **Amount Mismatch**
   - Requested amount ≠ Calculated earnings
   - **Action**: BLOCK payment, investigate

2. **Duplicate Payment Attempt**
   - Time records already marked as paid
   - **Action**: BLOCK payment, alert admin

3. **Stripe Balance Discrepancy**
   - Your database balance ≠ Stripe balance
   - **Action**: Daily reconciliation required

4. **Failed Stripe Transfer**
   - Stripe API returned error
   - **Action**: Check caregiver's account, retry manually

5. **Missing Stripe Transfer ID**
   - Payment marked complete but no Stripe ID
   - **Action**: CRITICAL - verify if money actually sent

---

## 📈 **FINANCIAL REPORTS**

### Monthly Payout Summary:
```php
$monthlyPayouts = PayoutTransaction::whereMonth('completed_at', now()->month)
    ->where('status', 'completed')
    ->sum('amount');

echo "Total paid to caregivers this month: \${$monthlyPayouts}";
```

### Caregiver Payment History:
```php
$caregiverPayouts = PayoutTransaction::where('caregiver_id', 3)
    ->where('status', 'completed')
    ->orderBy('completed_at', 'desc')
    ->get();
```

### Platform Revenue Check:
```php
$totalRevenue = Payment::where('status', 'completed')->sum('amount');
$totalPayouts = PayoutTransaction::where('status', 'completed')->sum('amount');
$platformRevenue = $totalRevenue - $totalPayouts;

echo "Platform Revenue: \${$platformRevenue}";
```

---

## 🔐 **SECURITY FEATURES**

1. **Database Transactions**
   - All-or-nothing: If anything fails, entire payout is rolled back
   - Prevents partial payments

2. **Immutable Audit Trail**
   - Every payout creates permanent record
   - Cannot be deleted, only status updated

3. **Admin User Tracking**
   - Records WHO approved each payment
   - Accountability for every dollar

4. **Stripe Transfer Linking**
   - Every database record links to Stripe transfer ID
   - Can verify in Stripe dashboard

5. **Time Tracking Linkage**
   - JSON array stores which sessions were paid
   - Can reconstruct payment at any time

---

## ⚠️ **SAFETY LIMITS**

Currently set to **$10,000 per payout**. Adjust in `PayoutService.php` line 134:
```php
if ($amount > 10000) { // Change this number
    $errors[] = 'Amount exceeds safety limit';
}
```

---

## 🔧 **SETUP INSTRUCTIONS**

### 1. Run Migration:
```bash
php artisan migrate
```

### 2. Update AdminController to use PayoutService:
```php
use App\Services\PayoutService;

public function payCaregiver(Request $request)
{
    $validated = $request->validate([
        'caregiver_id' => 'required|exists:caregivers,id',
        'amount' => 'required|numeric|min:0'
    ]);

    $payoutService = new PayoutService();
    $result = $payoutService->processCaregiverPayout(
        $validated['caregiver_id'],
        $validated['amount'],
        auth()->id()
    );

    return response()->json($result);
}
```

### 3. Add Daily Snapshot to Scheduler:
In `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('finance:snapshot')->dailyAt('23:59');
}
```

### 4. Set up Monitoring Dashboard
Create admin route to view:
- Today's payouts
- Failed transactions
- Balance discrepancies
- Pending reconciliations

---

## 📱 **STRIPE DASHBOARD VERIFICATION**

### Where to Check in Stripe:

1. **Transfers**
   - Home → Transfers
   - Search by `stripe_transfer_id` from database
   - Verify amount and destination

2. **Connected Accounts**
   - Connect → Accounts
   - Click caregiver's account
   - See their balance and transfer history

3. **Balance**
   - Home → Balance
   - Compare with `daily_balance_snapshots.stripe_balance`
   - Should match within $1 (rounding)

---

## 🎯 **BEST PRACTICES**

1. **Run daily snapshot command every day**
2. **Review discrepancies immediately**
3. **Never delete PayoutTransaction records**
4. **Export financial ledger monthly for accounting**
5. **Reconcile Stripe balance weekly**
6. **Keep failed payout records for investigation**
7. **Alert system if payment > $1,000**
8. **Require 2-factor auth for large payouts**

---

## 💾 **BACKUP STRATEGY**

### Critical Tables to Backup:
- `payout_transactions` - Every payout ever made
- `financial_ledger` - Double-entry bookkeeping
- `time_trackings` - What was paid for
- `daily_balance_snapshots` - Daily reconciliation

### Backup Frequency:
- **Before payouts**: Backup database
- **Daily**: Automated backup at midnight
- **Monthly**: Archive to cold storage

---

## ✅ **COMPLIANCE CHECKLIST**

- [ ] All payouts logged in `payout_transactions`
- [ ] Stripe transfer IDs recorded
- [ ] Time tracking records linked
- [ ] Admin approver tracked
- [ ] Pre and post-payment verifications passed
- [ ] Financial ledger entries created
- [ ] Daily snapshots running
- [ ] Stripe balance reconciled
- [ ] No discrepancies found
- [ ] Audit trail complete

---

## 🆘 **TROUBLESHOOTING**

### "Amount mismatch" error:
- **Cause**: Calculated amount ≠ requested amount
- **Fix**: Check for rounding issues, verify time_tracking records

### "Stripe transfer failed":
- **Cause**: Caregiver's account has issues
- **Fix**: Check Stripe dashboard, verify bank account connected

### "No unpaid records":
- **Cause**: Time tracking records already paid
- **Fix**: Check `paid_at` timestamps, prevent duplicate payment

### "Balance discrepancy":
- **Cause**: Database balance ≠ Stripe balance
- **Fix**: Run reconciliation report, check for missing transactions

---

## 📞 **SUPPORT**

If you lose money or find discrepancies:

1. **Stop all payouts immediately**
2. **Run**: `php artisan finance:snapshot`
3. **Check**: `daily_balance_snapshots` for discrepancies
4. **Review**: All `payout_transactions` from today
5. **Verify**: Each transfer in Stripe dashboard
6. **Document**: All findings
7. **Contact**: Stripe support if money missing

---

**Remember**: Every payout is logged, verified, and reconciled. You have a complete audit trail! 🔒
