<template>
  <div class="custom-onboarding-container">
    <v-container fluid class="fill-height pa-0">
      <v-row no-gutters class="fill-height">
        <!-- Left Column - Your Branding (Dark Blue) -->
        <v-col cols="12" md="5" class="left-column d-flex flex-column justify-center align-center pa-8">
          <div class="branding-content text-center">
            <div class="logo-container mb-8">
              <img src="/logo.png" alt="CAS Private Care" class="logo" />
            </div>
            
            <h1 class="welcome-title mb-4">{{ roleConfig.title }}</h1>
            <p class="welcome-subtitle mb-8">
              {{ roleConfig.subtitle }}
            </p>
            
            <v-list class="benefits-list" lines="two">
              <v-list-item prepend-icon="mdi-lock-check" class="px-0">
                <v-list-item-title class="text-white font-weight-medium">Bank-Level Security</v-list-item-title>
                <v-list-item-subtitle class="text-grey-lighten-1">Your data is encrypted and protected</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item prepend-icon="mdi-clock-fast" class="px-0">
                <v-list-item-title class="text-white font-weight-medium">Weekly Payouts</v-list-item-title>
                <v-list-item-subtitle class="text-grey-lighten-1">Automatic payments every Friday</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item prepend-icon="mdi-cash" class="px-0">
                <v-list-item-title class="text-white font-weight-medium">{{ roleConfig.rateText }}</v-list-item-title>
                <v-list-item-subtitle class="text-grey-lighten-1">Competitive commission structure</v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </div>
        </v-col>

        <!-- Right Column - Your Custom Form (White) -->
        <v-col cols="12" md="7" class="right-column d-flex flex-column justify-center pa-8">
          <div class="form-content">
            <h2 class="form-title mb-2">Bank Account Information</h2>
            <p class="form-subtitle text-grey-darken-1 mb-6">
              Enter your bank details below. This information is securely transmitted to Stripe.
            </p>

            <v-card elevation="0" class="form-card pa-6" :loading="loading">
              <!-- Payout Method Tabs -->
              <div class="mb-6">
                <h3 class="text-h6 mb-4 text-grey-darken-3">Select Payout Method</h3>
                <v-tabs
                  v-model="selectedPayoutMethod"
                  color="primary"
                  align-tabs="start"
                  class="payout-tabs mb-6"
                >
                  <v-tab value="card">
                    <v-icon start>mdi-credit-card</v-icon>
                    Card
                    <div class="card-logos ml-2">
                      <img src="https://js.stripe.com/v3/fingerprinted/img/visa-729c05c240c4bdb47b03ac81d9945bfe.svg" alt="Visa" height="16" class="mx-1">
                      <img src="https://js.stripe.com/v3/fingerprinted/img/mastercard-4d8844094130711885b5e41b28c9848f.svg" alt="Mastercard" height="16" class="mx-1">
                      <img src="https://js.stripe.com/v3/fingerprinted/img/amex-a49b82f46c5cd6a96a6e418a6ca1717c.svg" alt="Amex" height="16" class="mx-1">
                    </div>
                  </v-tab>
                  <v-tab value="alipay">
                    <v-icon start>mdi-currency-cny</v-icon>
                    Alipay
                  </v-tab>
                  <v-tab value="cashapp">
                    <v-icon start>mdi-cash</v-icon>
                    Cash App Pay
                  </v-tab>
                  <v-tab value="bank">
                    <v-icon start>mdi-bank</v-icon>
                    Bank
                  </v-tab>
                </v-tabs>
              </div>

              <!-- Bank Account Form (shows when bank is selected) -->
              <v-form ref="form" v-model="valid" @submit.prevent="submitBankDetails" v-if="selectedPayoutMethod === 'bank'">
                <h3 class="text-h6 mb-4 text-grey-darken-3">Bank Account Information</h3>
                
                <!-- Account Holder Name -->
                <v-text-field
                  v-model="bankDetails.accountHolderName"
                  label="Account Holder Name"
                  placeholder="John Doe"
                  variant="outlined"
                  prepend-inner-icon="mdi-account"
                  :rules="[rules.required]"
                  class="mb-4"
                ></v-text-field>

                <!-- Routing Number -->
                <v-text-field
                  v-model="bankDetails.routingNumber"
                  label="Routing Number"
                  placeholder="110000000"
                  variant="outlined"
                  prepend-inner-icon="mdi-bank"
                  :rules="[rules.required, rules.routing]"
                  maxlength="9"
                  class="mb-4"
                  hint="9-digit routing number"
                ></v-text-field>

                <!-- Account Number -->
                <v-text-field
                  v-model="bankDetails.accountNumber"
                  label="Account Number"
                  placeholder="000123456789"
                  variant="outlined"
                  prepend-inner-icon="mdi-numeric"
                  :rules="[rules.required, rules.account]"
                  class="mb-4"
                ></v-text-field>

                <!-- Confirm Account Number -->
                <v-text-field
                  v-model="bankDetails.confirmAccountNumber"
                  label="Confirm Account Number"
                  placeholder="000123456789"
                  variant="outlined"
                  prepend-inner-icon="mdi-numeric"
                  :rules="[rules.required, rules.match]"
                  class="mb-4"
                ></v-text-field>

                <!-- Account Type -->
                <v-select
                  v-model="bankDetails.accountType"
                  label="Account Type"
                  :items="['Checking', 'Savings']"
                  variant="outlined"
                  prepend-inner-icon="mdi-wallet"
                  :rules="[rules.required]"
                  class="mb-4"
                ></v-select>

                <!-- Terms Agreement -->
                <v-checkbox
                  v-model="bankDetails.agreeToTerms"
                  :rules="[rules.mustAgree]"
                  class="mb-4"
                >
                  <template v-slot:label>
                    <div class="text-body-2">
                      I authorize Stripe to debit my account for any future payouts.
                      <a href="#" class="text-primary">Terms & Conditions</a>
                    </div>
                  </template>
                </v-checkbox>

                <!-- Error Alert -->
                <v-alert v-if="error" type="error" variant="tonal" closable @click:close="error = null" class="mb-4">
                  {{ error }}
                </v-alert>

                <!-- Submit Button -->
                <v-btn
                  type="submit"
                  color="primary"
                  size="large"
                  block
                  :loading="loading"
                  :disabled="!valid"
                  class="text-none"
                >
                  <v-icon start>mdi-check-circle</v-icon>
                  Connect Bank Account
                </v-btn>
              </v-form>

              <!-- Coming Soon Message for Other Methods -->
              <div v-else>
                <!-- Card Debit Form -->
                <div v-if="selectedPayoutMethod === 'card'">
                  <h3 class="text-h6 mb-4 text-grey-darken-3">Debit Card Information</h3>
                  <div>
                    <v-text-field
                      v-model="cardDetails.cardholderName"
                      label="Cardholder Name"
                      placeholder="John Doe"
                      variant="outlined"
                      prepend-inner-icon="mdi-account"
                      :error-messages="cardErrors.cardholderName"
                      @blur="validateCardField('cardholderName')"
                      class="mb-4"
                    ></v-text-field>

                    <v-text-field
                      v-model="cardDetails.cardNumber"
                      label="Card Number"
                      placeholder="4242 4242 4242 4242"
                      variant="outlined"
                      prepend-inner-icon="mdi-credit-card"
                      :error-messages="cardErrors.cardNumber"
                      @blur="validateCardField('cardNumber')"
                      @input="formatCardNumber"
                      class="mb-4"
                    ></v-text-field>

                    <v-row>
                      <v-col cols="6">
                        <v-text-field
                          v-model="cardDetails.expiryDate"
                          label="Expiry Date"
                          placeholder="MM/YY"
                          variant="outlined"
                          prepend-inner-icon="mdi-calendar"
                          :error-messages="cardErrors.expiryDate"
                          @blur="validateCardField('expiryDate')"
                          @input="formatExpiryDate"
                          class="mb-4"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="6">
                        <v-text-field
                          v-model="cardDetails.cvv"
                          label="CVV"
                          placeholder="123 or 1234"
                          variant="outlined"
                          prepend-inner-icon="mdi-lock"
                          type="password"
                          class="mb-4"
                          hint="3 digits for Visa/MC, 4 for Amex"
                          persistent-hint
                          :error-messages="cardErrors.cvv"
                          @input="formatCvv"
                          @blur="validateCardField('cvv')"
                        ></v-text-field>
                      </v-col>
                    </v-row>

                    <v-alert v-if="error" type="error" variant="tonal" closable @click:close="error = null" class="mb-4">
                      {{ error }}
                    </v-alert>

                    <v-btn
                      color="primary"
                      size="large"
                      block
                      :loading="loading"
                      :disabled="!isCardFormValid"
                      class="text-none"
                      @click="submitCardDetails"
                    >
                      <v-icon start>mdi-check-circle</v-icon>
                      Connect Debit Card
                    </v-btn>
                  </div>
                </div>

                <!-- Alipay Form -->
                <div v-else-if="selectedPayoutMethod === 'alipay'">
                  <h3 class="text-h6 mb-4 text-grey-darken-3">Alipay Account Information</h3>
                  <v-form ref="alipayForm" v-model="alipayValid" @submit.prevent="submitAlipayDetails">
                    <v-text-field
                      v-model="alipayDetails.accountName"
                      label="Account Name"
                      placeholder="Your Name"
                      variant="outlined"
                      prepend-inner-icon="mdi-account"
                      :rules="[rules.required]"
                      class="mb-4"
                    ></v-text-field>

                    <v-text-field
                      v-model="alipayDetails.alipayId"
                      label="Alipay ID / Email / Phone"
                      placeholder="example@email.com or +86 123 4567 8901"
                      variant="outlined"
                      prepend-inner-icon="mdi-currency-cny"
                      :rules="[rules.required]"
                      class="mb-4"
                    ></v-text-field>

                    <v-alert type="info" variant="tonal" class="mb-4">
                      <div class="text-body-2">
                        Enter your Alipay ID, registered email, or mobile number
                      </div>
                    </v-alert>

                    <v-alert v-if="error" type="error" variant="tonal" closable @click:close="error = null" class="mb-4">
                      {{ error }}
                    </v-alert>

                    <v-btn
                      type="submit"
                      color="primary"
                      size="large"
                      block
                      :loading="loading"
                      :disabled="!alipayValid"
                      class="text-none"
                    >
                      <v-icon start>mdi-check-circle</v-icon>
                      Connect Alipay Account
                    </v-btn>
                  </v-form>
                </div>

                <!-- Cash App Form -->
                <div v-else-if="selectedPayoutMethod === 'cashapp'">
                  <h3 class="text-h6 mb-4 text-grey-darken-3">Cash App Information</h3>
                  <v-form ref="cashappForm" v-model="cashappValid" @submit.prevent="submitCashAppDetails">
                    <v-text-field
                      v-model="cashappDetails.accountName"
                      label="Account Name"
                      placeholder="Your Name"
                      variant="outlined"
                      prepend-inner-icon="mdi-account"
                      :rules="[rules.required]"
                      class="mb-4"
                    ></v-text-field>

                    <v-text-field
                      v-model="cashappDetails.cashtag"
                      label="$Cashtag"
                      placeholder="$YourCashtag"
                      variant="outlined"
                      prepend-inner-icon="mdi-cash"
                      :rules="[rules.required, rules.cashtag]"
                      class="mb-4"
                      prefix="$"
                    ></v-text-field>

                    <v-alert type="info" variant="tonal" class="mb-4">
                      <div class="text-body-2">
                        Enter your Cash App $Cashtag (e.g., $JohnDoe)
                      </div>
                    </v-alert>

                    <v-alert v-if="error" type="error" variant="tonal" closable @click:close="error = null" class="mb-4">
                      {{ error }}
                    </v-alert>

                    <v-btn
                      type="submit"
                      color="primary"
                      size="large"
                      block
                      :loading="loading"
                      :disabled="!cashappValid"
                      class="text-none"
                    >
                      <v-icon start>mdi-check-circle</v-icon>
                      Connect Cash App
                    </v-btn>
                  </v-form>
                </div>
              </div>
            </v-card>

            <!-- Security Notice -->
            <v-card elevation="0" color="blue-lighten-5" class="mt-4 pa-4">
              <div class="d-flex align-center">
                <v-icon size="32" color="blue-darken-2" class="mr-3">mdi-shield-lock</v-icon>
                <div class="text-body-2">
                  <div class="font-weight-bold text-blue-darken-4 mb-1">Your data is secure</div>
                  <div class="text-blue-darken-2">
                    We use bank-level encryption. Your account details are never stored on our servers.
                  </div>
                </div>
              </div>
            </v-card>

            <!-- Back Button -->
            <div class="text-center mt-4">
              <v-btn variant="text" prepend-icon="mdi-arrow-left" @click="goBack">
                Back to Dashboard
              </v-btn>
            </div>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

