# 🔄 RECURRING PAYMENTS & AUTO-REBOOKING ANALYSIS

## 📋 CURRENT STATE ASSESSMENT

### ✅ What EXISTS (Infrastructure)

#### 1. **Database Fields** (Bookings Table)
```php
// These fields EXIST in the bookings table:
'recurring_service' => boolean          // Checkbox if booking is recurring
'recurring_schedule' => string          // daily, weekly, bi_weekly, monthly
'stripe_subscription_id' => string      // Stripe subscription ID
'payment_type' => enum                  // 'one-time' or 'recurring'
'auto_pay_enabled' => boolean           // If auto-payment is active
'next_payment_date' => datetime         // When next payment is due
'stripe_price_id' => string             // Stripe price ID for subscription
```

#### 2. **Payment Method Binding**
✅ Clients CAN link their payment methods via:
- `/connect-payment-method` route
- Stripe Connect onboarding
- Payment methods saved in Stripe

#### 3. **Subscription Infrastructure**
✅ Code exists in `ClientPaymentController.php`:
- `createSubscription()` method
- `cancelSubscription()` method
- Stripe subscription creation logic

#### 4. **Webhook Handlers**
✅ `StripeWebhookController.php` handles:
- `customer.subscription.deleted`
- `customer.subscription.updated`
- `invoice.payment_succeeded`
- `invoice.payment_failed`

### ❌ What DOES NOT EXIST (Missing Logic)

#### 1. **Automatic Rebooking System**
❌ No cron job/scheduled task to:
- Check when bookings end
- Create new booking with same parameters
- Charge the client automatically
- Repeat the service duration

#### 2. **Recurring Booking Creation**
❌ No command like:
```bash
php artisan bookings:process-recurring
```

#### 3. **Auto-Charge Logic**
❌ No automatic charging when:
- Previous booking ends
- Client has payment method on file
- `recurring_service = true`

#### 4. **Booking Repetition**
❌ No logic to:
- Copy booking details (duration, hours, service type)
- Create identical new booking
- Set start date = previous end date + 1 day
- Maintain same caregivers if possible

---

## 🚨 THE PROBLEM

### What You WANT:
```
Client books 15-day service
┌─────────────────────────────────────────────┐
│ Booking #1: Jan 1 - Jan 15 (15 days)       │
│ Status: Paid ($10,800)                      │
│ Payment Method: Card ending ****4242        │
└─────────────────────────────────────────────┘
                     ⬇️
         Jan 15 (booking ends)
                     ⬇️
┌─────────────────────────────────────────────┐
│ 🔄 AUTO-CREATE Booking #2                  │
│ Start: Jan 16                               │
│ Duration: 15 days (same as original)        │
│ Hours: Same as original                     │
│ Caregiver: Same if available                │
│ 💳 AUTO-CHARGE: $10,800                    │
│ Status: Auto-approved & paid                │
└─────────────────────────────────────────────┘
                     ⬇️
         Jan 30 (booking #2 ends)
                     ⬇️
┌─────────────────────────────────────────────┐
│ 🔄 AUTO-CREATE Booking #3                  │
│ ... repeats forever until canceled          │
└─────────────────────────────────────────────┘
```

### What CURRENTLY Happens:
```
Client books 15-day service
┌─────────────────────────────────────────────┐
│ Booking #1: Jan 1 - Jan 15 (15 days)       │
│ Status: Paid ($10,800)                      │
└─────────────────────────────────────────────┘
                     ⬇️
         Jan 15 (booking ends)
                     ⬇️
┌─────────────────────────────────────────────┐
│ ❌ NOTHING HAPPENS                          │
│ Client must manually book again             │
│ No auto-rebooking                           │
│ No auto-charging                            │
└─────────────────────────────────────────────┘
```

---

## 🎯 WHAT NEEDS TO BE IMPLEMENTED

### 1. **Recurring Booking Command**

