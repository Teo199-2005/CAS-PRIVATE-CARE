# 🔄 Recurring Booking - Quick Reference Card

## For Client Payment Information Page

### Visual Card to Display Above Payment Methods

```
┌──────────────────────────────────────────────────────────────────────┐
│ 🔄 AUTOMATIC RECURRING CONTRACTS                                     │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│ ✅ HOW IT WORKS                                                      │
│                                                                      │
│   When you pay for an approved booking:                            │
│   • Your booking automatically becomes a recurring contract         │
│   • Auto-renewal is enabled by default                             │
│   • Your saved card will be charged when the contract ends         │
│   • A new booking with the same schedule will begin automatically  │
│                                                                      │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│ 🔔 BEFORE RENEWAL                                                    │
│                                                                      │
│   We'll send you email reminders:                                  │
│   📧 5 days before renewal                                          │
│   📧 4 days before renewal                                          │
│   📧 3 days before renewal                                          │
│   📧 2 days before renewal                                          │
│   📧 1 day before renewal                                           │
│   🖥️ Dashboard countdown banner appears                            │
│                                                                      │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│ ⚙️ YOU'RE IN CONTROL                                                │
│                                                                      │
│   Manage your recurring contracts below:                           │
│   • ⏸️ Pause Auto-Renewal - Temporarily stop automatic payments     │
│   • ▶️ Resume Auto-Renewal - Reactivate automatic payments         │
│   • ❌ Cancel Recurring - Stop future renewals permanently         │
│                                                                      │
│   ⚠️ Note: Canceling recurring will not affect your current        │
│   service period. Your care will continue until the scheduled       │
│   end date, but no new booking will be created automatically.      │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

---

## Vue Component Implementation

### Add This Info Card to ClientPaymentMethods.vue

Add this component before the "Recurring Contracts" section:

```vue
<template>
  <!-- Info Card About Recurring -->
  <v-card class="recurring-info-card mb-6" elevation="0" outlined>
    <v-card-text class="pa-5">
      <div class="d-flex align-start mb-3">
        <v-icon color="primary" size="32" class="mr-3 mt-1">mdi-autorenew</v-icon>
        <div>
          <h4 class="text-h6 font-weight-bold mb-1">Automatic Recurring Contracts</h4>
          <p class="text-body-2 text-grey-darken-1 mb-0">
            All paid bookings automatically become recurring contracts with auto-renewal enabled
          </p>
        </div>
      </div>

      <v-row class="mt-4">
        <v-col cols="12" md="4">
          <div class="info-section">
            <v-icon color="success" size="24" class="mb-2">mdi-check-circle</v-icon>
            <h5 class="text-subtitle-1 font-weight-bold mb-2">How It Works</h5>
            <ul class="info-list">
              <li>Pay for approved booking</li>
              <li>Auto-renewal enabled automatically</li>
              <li>Contract renews on end date</li>
              <li>Saved card charged automatically</li>
              <li>New booking created seamlessly</li>
            </ul>
          </div>
        </v-col>

        <v-col cols="12" md="4">
          <div class="info-section">
            <v-icon color="info" size="24" class="mb-2">mdi-bell-ring</v-icon>
            <h5 class="text-subtitle-1 font-weight-bold mb-2">Before Renewal</h5>
            <ul class="info-list">
              <li>5 email reminders (days 5-1)</li>
              <li>Dashboard countdown banner</li>
              <li>Next charge date displayed</li>
              <li>Amount shown in advance</li>
              <li>Full transparency</li>
            </ul>
          </div>
        </v-col>

        <v-col cols="12" md="4">
          <div class="info-section">
            <v-icon color="primary" size="24" class="mb-2">mdi-cog</v-icon>
            <h5 class="text-subtitle-1 font-weight-bold mb-2">Your Control</h5>
            <ul class="info-list">
              <li>Pause auto-renewal anytime</li>
              <li>Resume when ready</li>
              <li>Cancel recurring permanently</li>
              <li>Current service continues</li>
              <li>No surprise charges</li>
            </ul>
          </div>
        </v-col>
      </v-row>

      <v-alert type="info" variant="tonal" class="mt-4 mb-0">
        <template #text>
          <strong>Important:</strong> Canceling recurring will not affect your current service period. 
          Your care will continue until the scheduled end date, but no new booking will be created automatically.
        </template>
      </v-alert>
    </v-card-text>
  </v-card>
</template>