// Role detection
const userRole = window.userRole || 'caregiver'; // Set by blade template

// Role-specific configuration
const roleConfig = computed(() => {
  const configs = {
    caregiver: {
      title: 'Connect Your Payout Method',
      subtitle: 'Set up your bank account to receive weekly payments for your care services.',
      rateText: 'Earnings based on hours worked',
      apiEndpoint: '/api/stripe/connect-payout-method',
      redirectUrl: '/caregiver/dashboard-vue?section=payment&success=true'
    },
    marketing: {
      title: 'Connect Your Commission Payout',
      subtitle: 'Set up your bank account to receive weekly commission payments for client referrals.',
      rateText: '$1.00 per hour per referral',
      apiEndpoint: '/api/stripe/connect-bank-account-marketing',
      redirectUrl: '/marketing/dashboard-vue?section=payments&success=true'
    },
    training: {
      title: 'Connect Your Commission Payout',
      subtitle: 'Set up your bank account to receive weekly commission payments for trained caregivers.',
      rateText: '$2.00 per hour per trained caregiver',
      apiEndpoint: '/api/stripe/connect-bank-account-training',
      redirectUrl: '/training/dashboard-vue?section=payments&success=true'
    }
  };
  return configs[userRole] || configs.caregiver;
});

const form = ref(null);
const cardForm = ref(null);
const alipayForm = ref(null);
const cashappForm = ref(null);