Create: `app/Console/Commands/ProcessRecurringBookings.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\User;
use App\Services\StripePaymentService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessRecurringBookings extends Command
{
    protected $signature = 'bookings:process-recurring';
    protected $description = 'Process recurring bookings and create new bookings automatically';

    public function handle()
    {
        $this->info('🔄 Processing recurring bookings...');
        
        // Find bookings that:
        // 1. Are marked as recurring_service = true
        // 2. Have ended (service_date + duration_days < today)
        // 3. Have auto_pay_enabled = true
        // 4. Have payment method on file
        // 5. Status = 'completed'
        
        $endedRecurringBookings = Booking::where('recurring_service', true)
            ->where('auto_pay_enabled', true)
            ->where('status', 'completed')
            ->whereNotNull('stripe_subscription_id')
            ->get();
            
        foreach ($endedRecurringBookings as $booking) {
            try {
                // Check if booking has ended
                $endDate = Carbon::parse($booking->service_date)
                    ->addDays($booking->duration_days);
                    
                if ($endDate->isPast()) {
                    // Check if we already created next booking
                    $nextBookingExists = Booking::where('client_id', $booking->client_id)
                        ->where('parent_booking_id', $booking->id)
                        ->exists();
                        
                    if (!$nextBookingExists) {
                        $this->createRecurringBooking($booking);
                    }
                }
            } catch (\Exception $e) {
                $this->error("Error processing booking #{$booking->id}: " . $e->getMessage());
            }
        }
        
        $this->info('✅ Recurring bookings processed successfully');
    }
    
    private function createRecurringBooking($originalBooking)
    {
        // Create new booking with same details
        $newBooking = Booking::create([
            'client_id' => $originalBooking->client_id,
            'parent_booking_id' => $originalBooking->id, // Track relationship
            'service_type' => $originalBooking->service_type,
            'duty_type' => $originalBooking->duty_type,
            'borough' => $originalBooking->borough,
            'city' => $originalBooking->city,
            'county' => $originalBooking->county,
            'service_date' => Carbon::parse($originalBooking->service_date)
                ->addDays($originalBooking->duration_days + 1), // Start day after previous ends
            'start_time' => $originalBooking->start_time,
            'duration_days' => $originalBooking->duration_days,
            'hourly_rate' => $originalBooking->hourly_rate,
            'payment_method' => $originalBooking->payment_method,
            'day_schedules' => $originalBooking->day_schedules,
            'recurring_service' => true,
            'recurring_schedule' => $originalBooking->recurring_schedule,
            'status' => 'pending_payment', // Will be approved after payment
            'auto_pay_enabled' => true,
            'street_address' => $originalBooking->street_address,
            'apartment_unit' => $originalBooking->apartment_unit,
            'special_instructions' => $originalBooking->special_instructions,
        ]);
        
        // Charge the client automatically
        $this->chargeRecurringBooking($newBooking);
        
        $this->info("✅ Created recurring booking #{$newBooking->id} from #{$originalBooking->id}");
    }
    
    private function chargeRecurringBooking($booking)
    {
        // Get client's default payment method
        $client = User::find($booking->client_id);
        
        if (!$client->stripe_customer_id) {
            throw new \Exception('Client has no payment method on file');
        }
        
        // Calculate amount
        $hours = $this->extractHours($booking->duty_type);
        $amount = $hours * $booking->duration_days * $booking->hourly_rate;
        
        // Charge via Stripe
        $paymentService = new StripePaymentService();
        $result = $paymentService->chargeRecurringBooking($booking, $amount);
        
        if ($result['success']) {
            $booking->update([
                'status' => 'approved',
                'payment_status' => 'paid',
                'payment_date' => now(),
                'stripe_payment_intent_id' => $result['payment_intent_id'],
            ]);
        }
    }
    
    private function extractHours($dutyType)
    {
        preg_match('/(\d+)/', $dutyType, $matches);
        return $matches[1] ?? 8;
    }
}
```

---

### 2. **Database Migration**

Create: `database/migrations/2026_01_09_add_parent_booking_id_to_bookings.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_booking_id')->nullable()->after('id');
            $table->foreign('parent_booking_id')
                  ->references('id')
                  ->on('bookings')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['parent_booking_id']);
            $table->dropColumn('parent_booking_id');
        });
    }
};
```

---

### 3. **Update Kernel.php Schedule**

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('bookings:update-status')->hourly();
    $schedule->command('app:auto-clock-out')->everyMinute();
    
    // NEW: Process recurring bookings daily at 1 AM
    $schedule->command('bookings:process-recurring')->dailyAt('01:00');
}
```

---

### 4. **Update Booking Model**

```php
// Add to fillable array
'parent_booking_id',

// Add relationship
public function parentBooking()
{
    return $this->belongsTo(Booking::class, 'parent_booking_id');
}

public function childBookings()
{
    return $this->hasMany(Booking::class, 'parent_booking_id');
}
```

---

### 5. **Add Stripe Charge Method**

Update: `app/Services/StripePaymentService.php`

```php
public function chargeRecurringBooking($booking, $amount)
{
    $client = User::find($booking->client_id);
    
    try {
        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => $amount * 100, // Convert to cents
            'currency' => 'usd',
            'customer' => $client->stripe_customer_id,
            'payment_method' => $this->getDefaultPaymentMethod($client->stripe_customer_id),
            'off_session' => true, // Important for recurring charges
            'confirm' => true,
            'description' => "Recurring booking #{$booking->id}",
            'metadata' => [
                'booking_id' => $booking->id,
                'client_id' => $client->id,
                'type' => 'recurring',
            ],
        ]);
        
        return [
            'success' => true,
            'payment_intent_id' => $paymentIntent->id,
        ];
    } catch (\Exception $e) {
        Log::error('Recurring charge failed', [
            'booking_id' => $booking->id,
            'error' => $e->getMessage(),
        ]);
        
        // Send notification to admin and client
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}