<style scoped>
.recurring-info-card {
  border: 2px solid #e3f2fd;
  background: linear-gradient(135deg, #f5f9ff 0%, #ffffff 100%);
}

.info-section {
  padding: 16px;
  background: white;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  height: 100%;
}

.info-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.info-list li {
  padding: 4px 0;
  font-size: 14px;
  color: #616161;
  position: relative;
  padding-left: 20px;
}

.info-list li:before {
  content: '•';
  position: absolute;
  left: 0;
  color: #2563eb;
  font-weight: bold;
}
</style>
```

---

## Alternative: Simple Banner Version

If the full card is too large, use this compact version:

```vue
<v-alert 
  type="info" 
  variant="tonal" 
  icon="mdi-autorenew" 
  prominent
  class="mb-6"
>
  <template #title>
    <strong>Automatic Recurring Contracts Enabled</strong>
  </template>
  <template #text>
    <p class="mb-2">
      When you pay for an approved booking, it automatically becomes a recurring contract. 
      Your saved card will be charged when the contract ends, and a new booking will begin automatically.
    </p>
    <p class="mb-2">
      <strong>You'll receive 5 email reminders</strong> before renewal (5, 4, 3, 2, and 1 days before).
    </p>
    <p class="mb-0">
      <strong>You're in control:</strong> You can pause or cancel recurring anytime. 
      Canceling won't affect your current service period.
    </p>
  </template>
</v-alert>
```

---

## Display Location

Place this info card in **ClientPaymentMethods.vue** or the Payment Information page:

1. **Above "Saved Payment Methods"** (shows context before they save a card)
2. **Above "Recurring Contracts"** (shows info right before the management section)

### Recommended Flow:

```
Payment Information Page
├─ Saved Payment Methods
├─ [NEW] Recurring Info Card ← Add here
├─ Recurring Contracts
│  ├─ Active Booking #11
│  ├─ Active Booking #7
│  └─ Cancelled Booking #5
└─ Payment History
```

---

## Key Messages to Emphasize

### ✅ Auto-Enabled
"All paid bookings automatically become recurring contracts"

### 🔔 No Surprises
"5 email reminders before renewal + dashboard countdown"

### 🛡️ Protected Service
"Canceling recurring won't affect your current service period"

### ⚙️ Full Control
"Pause, resume, or cancel anytime"

---

## Client FAQ Section

### Add FAQ accordion below the info card:

```vue
<v-expansion-panels class="mb-6">
  <v-expansion-panel>
    <v-expansion-panel-title>
      <v-icon class="mr-3">mdi-help-circle</v-icon>
      What happens when I pay for a booking?
    </v-expansion-panel-title>
    <v-expansion-panel-text>
      Your booking automatically becomes a recurring contract with auto-renewal enabled. 
      This ensures your care continues seamlessly without interruption.
    </v-expansion-panel-text>
  </v-expansion-panel>

  <v-expansion-panel>
    <v-expansion-panel-title>
      <v-icon class="mr-3">mdi-help-circle</v-icon>
      What if I don't want auto-renewal?
    </v-expansion-panel-title>
    <v-expansion-panel-text>
      You can cancel recurring anytime from the "Recurring Contracts" section below. 
      Your current service will complete as scheduled, but no new booking will be created automatically.
    </v-expansion-panel-text>
  </v-expansion-panel>

  <v-expansion-panel>
    <v-expansion-panel-title>
      <v-icon class="mr-3">mdi-help-circle</v-icon>
      Will I be notified before renewal?
    </v-expansion-panel-title>
    <v-expansion-panel-text>
      Yes! We'll send you 5 email reminders (5, 4, 3, 2, and 1 days before renewal). 
      A countdown banner will also appear on your dashboard.
    </v-expansion-panel-text>
  </v-expansion-panel>

  <v-expansion-panel>
    <v-expansion-panel-title>
      <v-icon class="mr-3">mdi-help-circle</v-icon>
      Can I change my payment method?
    </v-expansion-panel-title>
    <v-expansion-panel-text>
      Yes, you can add or update your payment methods in the "Saved Payment Methods" section above. 
      The default payment method will be used for recurring charges.
    </v-expansion-panel-text>
  </v-expansion-panel>

  <v-expansion-panel>
    <v-expansion-panel-title>
      <v-icon class="mr-3">mdi-help-circle</v-icon>
      What if my payment fails?
    </v-expansion-panel-title>
    <v-expansion-panel-text>
      If a recurring payment fails, you'll receive an immediate notification. 
      No new booking will be created, and you'll need to update your payment method and contact support.
    </v-expansion-panel-text>
  </v-expansion-panel>
</v-expansion-panels>
```

---

## Success Message After Payment

When payment succeeds, show this enhanced message:

```javascript
// In ClientPaymentController.php response:
return response()->json([
    'success' => true,
    'message' => 'Payment successful! Auto-renewal has been enabled for this contract.',
    'payment_intent_id' => $paymentIntent->id,
    'recurring_enabled' => true,
    'next_actions' => [
        'You will receive 5 email reminders before renewal',
        'Manage your recurring contract in Payment Information',
        'You can pause or cancel anytime'
    ]
]);
```

Display in modal:
```vue
<v-alert type="success" prominent class="mb-4">
  <template #title>
    <strong>Payment Successful!</strong>
  </template>
  <template #text>
    <p class="mb-2">
      <v-icon color="success" size="20">mdi-check-circle</v-icon>
      <strong>Auto-renewal has been enabled for this contract.</strong>
    </p>
    <ul class="success-list">
      <li>You'll receive 5 email reminders before renewal</li>
      <li>Manage your contract in Payment Information</li>
      <li>You can pause or cancel anytime</li>
    </ul>
  </template>
</v-alert>
```

---

**Last Updated**: January 10, 2026  
**For**: Payment Information Page UI Enhancement  
**Purpose**: Client education about recurring booking system