const valid = ref(false);
const cardValid = ref(false);
const alipayValid = ref(false);
const cashappValid = ref(false);

const loading = ref(false);
const error = ref(null);
const selectedPayoutMethod = ref('bank'); // Default to bank

// Card form validation errors (manual validation - no Vuetify rules)
const cardErrors = ref({
  cardholderName: '',
  cardNumber: '',
  expiryDate: '',
  cvv: ''
});

const bankDetails = ref({
  accountHolderName: '',
  routingNumber: '',
  accountNumber: '',
  confirmAccountNumber: '',
  accountType: 'Checking',
  agreeToTerms: false
});

const cardDetails = ref({
  cardholderName: '',
  cardNumber: '',
  expiryDate: '',
  cvv: ''
});

const alipayDetails = ref({
  accountName: '',
  alipayId: ''
});

const cashappDetails = ref({
  accountName: '',
  cashtag: ''
});

const rules = {
  required: value => !!value || 'This field is required',
  routing: value => /^\d{9}$/.test(value) || 'Must be 9 digits',
  account: value => /^\d{4,17}$/.test(value) || 'Must be 4-17 digits',
  match: value => value === bankDetails.value.accountNumber || 'Account numbers must match',
  mustAgree: value => value === true || 'You must agree to continue',
  cardNumber: value => /^\d{4}\s\d{4}\s\d{4}\s\d{4}$/.test(value) || 'Invalid card number',
  expiryDate: value => /^(0[1-9]|1[0-2])\/\d{2}$/.test(value) || 'Format: MM/YY',
  cvv: value => {
    if (!value) return 'Enter your CVV';
    const digits = value.replace(/\D/g, '');
    const len = digits.length;
    // Accept 3 or 4 digits - return true immediately to stop validation
    if (len === 3 || len === 4) return true;
    // If less than 3, ask for more
    if (len < 3) return `Enter ${3 - len} more digit${3 - len > 1 ? 's' : ''}`;
    // If more than 4, say too many
    return 'CVV too long (max 4 digits)';
  },
  cashtag: value => /^[a-zA-Z0-9]{1,20}$/.test(value) || 'Invalid $Cashtag'
};

