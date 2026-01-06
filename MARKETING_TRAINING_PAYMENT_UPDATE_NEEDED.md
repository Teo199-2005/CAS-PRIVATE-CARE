# Marketing & Training Center Payment Integration Status

## 🔍 Current Status (NEEDS UPDATE)

### Marketing Dashboard
**File:** `resources/js/components/MarketingDashboard.vue`
**Current Payment Setup:**
- ❌ Using old card-based payment methods (line 280-340)
- ❌ Manual bank account entry (not Stripe Connect)
- ❌ "Add Payment Method" dialog with card forms
- ❌ Not using standardized bank onboarding page

**Should Have:**
- ✅ "Connect Payout Method" button (like caregivers)
- ✅ Redirect to `/connect-bank-account-marketing`
- ✅ Same Stripe Connect integration
- ✅ Same beautiful two-column design

---

### Training Center Dashboard
**File:** `resources/js/components/TrainingDashboard.vue`
**Current Payment Setup:**
- ❓ Need to check if similar to marketing dashboard
- ❓ Likely using old payment methods too

**Should Have:**
- ✅ "Connect Payout Method" button (like caregivers)
- ✅ Redirect to `/connect-bank-account-training`
- ✅ Same Stripe Connect integration
- ✅ Same beautiful two-column design

---

## 💰 Payment Flow Explanation

### How They Get Paid

#### Marketing Staff Commission
**Rate:** $1/hour per booking they referred
**Example:**
- Client books 360 hours of service
- Marketing staff who referred = $360 commission

**Payout Flow:**
```
Client Pays $16,200
    ↓
System calculates: 360hrs × $1/hr = $360
    ↓
Admin approves marketing commission
    ↓
Stripe Transfer: $360 → Marketing's Connect Account
    ↓
Marketing receives in bank (2-3 days)
```

#### Training Center Commission
**Rate:** $2/hour per caregiver they trained
**Example:**
- Caregiver works 360 hours
- Training center that certified them = $720 commission

**Payout Flow:**
```
Client Pays $16,200
    ↓
System calculates: 360hrs × $2/hr = $720
    ↓
Admin approves training commission
    ↓
Stripe Transfer: $720 → Training Center's Connect Account
    ↓
Training center receives in bank (2-3 days)
```

---

## 🔧 Required Updates

### 1. Marketing Dashboard Payment Section

**Replace Lines 274-350 with:**
```vue
<!-- Payment Information Section -->
<div v-if="currentSection === 'payment'">
  <v-card elevation="0">
    <v-card-title class="card-header pa-8">
      <div class="d-flex align-center">
        <v-icon size="40" color="primary" class="mr-4">mdi-credit-card</v-icon>
        <div>
          <div class="section-title primary--text">Payment Information</div>
          <div class="text-caption text-grey">Connect your bank account to receive commission payouts</div>
        </div>
      </div>
    </v-card-title>
    <v-card-text class="pa-8">
      <v-alert
        color="info"
        variant="tonal"
        prominent
        class="mb-6"
      >
        <div class="font-weight-bold mb-2">Connect Payout Method</div>
        <p class="mb-4">
          Connect your preferred payout method via Stripe to receive weekly commission payments.<br>
          Your payment information is securely encrypted and never shared.
        </p>
        <v-btn
          color="primary"
          size="large"
          prepend-icon="mdi-bank"
          href="/connect-bank-account-marketing"
          elevation="3"
          class="text-none font-weight-bold"
        >
          Connect Bank Account
        </v-btn>
      </v-alert>

      <!-- Commission Summary -->
      <v-card elevation="2" class="mb-6">
        <v-card-title class="pa-6 bg-success">
          <span class="section-title success--text">Commission Summary</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12" md="4">
              <div class="text-center py-4">
                <span class="summary-label">Total Earned</span>
                <div class="summary-value success--text">${{ totalCommission }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="4">
              <div class="text-center py-4">
                <span class="summary-label">This Month</span>
                <div class="summary-value primary--text">${{ monthlyCommission }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="4">
              <div class="text-center py-4">
                <span class="summary-label">Last Payout</span>
                <div class="summary-value grey--text">${{ lastPayoutAmount || '0.00' }}</div>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-card-text>
  </v-card>
</div>
```

---

### 2. Training Dashboard Payment Section

