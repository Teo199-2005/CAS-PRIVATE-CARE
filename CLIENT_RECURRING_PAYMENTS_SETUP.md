# Client Recurring Payments & Auto-Pay Setup

## Overview
This implementation enables clients to:
1. **Link/save payment methods** (cards) to their account via Stripe
2. **Enable auto-pay** for ongoing booking contracts
3. **Cancel future payments** manually
4. **View saved payment methods** and manage them

## Architecture

### Backend (Laravel)
- **Controller**: `app/Http/Controllers/ClientPaymentController.php`
  - Uses `Stripe\StripeClient` for API calls
  - Methods:
    - `createSetupIntent()` → Returns client_secret for adding a card (Stripe Elements)
    - `listPaymentMethods()` → Lists all saved payment methods (cards)
    - `attachPaymentMethod()` → Attaches a payment method to customer & sets as default
    - `detachPaymentMethod($pmId)` → Removes a saved payment method
    - `createSubscription($request)` → Creates a recurring Stripe subscription
    - `cancelSubscription($request, $subscriptionId)` → Cancels a subscription

- **Routes**: `routes/api.php`
  ```php
  Route::middleware('auth')->group(function () {
      Route::post('/client/payments/setup-intent', [ClientPaymentController::class, 'createSetupIntent']);
      Route::get('/client/payments/methods', [ClientPaymentController::class, 'listPaymentMethods']);
      Route::post('/client/payments/attach', [ClientPaymentController::class, 'attachPaymentMethod']);
      Route::post('/client/payments/detach/{pmId}', [ClientPaymentController::class, 'detachPaymentMethod']);
      Route::post('/client/subscriptions', [ClientPaymentController::class, 'createSubscription']);
      Route::post('/client/subscriptions/{id}/cancel', [ClientPaymentController::class, 'cancelSubscription']);
  });
  ```

### Frontend (Vue)
- **Component**: `resources/js/components/ClientPaymentMethods.vue`
  - Displays list of saved payment methods
  - Shows card brand, last 4 digits, expiration
  - Inline Stripe Elements form to add new cards
  - Remove button for each saved method
  - Uses Stripe.js `confirmSetup` to save cards without charging

- **Integrated into**: `ClientDashboard.vue` → Payment Information tab

## Stripe Setup Steps

### 1. Add `stripe_customer_id` Column to Users Table
If not already present, run a migration:
```bash
php artisan make:migration add_stripe_customer_id_to_users_table
```

Migration content:
```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('stripe_customer_id')->nullable()->after('email');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('stripe_customer_id');
    });
}
```

Then run:
```bash
php artisan migrate
```

### 2. Create Recurring Products & Prices in Stripe Dashboard