// Format card number with spaces
const formatCardNumber = (event) => {
  let value = cardDetails.value.cardNumber.replace(/\s/g, '');
  let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
  cardDetails.value.cardNumber = formattedValue;
};

// Format expiry date MM/YY
const formatExpiryDate = (event) => {
  let value = cardDetails.value.expiryDate.replace(/\D/g, '');
  if (value.length >= 2) {
    value = value.slice(0, 2) + '/' + value.slice(2, 4);
  }
  cardDetails.value.expiryDate = value;
};

// Format CVV - only allow digits, max 4 characters
const formatCvv = () => {
  const digits = cardDetails.value.cvv.replace(/\D/g, '').slice(0, 4);
  cardDetails.value.cvv = digits;
};

// Validate individual card field
const validateCardField = (field) => {
  const value = cardDetails.value[field];
  
  switch (field) {
    case 'cardholderName':
      cardErrors.value.cardholderName = value ? '' : 'Cardholder name is required';
      break;
    case 'cardNumber':
      const cleanNumber = (value || '').replace(/\s/g, '');
      if (!cleanNumber) {
        cardErrors.value.cardNumber = 'Card number is required';
      } else if (!/^\d{16}$/.test(cleanNumber)) {
        cardErrors.value.cardNumber = 'Enter a valid 16-digit card number';
      } else {
        cardErrors.value.cardNumber = '';
      }
      break;
    case 'expiryDate':
      if (!value) {
        cardErrors.value.expiryDate = 'Expiry date is required';
      } else if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(value)) {
        cardErrors.value.expiryDate = 'Format: MM/YY';
      } else {
        cardErrors.value.expiryDate = '';
      }
      break;
    case 'cvv':
      if (!value) {
        cardErrors.value.cvv = 'CVV is required';
      } else {
        const digits = value.replace(/\D/g, '');
        // ACCEPT BOTH 3 AND 4 DIGITS
        if (digits.length === 3 || digits.length === 4) {
          cardErrors.value.cvv = '';
        } else if (digits.length < 3) {
          cardErrors.value.cvv = 'CVV must be at least 3 digits';
        } else {
          cardErrors.value.cvv = 'CVV too long';
        }
      }
      break;
  }
};