**Same update needed - Replace payment section with:**
```vue
<!-- Payment Information Section -->
<div v-if="currentSection === 'payment'">
  <v-card elevation="0">
    <v-card-title class="card-header pa-8">
      <div class="d-flex align-center">
        <v-icon size="40" color="primary" class="mr-4">mdi-credit-card</v-icon>
        <div>
          <div class="section-title primary--text">Payment Information</div>
          <div class="text-caption text-grey">Connect your bank account to receive training commission payouts</div>
        </div>
      </div>
    </v-card-title>
    <v-card-text class="pa-8">
      <v-alert
        color="info"
        variant="tonal"
        prominent
        class="mb-6"
      >
        <div class="font-weight-bold mb-2">Connect Payout Method</div>
        <p class="mb-4">
          Connect your preferred payout method via Stripe to receive weekly training commission payments.<br>
          Your payment information is securely encrypted and never shared.
        </p>
        <v-btn
          color="primary"
          size="large"
          prepend-icon="mdi-bank"
          href="/connect-bank-account-training"
          elevation="3"
          class="text-none font-weight-bold"
        >
          Connect Bank Account
        </v-btn>
      </v-alert>

      <!-- Commission Summary -->
      <v-card elevation="2" class="mb-6">
        <v-card-title class="pa-6 bg-success">
          <span class="section-title success--text">Training Commission Summary</span>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12" md="4">
              <div class="text-center py-4">
                <span class="summary-label">Total Earned</span>
                <div class="summary-value success--text">${{ totalCommission }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="4">
              <div class="text-center py-4">
                <span class="summary-label">This Month</span>
                <div class="summary-value primary--text">${{ monthlyCommission }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="4">
              <div class="text-center py-4">
                <span class="summary-label">Last Payout</span>
                <div class="summary-value grey--text">${{ lastPayoutAmount || '0.00' }}</div>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-card-text>
  </v-card>
</div>
```

---

### 3. Create Marketing Bank Onboarding Route

**Add to `routes/web.php`:**
```php
// Marketing Bank Onboarding
Route::get('/connect-bank-account-marketing', function () {
    $user = auth()->user();
    if ($user->user_type !== 'marketing') {
        return redirect('/login');
    }
    return view('connect-bank-account-marketing');
})->name('marketing.connect.bank');
```

---

### 4. Create Training Bank Onboarding Route

**Add to `routes/web.php`:**
```php
// Training Center Bank Onboarding
Route::get('/connect-bank-account-training', function () {
    $user = auth()->user();
    if (!in_array($user->user_type, ['training', 'training_center'])) {
        return redirect('/login');
    }
    return view('connect-bank-account-training');
})->name('training.connect.bank');
```

---

### 5. Create Marketing Bank Onboarding Page

**File:** `resources/views/connect-bank-account-marketing.blade.php`
```php
@extends('layouts.app')

@section('content')
<div id="marketing-bank-onboarding-app"></div>
@endsection

@push('scripts')
<script>
window.userRole = 'marketing';
window.stripeKey = "{{ config('stripe.publishable_key') }}";
</script>
@vite(['resources/js/app.js'])
@endpush
```

---

### 6. Create Training Bank Onboarding Page

**File:** `resources/views/connect-bank-account-training.blade.php`
```php
@extends('layouts.app')

@section('content')
<div id="training-bank-onboarding-app"></div>
@endsection

@push('scripts')
<script>
window.userRole = 'training';
window.stripeKey = "{{ config('stripe.publishable_key') }}";
</script>
@vite(['resources/js/app.js'])
@endpush
```

---

### 7. Update CustomBankOnboarding Component

**Make it role-aware:**
```vue
<script setup>
const userRole = window.userRole || 'caregiver'; // 'caregiver', 'marketing', 'training'

const roleText = computed(() => {
  const roles = {
    caregiver: {
      title: 'Connect Your Payout Method',
      subtitle: 'Set up your bank account to receive weekly payments for your care services.',
      benefitTitle: 'Weekly Payouts',
      benefitDesc: 'Automatic payments every Friday'
    },
    marketing: {
      title: 'Connect Your Commission Account',
      subtitle: 'Set up your bank account to receive weekly commission payments.',
      benefitTitle: 'Commission Payouts',
      benefitDesc: 'Receive $1/hour for each referral'
    },
    training: {
      title: 'Connect Your Commission Account',
      subtitle: 'Set up your bank account to receive weekly training commission payments.',
      benefitTitle: 'Training Commissions',
      benefitDesc: 'Receive $2/hour per trained caregiver'
    }
  };
  return roles[userRole];
});

// API endpoint changes based on role
const submitBankDetails = async () => {
  const endpoints = {
    caregiver: '/api/stripe/connect-bank-account',
    marketing: '/api/stripe/connect-bank-account-marketing',
    training: '/api/stripe/connect-bank-account-training'
  };
  
  const response = await axios.post(endpoints[userRole], bankDetails);
  
  const redirects = {
    caregiver: '/caregiver-dashboard?section=payment&success=true',
    marketing: '/marketing/dashboard-vue?section=payments&success=true',
    training: '/training/dashboard-vue?section=payments&success=true'
  };
  
  if (response.data.success) {
    window.location.href = redirects[userRole];
  }
};
</script>
```

---

### 8. Create Marketing Connect API

**Add to `app/Http/Controllers/StripeController.php`:**
```php
public function connectMarketingBankAccount(Request $request)
{
    $validated = $request->validate([
        'accountHolderName' => 'required|string|max:255',
        'routingNumber' => 'required|digits:9',
        'accountNumber' => 'required|string|min:4|max:17',
        'accountType' => 'required|in:checking,savings'
    ]);

    $user = auth()->user();
    
    $result = $this->stripePaymentService->addMarketingBankAccount($user, [
        'accountHolderName' => $validated['accountHolderName'],
        'routingNumber' => $validated['routingNumber'],
        'accountNumber' => $validated['accountNumber'],
        'accountType' => strtolower($validated['accountType'])
    ]);

    return response()->json($result);
}
```