**Option A: Manual (Stripe Dashboard)**
1. Log in to [Stripe Dashboard](https://dashboard.stripe.com/)
2. Go to **Products** → Click **+ New**
3. Create product: "Caregiver Service - Monthly"
4. Add a **Recurring price**: e.g., $10,800/month
5. Copy the **Price ID** (starts with `price_...`)
6. Use this `price_id` when creating subscriptions from the frontend

**Option B: Programmatic (via API)**
```php
$product = $stripe->products->create([
    'name' => 'Monthly Caregiving Service',
]);

$price = $stripe->prices->create([
    'product' => $product->id,
    'unit_amount' => 1080000, // $10,800 in cents
    'currency' => 'usd',
    'recurring' => [
        'interval' => 'month',
    ],
]);

// Use $price->id when creating subscriptions
```

### 3. Enable Auto-Pay for a Booking

When a client wants auto-pay:
1. They add a payment method via `ClientPaymentMethods.vue`
2. Frontend calls `POST /api/client/subscriptions` with `price_id`
3. Backend creates a Stripe subscription using the customer's default payment method
4. Stripe automatically charges the card each billing period

**Frontend Example:**
```javascript
const enableAutoPay = async (priceId) => {
  try {
    const res = await axios.post('/api/client/subscriptions', { price_id: priceId });
    console.log('Subscription created:', res.data.subscription);
    alert('Auto-pay enabled!');
  } catch (e) {
    console.error(e);
    alert('Failed to enable auto-pay');
  }
};
```

### 4. Cancel Auto-Pay

When a client wants to cancel:
1. Get the `subscription_id` (stored from creation response or fetched from Stripe)
2. Frontend calls `POST /api/client/subscriptions/{id}/cancel`
3. Backend cancels the subscription via Stripe API
4. No further charges occur

**Frontend Example:**
```javascript
const cancelAutoPay = async (subscriptionId) => {
  if (!confirm('Cancel auto-pay?')) return;
  try {
    await axios.post(`/api/client/subscriptions/${subscriptionId}/cancel`);
    alert('Auto-pay canceled');
  } catch (e) {
    console.error(e);
    alert('Failed to cancel');
  }
};
```

## User Flow

### Adding a Payment Method
1. Client navigates to **Payment Information** tab
2. Sees "ClientPaymentMethods" component
3. Clicks to add a new card
4. Stripe Elements loads (card input form)
5. Client enters card details
6. Clicks "Save Card"
7. Frontend calls `createSetupIntent()` → gets `client_secret`
8. Stripe confirms setup via `stripe.confirmSetup()`
9. Frontend calls `attachPaymentMethod()` to save payment method to customer
10. Card now appears in saved list (with last 4 digits)

### Enabling Auto-Pay for a Booking
1. When booking is approved/created, show an "Enable Auto-Pay" option
2. Client must have at least 1 saved payment method
3. Click "Enable Auto-Pay" triggers subscription creation
4. Pass `price_id` (from Stripe Product) to backend
5. Backend creates subscription with `customer` + `price_id`
6. Stripe charges immediately (or on next billing cycle based on settings)
7. Future payments occur automatically each month/period

### Canceling Auto-Pay
1. Client sees active subscription in dashboard
2. Clicks "Cancel Auto-Pay"
3. Confirmation prompt
4. Frontend calls `cancelSubscription()`
5. Backend cancels via Stripe API
6. Subscription status changes to `canceled`
7. No further charges

## Database Considerations

### Storing Subscription Info
You may want to add a `subscriptions` table or add columns to `bookings`:
```php
Schema::table('bookings', function (Blueprint $table) {
    $table->string('stripe_subscription_id')->nullable();
    $table->enum('payment_type', ['one-time', 'recurring'])->default('one-time');
    $table->timestamp('next_payment_date')->nullable();
});
```

This helps track which bookings have active subscriptions.

### Webhooks (Recommended)
Set up Stripe webhooks to listen for:
- `invoice.payment_succeeded` → Mark booking as paid for that period
- `invoice.payment_failed` → Notify client of failed payment
- `customer.subscription.deleted` → Update booking status when canceled

Add webhook endpoint in `routes/api.php`:
```php
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handleWebhook']);
```

In `StripeWebhookController`:
```php
public function handleWebhook(Request $request)
{
    $sig = $request->header('Stripe-Signature');
    $payload = $request->getContent();
    $webhookSecret = env('STRIPE_WEBHOOK_SECRET');

    try {
        $event = \Stripe\Webhook::constructEvent($payload, $sig, $webhookSecret);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Invalid signature'], 400);
    }

    switch ($event->type) {
        case 'invoice.payment_succeeded':
            // Update booking payment status
            break;
        case 'invoice.payment_failed':
            // Notify client
            break;
        case 'customer.subscription.deleted':
            // Mark subscription as canceled
            break;
    }

    return response()->json(['received' => true]);
}
```

## Testing

### Test Mode (Stripe)
1. Use test API keys: `pk_test_...` and `sk_test_...`
2. Test card numbers:
   - Success: `4242 4242 4242 4242`
   - Decline: `4000 0000 0000 0002`
   - Authentication required: `4000 0025 0000 3155`
3. Any future expiration date (e.g., 12/34)
4. Any 3-digit CVC

### Local Testing Steps
1. Navigate to client dashboard → Payment Information tab
2. See "Payment Methods" section
3. Click to add a card
4. Enter test card `4242 4242 4242 4242`
5. Submit → Should see card saved with "Visa •••• 4242"
6. Click "Remove" → Card should disappear
7. (Future) Test subscription creation with a test `price_id`

## Production Checklist

- [ ] Replace `VITE_STRIPE_KEY` and `STRIPE_SECRET` with live keys in `.env`
- [ ] Create live products and prices in Stripe Dashboard
- [ ] Set up webhook endpoints and register them in Stripe Dashboard
- [ ] Add `STRIPE_WEBHOOK_SECRET` to `.env`
- [ ] Test subscription creation and cancellation in live mode
- [ ] Add UI for clients to see active subscriptions
- [ ] Add "Enable Auto-Pay" toggle in booking flow
- [ ] Send email notifications for payment success/failure
- [ ] Display next payment date in dashboard

## Security Notes

- Payment method details (card numbers) are **never** stored on your server
- Stripe handles all sensitive card data via Stripe Elements
- Only `payment_method_id` (e.g., `pm_...`) is passed to your backend
- Uses Stripe's `SetupIntent` for off-session future payments
- All API routes are protected with `auth` middleware

## Future Enhancements

- **Proration**: If client changes booking (e.g., hours/duration), prorate the subscription
- **Trial Periods**: Offer 7-day trials before charging
- **Multiple Subscriptions**: Allow clients to have separate subscriptions per booking
- **Payment Method Selection**: Let clients choose which saved card to use for a subscription
- **Payment History**: Show subscription invoices in dashboard
- **Failed Payment Recovery**: Retry logic for failed payments

## Summary

This implementation provides a foundation for:
- **Saving payment methods** (Stripe SetupIntent + Payment Element)
- **Creating recurring subscriptions** (Stripe Subscriptions API)
- **Canceling subscriptions** at any time
- **Auto-charging clients** on a regular schedule

Next steps: wire up subscription creation to specific bookings, add webhooks for invoice events, and build a UI for clients to manage their subscriptions directly from the dashboard.