// Check if card form is valid (for button enable/disable)
const isCardFormValid = computed(() => {
  const { cardholderName, cardNumber, expiryDate, cvv } = cardDetails.value;
  
  // Must have all fields
  if (!cardholderName?.trim()) return false;
  if (!cardNumber?.trim()) return false;
  if (!expiryDate?.trim()) return false;
  if (!cvv?.trim()) return false;
  
  // Validate card number (16 digits)
  const cleanNumber = cardNumber.replace(/\s/g, '');
  if (cleanNumber.length !== 16) return false;
  
  // Validate expiry format (MM/YY)
  if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiryDate)) return false;
  
  // Validate CVV (3 or 4 digits)
  const cvvDigits = cvv.replace(/\D/g, '');
  if (cvvDigits.length !== 3 && cvvDigits.length !== 4) return false;
  
  return true;
});

const submitBankDetails = async () => {
  if (!form.value.validate()) return;
  
  try {
    loading.value = true;
    error.value = null;

    // Send to backend to create Stripe bank account
    const response = await axios.post(roleConfig.value.apiEndpoint, {
      payoutMethod: 'bank',
      accountHolderName: bankDetails.value.accountHolderName,
      routingNumber: bankDetails.value.routingNumber,
      accountNumber: bankDetails.value.accountNumber,
      accountType: bankDetails.value.accountType.toLowerCase()
    });

    if (response.data.success) {
      window.location.href = roleConfig.value.redirectUrl;
    } else {
      throw new Error(response.data.message || 'Failed to connect bank account');
    }
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to connect bank account. Please try again.';
  } finally {
    loading.value = false;
  }
};

const submitCardDetails = async () => {
  // Validate all fields manually
  validateCardField('cardholderName');
  validateCardField('cardNumber');
  validateCardField('expiryDate');
  validateCardField('cvv');
  
  // Check if there are any errors
  if (cardErrors.value.cardholderName || 
      cardErrors.value.cardNumber || 
      cardErrors.value.expiryDate || 
      cardErrors.value.cvv) {
    error.value = 'Please fix all validation errors before submitting.';
    return;
  }
  
  try {
    loading.value = true;
    error.value = null;

    const response = await axios.post(roleConfig.value.apiEndpoint, {
      payoutMethod: 'card',
      cardholderName: cardDetails.value.cardholderName,
      cardNumber: cardDetails.value.cardNumber.replace(/\s/g, ''),
      expiryDate: cardDetails.value.expiryDate,
      cvv: cardDetails.value.cvv
    });

    if (response.data.success) {
      window.location.href = roleConfig.value.redirectUrl;
    } else {
      throw new Error(response.data.message || 'Failed to connect debit card');
    }
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to connect debit card. Please try again.';
  } finally {
    loading.value = false;
  }
};

