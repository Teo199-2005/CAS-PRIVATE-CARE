# Stripe Saved Payment Methods - Quick Reference

## 🚀 Quick Start

### For Testing

1. **Visit Payment Page**: `/payment?booking_id=1`

2. **Add Test Card**:
   - Click "Add Payment Method"
   - Card: `4242 4242 4242 4242`
   - Exp: Any future date
   - CVV: Any 3 digits
   - ZIP: Any 5 digits
   - Click "Add Card"

3. **Make Payment**:
   - Select the card
   - Click "Pay Now"
   - Enter your password
   - Click "Authorize Payment"

### Test Cards

```
✅ Success: 4242 4242 4242 4242
❌ Declined: 4000 0000 0000 9995
🔒 Requires Auth: 4000 0025 0000 3155
```

---

## 📡 API Endpoints

### Get Saved Payment Methods
```
GET /api/client/payment-methods
Headers: Authorization: Bearer {token}

Response:
{
  "success": true,
  "payment_methods": [
    {
      "id": "pm_xxxx",
      "card": {
        "brand": "visa",
        "last4": "4242",
        "exp_month": 12,
        "exp_year": 2025
      }
    }
  ]
}
```

### Create Setup Intent (Add Card)
```
POST /api/stripe/create-setup-intent
Headers: Authorization: Bearer {token}

Response:
{
  "success": true,
  "client_secret": "seti_xxxx_secret_yyyy"
}
```

### Charge Saved Method
```
POST /api/stripe/charge-saved-method
Headers: Authorization: Bearer {token}
Body: {
  "booking_id": 123,
  "payment_method_id": "pm_xxxx",
  "password": "user_password",
  "amount": 450.00
}

Response:
{
  "success": true,
  "message": "Payment processed successfully",
  "payment_intent_id": "pi_xxxx"
}
```

---

## 🎨 Component Usage

### In Blade Template

```blade
<!-- resources/views/payment.blade.php -->
<script src="https://js.stripe.com/v3/"></script>

<div id="payment-page-app">
    <payment-page stripe-key="{{ $stripeKey }}"></payment-page>
</div>
```

### Component Props

```vue
<payment-page 
  stripe-key="pk_test_xxxx"  <!-- Required -->
/>
```

---

## 🔧 Configuration

### Environment Variables
```env
STRIPE_KEY=pk_test_51SjOlT1VtFFyEmvE...
STRIPE_SECRET=sk_test_51SjOlT1VtFFyEmvE...
```

### Config File
```php
// config/stripe.php
return [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
];
```

---

## 🗄️ Database

### Required Columns

**Users Table**:
- `stripe_customer_id` (varchar, nullable, indexed)

**Bookings Table**:
- `payment_status` (varchar, default: 'pending')
- `stripe_payment_intent_id` (varchar, nullable)
- `payment_date` (timestamp, nullable)

### Migration Commands
```bash
php artisan migrate
```

---

## 🎭 Frontend Events

### Component Methods

```javascript
// Load saved cards
await loadSavedPaymentMethods()

// Select a card
selectPaymentMethod(method)

// Open add card modal
openAddCardModal()

// Save new card
await saveNewPaymentMethod()

// Show payment confirmation
handlePayNow()

// Process payment with password
await confirmPaymentWithPassword()
```

### Notifications

```javascript
showNotification('success', 'Card Added', 'Payment method saved successfully')
showNotification('error', 'Payment Failed', 'Card declined')
showNotification('warning', 'No Payment Method', 'Please select a card')
```

---

## 🔐 Security Checklist

- ✅ Password required before charging saved cards
- ✅ Cards stored in Stripe (not your database)
- ✅ All endpoints require authentication
- ✅ User can only access their own payment methods
- ✅ Booking ownership verified
- ✅ HTTPS required in production

---

## 🐛 Common Issues

### "Stripe key is missing"
```php
// Check .env file
STRIPE_KEY=pk_test_...

// Clear config cache
php artisan config:clear
```

### "Payment method not found"
```javascript
// Reload saved methods
await loadSavedPaymentMethods()
```

### "Incorrect password"
```php
// User entered wrong password
// Show error and allow retry
```

### Card Element not mounting
```javascript
// Wait for Stripe.js to load
initStripeWhenReady()
```

---

## 📊 Payment Flow

```
1. Load Page → Load Saved Methods
2. User Selects Card → Click "Pay Now"
3. Show Password Modal → User Enters Password
4. Submit Payment → Verify Password
5. Create PaymentIntent → Charge Card
6. Update Booking → Create Time Tracking
7. Show Success → Redirect to Dashboard
```

---

## 🎯 User Actions

### Add First Card
```
Visit /payment
  ↓
No saved cards → Empty state
  ↓
Click "Add Payment Method"
  ↓
Fill card details
  ↓
Click "Add Card"
  ↓
Card saved ✓
```

### Make Payment
```
Saved cards displayed
  ↓
Select card
  ↓
Click "Pay Now"
  ↓
Enter password
  ↓
Click "Authorize Payment"
  ↓
Payment processed ✓
```

### Add More Cards
```
Click "Add New Payment Method"
  ↓
Fill card details
  ↓
Click "Add Card"
  ↓
New card added ✓
```

---

## 🚀 Deployment

```bash
# 1. Update environment
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...

# 2. Run migrations
php artisan migrate

# 3. Build assets
npm run build

# 4. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 5. Test payment flow
```

---

## 📞 Quick Help

| Problem | Solution |
|---------|----------|
| Card won't save | Check Stripe test mode vs live mode |
| Payment fails | Check Stripe Dashboard logs |
| Modal won't open | Check browser console errors |
| Password rejected | Verify user password is correct |

---

## 🎉 Success Indicators

✅ Can add test card  
✅ Card appears in saved methods  
✅ Can select different cards  
✅ Password modal shows  
✅ Payment processes successfully  
✅ Booking status updates to "paid"  
✅ Redirect to dashboard works  

---

**Status**: ✅ Production Ready  
**Version**: 1.0.0  
**Last Updated**: January 2025
