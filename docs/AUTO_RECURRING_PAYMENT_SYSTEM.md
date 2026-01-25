# Auto-Recurring Payment System

## Overview
When a booking is approved and paid, it automatically becomes a recurring contract. The client can choose to cancel the recurring if they don't want auto-renewal.

## How It Works

### 1. Payment Flow
1. Client clicks **"Pay Now"** on an approved booking
2. Payment confirmation modal shows with saved cards
3. Client selects a card and clicks **"Pay $X,XXX"**
4. Beautiful processing modal shows with spinning animation
5. On success:
   - Green checkmark animation appears
   - Message: "Payment successful! Auto-renewal has been enabled for this contract."
   - Booking status updates to "Paid"
   - Recurring is automatically enabled

### 2. What Gets Updated on Payment
```php
$booking->update([
    'payment_status' => 'paid',
    'recurring_service' => true,    // Auto-enabled!
    'auto_pay_enabled' => true,     // Auto-pay enabled!
    'recurring_status' => 'active', // Ready for auto-renewal
]);
```

### 3. Client Options After Payment
The client can manage their recurring contract from **Payment Information** → **Recurring Contracts**:

- **Pause Auto-Renewal**: Temporarily stop auto-payments
- **Resume Auto-Renewal**: Re-enable auto-payments
- **Cancel Recurring**: Permanently disable auto-renewal
  - Current contract completes as scheduled
  - No new bookings created after this period ends

### 4. Auto-Renewal Process
When the contract end date approaches (handled by `ProcessRecurringBookings` command):

1. **5 days before**: Email reminder sent
2. **4 days before**: Email reminder sent
3. **3 days before**: Email reminder sent
4. **2 days before**: Email reminder sent
5. **1 day before**: Email reminder sent + Dashboard countdown banner
6. **On end date**: 
   - If `recurring_status = 'active'`: Auto-charge saved card and create new booking
   - If `recurring_status = 'cancelled'` or `'paused'`: Contract ends, no auto-charge

### 5. What Happens If Recurring is Cancelled
- Current service period completes normally
- No automatic payment on renewal date
- No new booking is created
- Client must manually book a new service if needed

## UI Components

### Payment Confirmation Modal
- Purple gradient header matching other modals
- Booking summary card with service details
- List of saved payment methods with radio selection
- "Use a Different Card" option to add new payment
- "Pay $X,XXX" button with lock icon

### Payment Processing Modal
- **Processing State**: Spinning purple loader with "Processing payment..."
- **Success State**: Green checkmark animation with "Payment successful! Auto-renewal has been enabled."
- **Error State**: Red X animation with error message and "Try Again" button

### Recurring Contracts Manager
Located in Payment Information section:
- Shows all paid bookings with recurring status
- Progress bar for current service period
- Days remaining indicator
- Next charge date display
- Action buttons: Pause, Resume, Cancel

## Database Fields Used
- `recurring_service` (boolean): Whether booking has recurring enabled
- `auto_pay_enabled` (boolean): Whether auto-charge is enabled
- `recurring_status` (string): 'active', 'paused', or 'cancelled'
- `recurring_count` (int): Number of times renewed
- `next_payment_date` (datetime): When next auto-charge will occur
- `last_recurring_charge_date` (datetime): When last auto-charged

## API Endpoints

### Payment
- `POST /api/stripe/charge-saved-method` - Charge saved card and enable recurring
- `GET /api/stripe/payment-methods` - List saved payment methods

### Recurring Management
- `GET /client/recurring` - Get all recurring bookings
- `POST /client/recurring/{id}/pause` - Pause auto-renewal
- `POST /client/recurring/{id}/resume` - Resume auto-renewal  
- `POST /client/recurring/{id}/cancel` - Cancel recurring (contract ends on scheduled date)

## Testing
1. Create a new booking and get it approved
2. Click "Pay Now" on the approved booking
3. Select your saved card and pay
4. Verify:
   - Payment processes successfully
   - Success animation shows with recurring message
   - Booking shows "Paid" status
   - Recurring Contracts section now shows this booking
5. Try pausing/cancelling the recurring to test those flows