const submitAlipayDetails = async () => {
  if (!alipayForm.value.validate()) return;
  
  try {
    loading.value = true;
    error.value = null;

    const response = await axios.post('/api/stripe/connect-payout-method', {
      payoutMethod: 'alipay',
      accountName: alipayDetails.value.accountName,
      alipayId: alipayDetails.value.alipayId
    });

    if (response.data.success) {
      window.location.href = '/caregiver-dashboard?section=payment&success=true';
    } else {
      throw new Error(response.data.message || 'Failed to connect Alipay account');
    }
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to connect Alipay account. Please try again.';
  } finally {
    loading.value = false;
  }
};

const submitCashAppDetails = async () => {
  if (!cashappForm.value.validate()) return;
  
  try {
    loading.value = true;
    error.value = null;

    const response = await axios.post('/api/stripe/connect-payout-method', {
      payoutMethod: 'cashapp',
      accountName: cashappDetails.value.accountName,
      cashtag: cashappDetails.value.cashtag
    });

    if (response.data.success) {
      window.location.href = '/caregiver-dashboard?section=payment&success=true';
    } else {
      throw new Error(response.data.message || 'Failed to connect Cash App');
    }
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to connect Cash App. Please try again.';
  } finally {
    loading.value = false;
  }
};

const goBack = () => {
  window.location.href = '/caregiver-dashboard?section=payment';
};
</script>

<style scoped>
.custom-onboarding-container {
  min-height: 100vh;
  background: #f9fafb;
  display: flex;
}

/* Left Column - Dark Slate (matching payment page) */
.left-column {
  background: #0F172A;
  color: white;
  position: fixed;
  top: 0;
  left: 0;
  width: 41.666667%; /* 5/12 of the screen */
  height: 100vh;
  overflow: hidden;
}

@media (max-width: 960px) {
  .left-column {
    position: relative;
    width: 100%;
    height: auto;
    min-height: 400px;
  }
}

.left-column::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
  animation: pulse 15s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 0.3; }
  50% { transform: scale(1.1); opacity: 0.5; }
}

.branding-content {
  position: relative;
  z-index: 1;
  max-width: 500px;
}

.logo {
  max-width: 200px;
  height: auto;
  filter: brightness(0) invert(1);
}

.welcome-title {
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1.2;
}

.welcome-subtitle {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.9);
}

.benefits-list {
  background: transparent !important;
  text-align: left;
}

.benefits-list :deep(.v-list-item__prepend) .v-icon {
  color: white !important;
  opacity: 1 !important;
}

/* Right Column - White */
.right-column {
  background: white;
  margin-left: 41.666667%; /* Match left column width */
  width: 58.333333%; /* Remaining width (7/12) */
  min-height: 100vh;
}

@media (max-width: 960px) {
  .right-column {
    margin-left: 0;
    width: 100%;
  }
}

.form-content {
  max-width: 600px;
  width: 100%;
  margin: 0 auto;
}

.form-title {
  font-size: 2rem;
  font-weight: 700;
  color: #0F172A;
}

.form-card {
  border: 1px solid #e0e0e0;
  border-radius: 12px;
}

/* Payout Method Tabs */
.payout-tabs {
  border-bottom: 2px solid #e0e0e0;
}

.payout-tabs :deep(.v-tab) {
  text-transform: none;
  font-weight: 500;
  letter-spacing: 0;
  min-width: 120px;
}

.payout-tabs :deep(.v-tab--selected) {
  color: #3b82f6;
  font-weight: 600;
}

.payout-tabs :deep(.v-tab__slider) {
  background-color: #3b82f6;
  height: 3px;
}

.card-logos {
  display: flex;
  align-items: center;
  gap: 4px;
}

.card-logos img {
  height: 16px;
  width: auto;
}

/* Mobile Responsive */
@media (max-width: 960px) {
  .welcome-title {
    font-size: 2rem;
  }
}
</style>