private function getDefaultPaymentMethod($customerId)
{
    $customer = $this->stripe->customers->retrieve($customerId);
    return $customer->invoice_settings->default_payment_method 
        ?? $customer->default_source;
}
```

---

## 📊 HOW IT WILL WORK

### Client Dashboard View:

```
┌─────────────────────────────────────────────────────────────┐
│  💳 SAVED PAYMENT METHODS                                   │
├─────────────────────────────────────────────────────────────┤
│  Visa •••• 4242                            [Remove]         │
│  Expires: 2/2033                                            │
│  ✅ Auto-pay enabled for recurring bookings                │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  📋 ACTIVE RECURRING CONTRACTS                              │
├─────────────────────────────────────────────────────────────┤
│  🔄 Booking #12 - Live-in Care                             │
│  Status: Active • Next charge: Jan 16, 2026                │
│  Amount: $10,800 every 15 days                             │
│  [View Details] [Cancel Recurring]                         │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  📜 BOOKING HISTORY                                         │
├─────────────────────────────────────────────────────────────┤
│  ✅ Booking #12 (Jan 1-15) - Paid $10,800                  │
│  🔄 Booking #13 (Jan 16-30) - Auto-scheduled               │
│  ⏳ Booking #14 (Jan 31-Feb 14) - Upcoming                 │
└─────────────────────────────────────────────────────────────┘
```

### Admin Dashboard View:

```
┌─────────────────────────────────────────────────────────────┐
│  🔄 RECURRING BOOKINGS MONITOR                              │
├─────────────────────────────────────────────────────────────┤
│  Client: John Doe                                           │
│  Original: Booking #12 (completed)                          │
│  Current: Booking #13 (active)                              │
│  Status: ✅ Auto-payment successful                        │
│  Next charge: Jan 31, 2026                                  │
└─────────────────────────────────────────────────────────────┘
```

---

## 🚀 IMPLEMENTATION STEPS

### Phase 1: Database Setup
1. ✅ Run migration to add `parent_booking_id`
2. ✅ Update Booking model with relationships

### Phase 2: Command Creation
1. ✅ Create `ProcessRecurringBookings` command
2. ✅ Add to Kernel.php schedule
3. ✅ Test manually: `php artisan bookings:process-recurring`

### Phase 3: Stripe Integration
1. ✅ Add `chargeRecurringBooking()` method
2. ✅ Handle payment failures
3. ✅ Send notifications

### Phase 4: Dashboard Updates
1. ✅ Show recurring status on client dashboard
2. ✅ Show next charge date
3. ✅ Add "Cancel Recurring" button
4. ✅ Show booking chain (parent → child bookings)

### Phase 5: Admin Portal
1. ✅ Monitor recurring bookings
2. ✅ View payment success/failures
3. ✅ Manual intervention if needed

---

## ✅ TESTING CHECKLIST

### Test Scenario 1: First Recurring Booking
1. Client books 15-day service with recurring checked
2. Client links payment method
3. Client pays for first booking
4. Wait 15 days (or manually run command)
5. **Expected:** New booking created automatically and charged

### Test Scenario 2: Payment Failure
1. Remove client's payment method
2. Run recurring command
3. **Expected:** 
   - Booking created but not approved
   - Notification sent to client and admin
   - Status shows "Payment failed"

### Test Scenario 3: Cancel Recurring
1. Client clicks "Cancel Recurring"
2. Current booking completes
3. **Expected:** No new booking created

---

## 📝 SUMMARY

**CURRENTLY:**
- ✅ Infrastructure exists (database fields, webhooks)
- ❌ NO automatic rebooking
- ❌ NO automatic charging
- ❌ NO booking repetition

**NEEDS TO BE BUILT:**
1. ❌ `ProcessRecurringBookings` command
2. ❌ `chargeRecurringBooking()` method
3. ❌ `parent_booking_id` migration
4. ❌ Dashboard UI for recurring contracts
5. ❌ Cancel recurring functionality
6. ❌ Email notifications for recurring charges

**ESTIMATED TIME:** 6-8 hours of development

**PRIORITY:** HIGH (Core business feature)

---

**Date:** January 9, 2026
**Status:** 🚨 NOT IMPLEMENTED - Needs Development
