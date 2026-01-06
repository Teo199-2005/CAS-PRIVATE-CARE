# Fix $0.00 Booking Amount - Complete Guide

## 🚨 Problem: Shows $0.00 and "Failed to initialize payment"

Your booking is missing required data fields. Here's how to fix it:

---

## ✅ Quick Fix (Recommended)

### Option 1: Auto-Fix a Booking via API

Run this in your browser console while on the payment page:

```javascript
// Get booking ID from URL
const urlParams = new URLSearchParams(window.location.search);
const bookingId = urlParams.get('booking_id');

// Fix the booking
fetch(`/api/bookings/${bookingId}/add-payment-data`, {
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    'Accept': 'application/json'
  }
})
.then(res => res.json())
.then(data => {
  console.log('✅ Booking fixed:', data);
  location.reload(); // Reload page
});
```

### Option 2: Update Booking in Database

Run this SQL query:

```sql
UPDATE bookings 
SET 
  duration_days = 15,
  duty_type = '8 Hours',
  hourly_rate = 40,
  hours = 120
WHERE id = 1;  -- Replace with your booking ID
```

### Option 3: Use Artisan Tinker

```bash
php artisan tinker

# Then in tinker:
$booking = \App\Models\Booking::find(1);
$booking->duration_days = 15;
$booking->duty_type = '8 Hours';
$booking->hourly_rate = 40;
$booking->hours = 120;
$booking->save();
```

---

## 🔍 Debugging: Check Console Logs

After refreshing the payment page, open browser console (F12) and look for:

```
📦 Booking loaded: {...}
💰 Calculation breakdown:
  Duration: 15 days
  Duty type: 8 Hours
  Hours/day: 8
  Hourly rate: $ 40
  Subtotal: $ 4800.00
  Total: $ 5226.00
💳 Creating payment intent for $5226.00 (522600 cents)
✅ Payment Intent created successfully
```

If you see:
```
❌ Invalid booking amount: 0
Booking data: {
  duration_days: null,
  duty_type: null,
  hourly_rate: null
}
```

Then the booking needs data (use one of the fixes above).

---

## 📊 Required Booking Fields

Your booking table needs these columns with data:

| Column | Type | Example | Required |
|--------|------|---------|----------|
| `duration_days` | integer | 15 | Yes |
| `duty_type` | string | "8 Hours" | Yes |
| `hourly_rate` | decimal | 40.00 | Yes |
| `hours` | integer | 120 | Optional* |

*If `hours` is missing, it's calculated as: `duration_days × hours_per_day`

---

## 🧮 Calculation Logic

```javascript
// Step 1: Extract hours per day from duty_type
duty_type = "8 Hours"  → 8 hours/day
duty_type = "12 Hours" → 12 hours/day
duty_type = "24 Hours" → 24 hours/day

// Step 2: Calculate total hours
total_hours = duration_days × hours_per_day
Example: 15 days × 8 hours = 120 hours

// Step 3: Calculate subtotal
subtotal = total_hours × hourly_rate
Example: 120 × $40 = $4,800

// Step 4: Add tax
tax = subtotal × 0.08875  // 8.875% NYC tax
total = subtotal + tax
Example: $4,800 + $426 = $5,226
```

---

## 🎯 Testing Different Scenarios

### Scenario 1: Standard 8-hour care
```sql
UPDATE bookings SET 
  duration_days = 15,
  duty_type = '8 Hours',
  hourly_rate = 40
WHERE id = 1;
```
**Result**: 15 × 8 × $40 = **$4,800** + tax = **$5,226**

### Scenario 2: Live-in 24-hour care
```sql
UPDATE bookings SET 
  duration_days = 30,
  duty_type = '24 Hours',
  hourly_rate = 45
WHERE id = 1;
```
**Result**: 30 × 24 × $45 = **$32,400** + tax = **$35,274.50**

### Scenario 3: Part-time 4-hour care
```sql
UPDATE bookings SET 
  duration_days = 7,
  duty_type = '4 Hours',
  hourly_rate = 35
WHERE id = 1;
```
**Result**: 7 × 4 × $35 = **$980** + tax = **$1,066.98**

