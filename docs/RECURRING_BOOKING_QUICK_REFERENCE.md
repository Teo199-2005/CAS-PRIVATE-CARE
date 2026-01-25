# 🔄 Recurring Booking - Quick Reference Card

## One-Page Cheat Sheet

---

## 📌 THE BASICS

**What is it?**
When a client pays for a booking, it automatically becomes a recurring contract.

**What happens?**
- Auto-renewal enabled by default
- Card charged when contract ends
- New booking created automatically
- Same caregiver assigned
- Service continues seamlessly

**Can clients cancel?**
Yes! Current service continues, but no auto-renewal after end date.

---

## ⚙️ HOW IT WORKS

```
Payment → Auto-Enabled → Service Period → Reminders → Renewal
```

1. **Client pays** → `recurring_service = true`
2. **Service runs** → 15 days of care
3. **5 reminders** → Days 5, 4, 3, 2, 1 before end
4. **End date** → 11:59 PM contract ends
5. **Auto-renewal** → 1:00 AM next day
   - IF `recurring_status = 'active'` → ✅ Charge card, create booking
   - IF `recurring_status = 'cancelled'` → ❌ No charge, contract ends

---

## 🎛️ CLIENT OPTIONS

| Action | Result | Current Service | Next Renewal |
|--------|--------|----------------|--------------|
| **Do Nothing** | Auto-renew continues | ✅ Continues | ✅ Renewed |
| **Pause** | Temporary stop | ✅ Continues | ❌ Skipped |
| **Resume** | Reactivate auto-pay | ✅ Continues | ✅ Renewed |
| **Cancel** | Permanent stop | ✅ Continues | ❌ Stopped |

---

## 🗄️ DATABASE FIELDS

```sql
bookings table:
├─ recurring_service (1 = yes, 0 = no)
├─ auto_pay_enabled (1 = yes, 0 = no)
├─ recurring_status ('active', 'paused', 'cancelled')
├─ recurring_count (number of renewals)
├─ last_recurring_charge_date (last charge)
└─ parent_booking_id (links to original)
```

---

## 🤖 AUTOMATED TASKS

### Process Recurring Bookings
```bash
Command: php artisan bookings:process-recurring
Schedule: Daily at 1:00 AM
Log: storage/logs/recurring-bookings.log
```

**Dry Run (test without changes):**
```bash
php artisan bookings:process-recurring --dry-run
```

### Send Reminder Emails
```bash
Command: php artisan bookings:send-recurring-reminders
Schedule: Daily at 9:00 AM
```

---

## 🔍 QUICK CHECKS

### Is Recurring Enabled?
```sql
SELECT recurring_service, auto_pay_enabled, recurring_status 
FROM bookings WHERE id = [booking_id];

-- Should see:
-- recurring_service = 1
-- auto_pay_enabled = 1
-- recurring_status = 'active'
```

### Did Renewal Happen?
```sql
SELECT * FROM bookings 
WHERE parent_booking_id = [original_id]
ORDER BY created_at DESC;

-- Should see new booking with matching details
```

### Check Payment
```sql
SELECT * FROM payments 
WHERE booking_id = [booking_id] 
ORDER BY created_at DESC LIMIT 1;

-- Should see completed payment record
```

---

## ⚠️ COMMON ISSUES

### Issue: Recurring not enabled after payment
**Check:**
- Payment successful?
- `ClientPaymentController.php:352-358` updating correctly?
- Database field types correct?

**Fix:**
```sql
UPDATE bookings 
SET recurring_service = 1,
    auto_pay_enabled = 1,
    recurring_status = 'active'
WHERE id = [booking_id];
```

### Issue: Auto-renewal not happening
**Check:**
- Scheduler running? (`php artisan schedule:work`)
- Booking end date passed?
- `recurring_status = 'active'`?
- Client has payment method?

**Fix:**
```bash
# Manually run process
php artisan bookings:process-recurring
```

### Issue: Emails not sending
**Check:**
- Email config correct?
- Mailtrap/SMTP working?
- Booking ends in 5 days?

**Fix:**
```bash
# Manually run reminders
php artisan bookings:send-recurring-reminders
```

---

## 🎯 KEY RULES

### ✅ DO THIS
- Auto-enable recurring on payment ✓
- Send 5 email reminders before renewal ✓
- Protect current service period ✓
- Allow pause/resume/cancel ✓
- Show countdown banner ✓

### ❌ DON'T DO THIS
- Don't interrupt current service when canceling ✗
- Don't charge without reminders ✗
- Don't auto-renew if cancelled ✗
- Don't hide renewal information ✗
- Don't prevent cancellation ✗

