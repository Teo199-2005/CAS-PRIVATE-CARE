# Recurring Booking Renewal Reminder System

## Overview
Implemented a comprehensive countdown notification system that alerts clients about upcoming recurring contract renewals through email and dashboard notifications.

## Features

### 1. **Automated Email Reminders**
- Sends emails at **5, 4, 3, 2, and 1 days** before contract renewal
- Total of **5 reminder emails** per renewal cycle
- Beautiful HTML email template with contract details
- Runs daily at 9:00 AM via Laravel scheduler

### 2. **Dashboard Countdown Banner**
- Real-time countdown display on client dashboard
- Shows days remaining until renewal
- Displays contract details (amount, service type, duration)
- Color-coded alerts:
  - **Red** (Error): 0-1 days remaining
  - **Orange** (Warning): 2 days remaining  
  - **Blue** (Info): 3-5 days remaining
- Dismissible banners (per session)
- Auto-refreshes every 5 minutes

### 3. **In-App Notifications**
- Creates notification in the database for each reminder
- Stored in `notifications` table
- Accessible through Notification Center
- Links to Payment Information page

## File Structure

### Backend Files

1. **`app/Console/Commands/SendRecurringReminderEmails.php`**
   - Artisan command: `php artisan bookings:send-recurring-reminders`
   - Checks all active recurring bookings
   - Calculates days until renewal
   - Sends emails at 5, 4, 3, 2, 1 days before
   - Creates in-app notifications
   - Prevents duplicate emails on the same day

2. **`app/Console/Kernel.php`** (Updated)
   - Scheduled to run daily at 9:00 AM
   - Command: `$schedule->command('bookings:send-recurring-reminders')->dailyAt('09:00')`

3. **`app/Http/Controllers/RecurringBookingController.php`** (Updated)
   - New method: `getUpcomingRenewals()`
   - API endpoint: `GET /client/recurring/upcoming-renewals`
   - Returns bookings renewing within 5 days

4. **`routes/web.php`** (Updated)
   - Added route: `Route::get('/upcoming-renewals', [RecurringBookingController::class, 'getUpcomingRenewals'])`

### Frontend Files

1. **`resources/js/components/RecurringRenewalCountdown.vue`** (New)
   - Vue component for countdown banner
   - Displays prominent alerts above dashboard
   - Shows countdown, amount, service details
   - "Manage" button → navigates to Payment Information
   - "Details" dialog with full contract information
   - Dismissible per session (uses sessionStorage)

2. **`resources/js/components/ClientDashboard.vue`** (Updated)
   - Imported `RecurringRenewalCountdown` component
   - Placed below Email Verification Banner
   - Passes navigation event to switch sections

3. **`resources/views/emails/recurring-reminder.blade.php`** (New)
   - Beautiful HTML email template
   - Gradient header with countdown badge
   - Contract details table
   - Warning box about auto-renewal
   - "What Happens Next" section
   - CTA button to manage contract
   - Responsive design

## Email Template Variables

The email template uses the following variables:
- `$client_name` - Client's name
- `$days_until_renewal` - Days remaining (5, 4, 3, 2, or 1)
- `$renewal_date` - Formatted renewal date (e.g., "February 15, 2026")
- `$booking_id` - Booking ID number
- `$service_type` - Service type (e.g., "Caregiver")
- `$duration_days` - Contract duration in days
- `$amount` - Total amount to be charged (formatted)
- `$hours_per_day` - Hours per day
- `$dashboard_url` - URL to client dashboard

## How It Works

### Reminder Email Flow:
1. **Daily Scheduled Task** (9:00 AM)
   - Command: `bookings:send-recurring-reminders` runs
   - Queries all active recurring bookings
   
2. **Calculate Days Until Renewal**
   - End date = `service_date + duration_days`
   - Days remaining = End date - Today
   
3. **Send Reminders**
   - If days remaining = 5, 4, 3, 2, or 1:
     - Check if email already sent today
     - Send email with contract details
     - Create in-app notification
     - Log the action

4. **Client Receives**
   - Email in inbox with countdown
   - Notification in dashboard notification center
   - Countdown banner on dashboard (if logged in)