---

## 🔧 Check Booking Structure

Run this to see what your booking currently has:

```sql
SELECT 
  id,
  client_id,
  duration_days,
  duty_type,
  hourly_rate,
  hours,
  status,
  created_at
FROM bookings 
WHERE id = 1;
```

Expected output:
```
id | duration_days | duty_type | hourly_rate | hours | status
1  | 15           | 8 Hours   | 40.00       | 120   | pending
```

---

## 🚀 Quick Test Commands

### 1. Fix Booking #1
```bash
curl -X POST http://localhost:8000/api/bookings/1/add-payment-data \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 2. Check Booking #1
```bash
curl http://localhost:8000/api/bookings/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Test Payment Page
```
Visit: http://localhost:8000/payment?booking_id=1
```

---

## 🎨 What You'll See After Fix

### Before:
```
Subtotal: $0.00
Tax: $0.00
Total: $0.00
❌ Error: Failed to initialize payment
```

### After:
```
Subtotal: $4,800.00
Tax: $426.00
Total: $5,226.00
✅ Stripe Payment Element loaded
💳 Ready to accept payment
```

---

## 📋 Migration (If Columns Don't Exist)

If your bookings table doesn't have these columns, create migration:

```bash
php artisan make:migration add_payment_fields_to_bookings_table
```

Then in the migration:

```php
public function up()
{
    Schema::table('bookings', function (Blueprint $table) {
        if (!Schema::hasColumn('bookings', 'duration_days')) {
            $table->integer('duration_days')->default(15);
        }
        if (!Schema::hasColumn('bookings', 'duty_type')) {
            $table->string('duty_type')->default('8 Hours');
        }
        if (!Schema::hasColumn('bookings', 'hourly_rate')) {
            $table->decimal('hourly_rate', 8, 2)->default(40.00);
        }
        if (!Schema::hasColumn('bookings', 'hours')) {
            $table->integer('hours')->nullable();
        }
    });
}
```

Run:
```bash
php artisan migrate
```

---

## 🎯 Verification Checklist

After applying fix, verify:

- [ ] Booking has `duration_days` (e.g., 15)
- [ ] Booking has `duty_type` (e.g., "8 Hours")
- [ ] Booking has `hourly_rate` (e.g., 40.00)
- [ ] Payment page shows calculated amount (not $0.00)
- [ ] Console shows calculation breakdown
- [ ] Payment Element loads successfully
- [ ] Can enter test card (4242 4242 4242 4242)
- [ ] Payment processes successfully

---

## 📞 Still Not Working?

### Check Browser Console

Look for these error patterns:

**Error**: `"Failed to initialize payment"`
**Cause**: Amount is $0.00
**Fix**: Add booking data (see fixes above)

**Error**: `"Request failed with status code 422"`
**Cause**: Amount is less than 50 cents
**Fix**: Ensure booking calculates to at least $0.50

**Error**: `"Booking not found"`
**Cause**: Invalid booking ID
**Fix**: Check URL has correct `?booking_id=X`

### Check Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

Look for:
- "Error creating payment intent"
- "Error loading booking details"
- Database errors

---

## 🎉 Success Indicators

When everything works, you'll see:

```
Console:
✅ Stripe initialized successfully
📦 Booking loaded: {...}
💰 Calculation breakdown: ...
💳 Creating payment intent for $5226.00
✅ Payment Intent created successfully
✅ Payment Element ready

UI:
✓ Shows correct amount ($5,226.00)
✓ Payment Element visible
✓ Subscribe button enabled
✓ Can enter card details
```

---

## 🆘 Quick Reference

**Fix booking**: `POST /api/bookings/{id}/add-payment-data`  
**Check booking**: `GET /api/bookings/{id}`  
**Payment page**: `/payment?booking_id={id}`  
**Test card**: `4242 4242 4242 4242`  
**Minimum amount**: $0.50 (50 cents)  

---

**Status**: Ready to fix  
**Build**: ✅ Complete  
**Next**: Apply one of the fixes above and refresh page!