---

## 📋 CANCELLATION CHECKLIST

When client cancels:

- [ ] Set `recurring_status = 'cancelled'`
- [ ] Set `auto_pay_enabled = false`
- [ ] Set `recurring_service = false`
- [ ] **DO NOT** change `service_date`
- [ ] **DO NOT** change `end_date`
- [ ] **DO NOT** change `status`
- [ ] Send notification
- [ ] Current service continues
- [ ] No auto-renewal on end date

---

## 📱 WHERE TO FIND

### Client Side
- **Main View**: Payment Information → Recurring Contracts
- **Banner**: Dashboard (5 days before renewal)
- **History**: Click "View History" on any booking card

### Admin Side
- **Monitor**: Admin Dashboard → Recurring Bookings
- **Logs**: `storage/logs/recurring-bookings.log`
- **Database**: `bookings` table → filter `recurring_service = 1`

---

## 🔐 SECURITY

- ✅ Stripe handles all payments
- ✅ PCI compliant
- ✅ Client authorizes on first payment
- ✅ Can revoke anytime
- ✅ No stored credit card data

---

## 📊 STATUS MEANINGS

| Status | Meaning | Action |
|--------|---------|--------|
| **active** | Will auto-renew | ✅ Charge on end date |
| **paused** | Temporarily stopped | ⏸️ Skip renewal |
| **cancelled** | Permanently stopped | ❌ Never renew |
| **failed** | Payment failed | ⚠️ Notify client |

---

## 🚨 EMERGENCY COMMANDS

### Stop All Renewals (Emergency)
```sql
-- DO NOT USE IN PRODUCTION without backup!
UPDATE bookings 
SET recurring_status = 'paused'
WHERE recurring_service = 1 
AND recurring_status = 'active';
```

### Manual Renewal (If Missed)
```bash
# Process specific booking
php artisan bookings:process-recurring
```

### Refund Failed Renewal
```php
// Via Stripe Dashboard or API
Stripe\Refund::create([
    'payment_intent' => 'pi_xxx',
]);
```

---

## 📞 QUICK CONTACTS

| Issue | Contact | Action |
|-------|---------|--------|
| Payment failed | Client | Update payment method |
| Caregiver not assigned | Admin | Manually assign |
| Email not sent | DevOps | Check email config |
| Stripe error | Developer | Check API keys |
| Database issue | DBA | Check table structure |

---

## ✅ DAILY CHECKLIST

### Morning (9 AM)
- [ ] Check email reminders sent
- [ ] Verify notifications created
- [ ] Review Mailtrap/email logs

### Night (1 AM - Next Day)
- [ ] Check recurring-bookings.log
- [ ] Verify renewals processed
- [ ] Check for failed payments
- [ ] Review Stripe dashboard

### Weekly
- [ ] Calculate renewal rate
- [ ] Review cancellation rate
- [ ] Check failed payment count
- [ ] Analyze client feedback

---

## 💡 PRO TIPS

1. **Test with dry-run first**
   ```bash
   php artisan bookings:process-recurring --dry-run
   ```

2. **Monitor logs in real-time**
   ```bash
   tail -f storage/logs/recurring-bookings.log
   ```

3. **Check scheduler is running**
   ```bash
   php artisan schedule:list
   ```

4. **Use Stripe test cards**
   - Success: 4242 4242 4242 4242
   - Decline: 4000 0000 0000 0002

5. **Set test booking end date**
   ```sql
   UPDATE bookings 
   SET end_date = DATE_SUB(NOW(), INTERVAL 1 DAY)
   WHERE id = [test_booking_id];
   ```

---

## 📚 FULL DOCUMENTATION

For detailed information, see:
- `RECURRING_BOOKING_USER_GUIDE.md` - Complete guide
- `RECURRING_BOOKING_FLOW_DIAGRAM.md` - Visual flows
- `RECURRING_BOOKING_TESTING_CHECKLIST.md` - Testing
- `RECURRING_BOOKING_EXECUTIVE_SUMMARY.md` - Overview

---

## 🎓 REMEMBER

**The Golden Rule:**
> Current service is ALWAYS protected. Canceling recurring never interrupts ongoing care.

**The Auto-Enable Rule:**
> Every paid booking automatically becomes recurring. This is by design.

**The Transparency Rule:**
> 5 email reminders + countdown banner = no surprise charges.

**The Control Rule:**
> Clients have full control to pause, resume, or cancel anytime.

---

**Print this page and keep it handy!** 📄

---

**Version**: 1.0  
**Last Updated**: January 10, 2026  
**Status**: Production Ready ✅