### Dashboard Countdown Flow:
1. **Component Loads** (`RecurringRenewalCountdown.vue`)
   - Calls API: `/client/recurring/upcoming-renewals`
   - Loads all bookings renewing within 5 days
   
2. **Display Banners**
   - Sorts by days remaining (ascending)
   - Shows color-coded v-alert for each
   - Auto-refreshes every 5 minutes
   
3. **User Actions**
   - Click "Manage" → Navigate to Payment Information
   - Click "Details" → Show full contract dialog
   - Click "X" → Dismiss banner (session only)

## Notification Types

The system creates the following notification types:
- `renewal_reminder_5_days` - 5 days before renewal
- `renewal_reminder_4_days` - 4 days before renewal
- `renewal_reminder_3_days` - 3 days before renewal
- `renewal_reminder_2_days` - 2 days before renewal
- `renewal_reminder_1_days` - 1 day before renewal

## Testing

### Test the Email Command Manually:
```bash
php artisan bookings:send-recurring-reminders
```

### Test with Specific Booking:
1. Create a booking with:
   - `recurring_service = true`
   - `auto_pay_enabled = true`
   - `recurring_status = 'active'`
   - `service_date` = Today minus (duration_days - 5) days
   
2. Run the command:
```bash
php artisan bookings:send-recurring-reminders
```

3. Check:
   - Email should be sent to client
   - Notification created in `notifications` table
   - Log entry in `storage/logs/recurring-reminders.log`

### Test the Dashboard Countdown:
1. Log in as a client with active recurring bookings
2. Ensure renewal date is within 5 days
3. Check dashboard for countdown banner
4. Verify:
   - Correct days remaining displayed
   - Amount and details accurate
   - "Manage" button works
   - Banner is dismissible

## Schedule Configuration

The reminder emails run on a daily schedule:

```php
$schedule->command('bookings:send-recurring-reminders')
    ->dailyAt('09:00')
    ->appendOutputTo(storage_path('logs/recurring-reminders.log'));
```

**Note:** Ensure Laravel scheduler is running via cron:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Email Configuration

Ensure your `.env` file has proper mail configuration:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-brevo-email
MAIL_PASSWORD=your-brevo-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@casprivatecare.com
MAIL_FROM_NAME="CAS Private Care"
```

## Database Requirements

The system uses existing fields in the `bookings` table:
- `recurring_service` (boolean)
- `auto_pay_enabled` (boolean)
- `recurring_status` (enum: active, paused, cancelled)
- `service_date` (date)
- `duration_days` (integer)
- `duty_type` (string - contains hours)
- `hourly_rate` (decimal)
- `service_type` (string)

## User Experience

### Client Receives:
1. **5 Days Before**: First reminder email + dashboard banner
2. **4 Days Before**: Second reminder email + updated banner
3. **3 Days Before**: Third reminder email + updated banner
4. **2 Days Before**: Fourth reminder (warning color) + urgent banner
5. **1 Day Before**: Final reminder (error color) + critical banner
6. **Renewal Day**: Contract auto-renews, charge processed

### Each Email Contains:
- Countdown badge in header
- Contract details table
- Amount to be charged
- Renewal date
- "What Happens Next" section
- Link to manage/cancel auto-renewal

### Dashboard Banner Shows:
- Real-time countdown
- Service type and duration
- Amount to be charged
- Quick action buttons
- Can be dismissed (returns on page refresh)

## Benefits

1. **Client Awareness**: Clients are well-informed about upcoming charges
2. **Prevents Surprises**: 5 reminders ensure clients don't forget
3. **Easy Management**: One-click access to disable auto-renewal
4. **Professional**: Beautiful emails and UI enhance brand perception
5. **Compliance**: Transparent billing practices
6. **Reduced Disputes**: Clear communication reduces chargebacks

## Future Enhancements

Potential additions:
- SMS reminders (Twilio integration)
- Push notifications (if mobile app added)
- Customizable reminder schedule
- Pause renewal from email link
- Preview next charge amount in real-time

---

**Implementation Date**: January 9, 2026
**Status**: ✅ Complete and Active
