# Email Marketing & Contractor Notification System

## Overview

This system provides comprehensive email marketing capabilities for clients and automated notifications for contractors.

---

## Features

### Client Marketing Emails

1. **Customizable Email Content**
   - Write custom email content with HTML support
   - Personalization tokens: `{{name}}`, `{{first_name}}`, `{{email}}`, `{{company_name}}`
   - Preview emails before sending
   - Send test emails to verify appearance

2. **Client Filters**
   - All Clients
   - Never Booked (registered but no bookings)
   - Inactive 30+ Days
   - Inactive 60+ Days
   - Inactive 90+ Days
   - Active Clients (booked in last 30 days)
   - Repeat Clients (2+ bookings)
   - VIP Clients (5+ bookings)

3. **Campaign Management**
   - Save campaigns as drafts
   - View campaign history
   - Track open rates and click rates
   - View detailed analytics

### Contractor Notifications

1. **Assignment Notifications** - When assigned to a new booking
2. **Shift Reminders** - 24 hours before scheduled shifts
3. **Cancellation Alerts** - When bookings are cancelled
4. **Weekly Earnings Summary** - Weekly earnings report

---

## Files Created

### Database Migration
```
database/migrations/2026_01_17_000001_create_email_campaigns_table.php
```
Creates three tables:
- `email_campaigns` - Store campaign data
- `email_logs` - Track individual email sends with opens/clicks
- `contractor_notification_settings` - Store contractor preferences

### Models
```
app/Models/EmailCampaign.php
app/Models/EmailLog.php
app/Models/ContractorNotificationSetting.php
```

### Mailable Classes
```
app/Mail/MarketingCampaignEmail.php
app/Mail/AssignmentNotificationEmail.php
app/Mail/ShiftReminderEmail.php
app/Mail/WeeklyEarningsSummaryEmail.php
app/Mail/BookingCancellationEmail.php
```

### Email Templates
```
resources/views/emails/marketing-campaign.blade.php
resources/views/emails/assignment-notification.blade.php
resources/views/emails/shift-reminder.blade.php
resources/views/emails/weekly-earnings-summary.blade.php
resources/views/emails/booking-cancellation.blade.php
```

### Controllers
```
app/Http/Controllers/Admin/AdminEmailController.php
app/Http/Controllers/ContractorNotificationController.php
```

### Services
```
app/Services/ContractorNotificationService.php
```

### Vue Components
```
resources/js/components/admin/EmailMarketingPanel.vue
```

---

## API Endpoints

### Admin Email Marketing

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin/email-marketing/dashboard` | Get dashboard stats |
| GET | `/api/admin/email-marketing/campaigns` | List all campaigns |
| GET | `/api/admin/email-marketing/campaigns/{id}` | Get single campaign |
| POST | `/api/admin/email-marketing/campaigns` | Create campaign |
| PUT | `/api/admin/email-marketing/campaigns/{id}` | Update campaign |
| DELETE | `/api/admin/email-marketing/campaigns/{id}` | Delete campaign |
| POST | `/api/admin/email-marketing/campaigns/{id}/send` | Send campaign |
| GET | `/api/admin/email-marketing/campaigns/{id}/analytics` | Get analytics |
| GET | `/api/admin/email-marketing/clients` | Get filtered clients |
| GET | `/api/admin/email-marketing/filter-options` | Get filter options |
| POST | `/api/admin/email-marketing/preview` | Preview email |
| POST | `/api/admin/email-marketing/test-email` | Send test email |

### Email Tracking (Public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/email/track/open/{token}` | Track email opens (returns 1x1 pixel) |
| GET | `/email/track/click/{token}` | Track link clicks (redirects) |

### Contractor Notification Settings

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/contractor/notifications/settings` | Get settings |
| POST | `/api/contractor/notifications/settings` | Update settings |

---

## Usage

### Sending Marketing Email (Admin)

```php
use App\Mail\MarketingCampaignEmail;
use Illuminate\Support\Facades\Mail;

// Create campaign
$campaign = EmailCampaign::create([
    'name' => 'Winter Promo',
    'subject' => 'Special Holiday Offer!',
    'content' => '<p>Hi {{name}},</p><p>Check out our holiday specials...</p>',
    'type' => 'promotional',
    'target_audience' => 'all',
    'status' => 'draft',
    'created_by' => auth()->id()
]);

// Send to clients
foreach ($clients as $client) {
    $emailLog = EmailLog::create([
        'campaign_id' => $campaign->id,
        'user_id' => $client->id,
        'email' => $client->email,
        'status' => 'pending'
    ]);
    
    Mail::to($client->email)->send(new MarketingCampaignEmail(
        $campaign,
        $client,
        $processedContent,
        $emailLog->tracking_token
    ));
}
```

### Sending Contractor Notifications

```php
use App\Services\ContractorNotificationService;

$notificationService = new ContractorNotificationService();

// When assigning contractor to booking
$notificationService->sendAssignmentNotification($booking, $contractor);

// For shift reminders (run via scheduled task)
$notificationService->sendUpcomingShiftReminders();

// When booking is cancelled
$notificationService->sendCancellationNotification($booking, $contractor, 'Client requested cancellation');

// Weekly earnings (run via scheduled task every Sunday)
$notificationService->sendAllWeeklyEarningsSummaries();
```

---

## Scheduled Tasks

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Send shift reminders daily at 9 AM
    $schedule->call(function () {
        $service = new \App\Services\ContractorNotificationService();
        $service->sendUpcomingShiftReminders();
    })->dailyAt('09:00');
    
    // Send weekly earnings every Sunday at 6 PM
    $schedule->call(function () {
        $service = new \App\Services\ContractorNotificationService();
        $service->sendAllWeeklyEarningsSummaries();
    })->weeklyOn(0, '18:00');
}
```

---

## Integration with Admin Dashboard

Add the EmailMarketingPanel to your admin dashboard:

```vue
<template>
  <div>
    <!-- Other dashboard content -->
    
    <!-- Email Marketing Tab -->
    <EmailMarketingPanel v-if="activeTab === 'email-marketing'" />
  </div>
</template>

<script>
import EmailMarketingPanel from './admin/EmailMarketingPanel.vue';

export default {
  components: {
    EmailMarketingPanel
  }
}
</script>
```

---

## Database Setup

Run the migration:
```bash
php artisan migrate
```

---

## Testing

1. **Test Marketing Email:**
   - Go to Admin Dashboard → Email Marketing
   - Create a campaign with test content
   - Click "Send Test" and enter your email
   - Verify email is received with correct styling

2. **Test Contractor Notifications:**
   - Assign a contractor to a booking
   - Check contractor receives assignment notification
   - Run shift reminder command to test reminders

---

## Tracking & Analytics

- **Open Tracking:** Emails contain a 1x1 transparent pixel that fires when opened
- **Click Tracking:** Links are wrapped to track clicks before redirecting
- **Metrics Available:**
  - Total sent
  - Open count and rate
  - Click count and rate
  - Failed deliveries
  - Individual recipient activity

---

## Notes

- All emails use the consistent branded layout from `layout.blade.php`
- Orange theme (#f97316) with CAS Private Care branding
- Logo from: `https://casprivatecare.online/logo%20flower.png`
- Emails are sent via Brevo SMTP (configured in `.env`)