---

### 9. Create Training Connect API

**Add to `app/Http/Controllers/StripeController.php`:**
```php
public function connectTrainingBankAccount(Request $request)
{
    $validated = $request->validate([
        'accountHolderName' => 'required|string|max:255',
        'routingNumber' => 'required|digits:9',
        'accountNumber' => 'required|string|min:4|max:17',
        'accountType' => 'required|in:checking,savings'
    ]);

    $user = auth()->user();
    
    $result = $this->stripePaymentService->addTrainingBankAccount($user, [
        'accountHolderName' => $validated['accountHolderName'],
        'routingNumber' => $validated['routingNumber'],
        'accountNumber' => $validated['accountNumber'],
        'accountType' => strtolower($validated['accountType'])
    ]);

    return response()->json($result);
}
```

---

### 10. Add Service Methods

**Add to `app/Services/StripePaymentService.php`:**
```php
public function addMarketingBankAccount(User $marketingUser, array $bankData): array
{
    try {
        // Create Connect account for marketing user
        if (!$marketingUser->stripe_connect_id) {
            $account = \Stripe\Account::create([
                'type' => 'express',
                'country' => 'US',
                'email' => $marketingUser->email,
                'capabilities' => [
                    'transfers' => ['requested' => true]
                ]
            ]);
            
            $marketingUser->update(['stripe_connect_id' => $account->id]);
        }

        // Create bank token
        $token = \Stripe\Token::create([
            'bank_account' => [
                'country' => 'US',
                'currency' => 'usd',
                'account_holder_name' => $bankData['accountHolderName'],
                'routing_number' => $bankData['routingNumber'],
                'account_number' => $bankData['accountNumber'],
            ],
        ]);

        // Add external account
        $externalAccount = \Stripe\Account::createExternalAccount(
            $marketingUser->stripe_connect_id,
            ['external_account' => $token->id]
        );

        return [
            'success' => true,
            'message' => 'Bank account connected successfully',
            'bank_account_id' => $externalAccount->id
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

public function addTrainingBankAccount(User $trainingUser, array $bankData): array
{
    try {
        // Create Connect account for training center
        if (!$trainingUser->stripe_connect_id) {
            $account = \Stripe\Account::create([
                'type' => 'express',
                'country' => 'US',
                'email' => $trainingUser->email,
                'capabilities' => [
                    'transfers' => ['requested' => true]
                ]
            ]);
            
            $trainingUser->update(['stripe_connect_id' => $account->id]);
        }

        // Create bank token
        $token = \Stripe\Token::create([
            'bank_account' => [
                'country' => 'US',
                'currency' => 'usd',
                'account_holder_name' => $bankData['accountHolderName'],
                'routing_number' => $bankData['routingNumber'],
                'account_number' => $bankData['accountNumber'],
            ],
        ]);

        // Add external account
        $externalAccount = \Stripe\Account::createExternalAccount(
            $trainingUser->stripe_connect_id,
            ['external_account' => $token->id]
        ];

        return [
            'success' => true,
            'message' => 'Bank account connected successfully',
            'bank_account_id' => $externalAccount->id
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
```

---

## ✅ Benefits of Updating

### Consistency
- ✅ All portals use same payment system
- ✅ Same beautiful design across platform
- ✅ Same security standards

### Security
- ✅ Stripe Connect (not manual card entry)
- ✅ Tokenized bank accounts
- ✅ PCI compliant

### User Experience
- ✅ Professional branded page
- ✅ Simple one-time setup
- ✅ Automatic payouts

### Admin Benefits
- ✅ One-click payouts for all roles
- ✅ Consistent payout workflow
- ✅ Unified financial tracking

---

## 📋 Implementation Checklist

- [ ] Update MarketingDashboard.vue payment section
- [ ] Update TrainingDashboard.vue payment section
- [ ] Create `/connect-bank-account-marketing` route
- [ ] Create `/connect-bank-account-training` route
- [ ] Create marketing bank onboarding blade view
- [ ] Create training bank onboarding blade view
- [ ] Make CustomBankOnboarding.vue role-aware
- [ ] Add connectMarketingBankAccount() controller method
- [ ] Add connectTrainingBankAccount() controller method
- [ ] Add addMarketingBankAccount() service method
- [ ] Add addTrainingBankAccount() service method
- [ ] Register new API routes
- [ ] Update app.js to mount marketing/training bank apps
- [ ] Build frontend (npm run build)
- [ ] Test marketing bank connection
- [ ] Test training bank connection

---

**Status:** ⚠️ **NEEDS UPDATE**
**Priority:** Medium
**Estimated Time:** 2-3 hours
