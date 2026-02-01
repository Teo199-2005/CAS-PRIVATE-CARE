# Stripe Test Cards Reference

## Basic Test Cards

| Card Number | Description |
|-------------|-------------|
| 4242424242424242 | Succeeds and immediately processes |
| 4000000000000002 | Charge is declined (generic_decline) |
| 4000000000009995 | Insufficient funds |
| 4000000000009987 | Lost card |
| 4000000000009979 | Stolen card |
| 4000000000000069 | Expired card |
| 4000000000000127 | Incorrect CVC |
| 4000000000000119 | Processing error |

## 3D Secure Test Cards

| Card Number | Description |
|-------------|-------------|
| 4000002500003155 | Requires 3DS authentication |
| 4000002760003184 | 3DS required, will succeed |
| 4000008260003178 | 3DS required, will fail |
| 4000000000003220 | 3DS2 - required on all transactions |
| 4000000000003063 | 3DS2 - supported but not required |
| 4000000000003055 | 3DS2 - not supported |
| 4000582600003091 | 3DS2 - authentication required (off-session) |

## Dispute Test Cards

| Card Number | Description |
|-------------|-------------|
| 4000000000000259 | Charge succeeds, dispute as fraudulent |
| 4000000000001976 | Charge succeeds, dispute as product not received |
| 4000000000005423 | Charge succeeds, dispute as duplicate |
| 4000000000002685 | Charge succeeds, dispute as subscription canceled |

## International Test Cards

| Card Number | Country | Description |
|-------------|---------|-------------|
| 4000000760000002 | Brazil (BR) | Brazilian card |
| 4000001240000000 | Canada (CA) | Canadian card |
| 4000004840000008 | Mexico (MX) | Mexican card |
| 4000000400000008 | Argentina (AR) | Argentine card |
| 4000005280000002 | Colombia (CO) | Colombian card |

## Stripe Connect Test Cards

| Card Number | Description |
|-------------|-------------|
| 4000000000000077 | Succeeds for Stripe Connect |
| 4000003720000278 | Blocked by Radar for Connect |

## Webhook Testing Cards

| Card Number | Description |
|-------------|-------------|
| 4000000000000341 | Attaching this card to a Customer object succeeds, but attempts to charge fail |
| 4000003800000446 | Succeeds with a risk_level of elevated |

## Testing Instructions

### Standard Payment Test
1. Use expiry date: Any future date (e.g., 12/34)
2. Use CVC: Any 3 digits (e.g., 123)
3. Use ZIP: Any 5 digits (e.g., 10001)

### 3D Secure Test
1. Use a 3DS test card from the table above
2. When redirected to the 3DS page, click "Complete" or "Fail" as needed
3. For automated testing, Stripe will auto-complete in test mode

### Testing in Development
```bash
# Start the Stripe CLI webhook forwarding
stripe listen --forward-to localhost:8000/api/stripe/webhook

# In another terminal, trigger a test event
stripe trigger payment_intent.succeeded
stripe trigger payment_intent.payment_failed
stripe trigger customer.subscription.created
```

### Common Test Scenarios

#### Successful Payment
```javascript
const paymentMethod = await stripe.createPaymentMethod({
  type: 'card',
  card: {
    number: '4242424242424242',
    exp_month: 12,
    exp_year: 2034,
    cvc: '123',
  },
});
```

#### 3DS Required Payment
```javascript
const paymentMethod = await stripe.createPaymentMethod({
  type: 'card',
  card: {
    number: '4000002500003155', // Requires 3DS
    exp_month: 12,
    exp_year: 2034,
    cvc: '123',
  },
});
// Handle the requires_action response
```

#### Declined Payment
```javascript
const paymentMethod = await stripe.createPaymentMethod({
  type: 'card',
  card: {
    number: '4000000000000002', // Will be declined
    exp_month: 12,
    exp_year: 2034,
    cvc: '123',
  },
});
// Handle the card_declined error
```

## Error Codes Reference

| Error Code | Description |
|------------|-------------|
| `card_declined` | The card was declined |
| `expired_card` | The card has expired |
| `incorrect_cvc` | The CVC was incorrect |
| `processing_error` | An error occurred while processing |
| `insufficient_funds` | The card has insufficient funds |
| `authentication_required` | 3DS authentication is required |

## CAS Private Care Specific Notes

### Contractor Payouts (Stripe Connect)
- Use Express accounts for contractor onboarding
- Test bank accounts: Use `000123456789` with routing `110000000`
- Instant payouts are not available in test mode

### Payment Holds
- Create a payment intent with `capture_method: 'manual'`
- Capture within 7 days or the authorization expires

### Recurring Payments
- Create a subscription with a test price ID
- Use `clock_advance` to simulate time passing in test mode
