<template>
  <!-- Payout result modal (success / failure with animation, same as client payment) -->
  <transition name="modal-fade">
    <div v-if="resultModal.show" class="payment-modal-overlay" @click.self="closeResultModal">
      <div class="payment-modal">
        <div v-if="resultModal.state === 'processing'" class="modal-content processing-state">
          <div class="spinner-container">
            <div class="payment-spinner"></div>
          </div>
          <h3 class="modal-title">Connecting Payout Method</h3>
          <p class="modal-description">Please wait while we securely save your payout information...</p>
        </div>

        <div v-if="resultModal.state === 'success'" class="modal-content success-state">
          <div class="success-animation">
            <div class="checkmark-circle">
              <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="checkmark-circle-path" cx="26" cy="26" r="25" fill="none"/>
                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
              </svg>
            </div>
          </div>
          <h3 class="modal-title">Payout Method Connected!</h3>
          <p class="modal-description">Your bank account has been successfully linked. You'll receive weekly payouts.</p>
          <div class="payment-details">
            <div class="detail-row">
              <span class="detail-label">Status:</span>
              <span class="detail-value status-active">✓ Active</span>
            </div>
          </div>
          <div class="modal-actions">
            <button type="button" class="modal-button dashboard-button" @click="goToDashboard">
              <i class="mdi mdi-speedometer2"></i>
              Go to Dashboard
            </button>
          </div>
          <p class="redirect-message">Auto-redirecting in {{ redirectCountdown }} seconds...</p>
        </div>

        <div v-if="resultModal.state === 'failed'" class="modal-content failed-state">
          <div class="error-animation">
            <div class="error-circle">
              <svg class="error-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="error-circle-path" cx="26" cy="26" r="25" fill="none"/>
                <path class="error-cross" fill="none" d="M16 16 36 36 M36 16 16 36"/>
              </svg>
            </div>
          </div>
          <h3 class="modal-title">Setup Failed</h3>
          <p class="modal-description error-text">{{ resultModal.errorMessage }}</p>
          <div class="modal-actions">
            <button type="button" class="modal-button retry-button" @click="closeResultModal">
              <i class="mdi mdi-refresh"></i>
              Try Again
            </button>
            <button type="button" class="modal-button contact-button" @click="contactSupport">
              <i class="mdi mdi-headset"></i>
              Contact Support
            </button>
          </div>
        </div>
      </div>
    </div>
  </transition>

  <div class="custom-onboarding-container">
    <v-container fluid class="fill-height pa-0">
      <v-row no-gutters class="fill-height">
        <!-- Left Column - Your Branding (Dark Blue) -->
        <v-col cols="12" md="5" class="left-column d-flex flex-column justify-center align-center pa-8 mobile-payout-hero">
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
              <!-- Payout Method: dropdown on mobile, tabs on desktop -->
              <div class="mb-6 payout-method-section">
                <h3 class="text-h6 mb-4 text-grey-darken-3 payout-method-label">Select Payout Method</h3>

                <!-- Mobile: dropdown for easy switching -->
                <div class="payout-method-dropdown">
                  <v-select
                    v-model="selectedPayoutMethod"
                    :items="payoutMethodOptions"
                    item-title="title"
                    item-value="value"
                    variant="outlined"
                    density="comfortable"
                    hide-details
                    class="payout-select"
                  >
                    <template #prepend-inner>
                      <v-icon size="small" :icon="currentPayoutMethodIcon" class="mr-1" />
                    </template>
                    <template #item="{ item, props: itemProps }">
                      <v-list-item v-bind="itemProps" :title="item.raw.title">
                        <template #prepend>
                          <v-icon :icon="item.raw.icon" size="small" class="mr-2" />
                        </template>
                      </v-list-item>
                    </template>
                    <template #selection="{ item }">
                      <span class="d-flex align-center">
                        <v-icon :icon="(item?.raw ?? item)?.icon" size="small" class="mr-2" />
                        {{ (item?.raw ?? item)?.title }}
                      </span>
                    </template>
                  </v-select>
                </div>

                <!-- Desktop: tabs -->
                <div class="payout-tabs-desktop">
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
              </div>

              <!-- Bank Account Form (shows when bank is selected) -->
              <v-form ref="form" v-model="valid" @submit.prevent="submitBankDetails" v-if="selectedPayoutMethod === 'bank'">
                <h3 class="text-h6 mb-4 text-grey-darken-3">Bank Account Information</h3>
                
                <!-- Account Holder Name (letters and spaces only, no numbers) -->
                <v-text-field
                  v-model="bankDetails.accountHolderName"
                  label="Account Holder Name"
                  variant="outlined"
                  prepend-inner-icon="mdi-account"
                  :rules="[rules.required, rules.accountHolderName]"
                  @input="filterAccountHolderName"
                  class="mb-4"
                ></v-text-field>

                <!-- Routing Number -->
                <v-text-field
                  v-model="bankDetails.routingNumber"
                  label="Routing Number"
                  variant="outlined"
                  prepend-inner-icon="mdi-bank"
                  :rules="[rules.required, rules.routing]"
                  maxlength="9"
                  inputmode="numeric"
                  @input="formatRoutingNumber"
                  class="mb-4"
                  hint="9-digit routing number"
                ></v-text-field>

                <!-- Account Number -->
                <v-text-field
                  v-model="bankDetails.accountNumber"
                  label="Account Number"
                  variant="outlined"
                  prepend-inner-icon="mdi-numeric"
                  :rules="[rules.required, rules.account]"
                  maxlength="17"
                  inputmode="numeric"
                  @input="formatAccountNumber"
                  class="mb-4"
                  hint="4-17 digits"
                ></v-text-field>

                <!-- Confirm Account Number -->
                <v-text-field
                  v-model="bankDetails.confirmAccountNumber"
                  label="Confirm Account Number"
                  variant="outlined"
                  prepend-inner-icon="mdi-numeric"
                  :rules="[rules.required, rules.match]"
                  maxlength="17"
                  inputmode="numeric"
                  @input="formatConfirmAccountNumber"
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
                      variant="outlined"
                      prepend-inner-icon="mdi-account"
                      :error-messages="cardErrors.cardholderName"
                      @input="filterCardholderName"
                      @blur="validateCardField('cardholderName')"
                      class="mb-4"
                      hint="Letters and spaces only"
                      persistent-hint
                    ></v-text-field>

                    <v-text-field
                      v-model="cardDetails.cardNumber"
                      label="Card Number"
                      variant="outlined"
                      prepend-inner-icon="mdi-credit-card"
                      :error-messages="cardErrors.cardNumber"
                      @blur="validateCardField('cardNumber')"
                      @input="formatCardNumber"
                      maxlength="19"
                      inputmode="numeric"
                      class="mb-4"
                    ></v-text-field>

                    <v-row>
                      <v-col cols="6">
                        <v-text-field
                          v-model="cardDetails.expiryDate"
                          label="Expiry Date (MM/YY)"
                          variant="outlined"
                          prepend-inner-icon="mdi-calendar"
                          :error-messages="cardErrors.expiryDate"
                          @blur="validateCardField('expiryDate')"
                          @input="formatExpiryDate"
                          maxlength="5"
                          inputmode="numeric"
                          class="mb-4"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="6">
                        <v-text-field
                          v-model="cardDetails.cvv"
                          label="CVV"
                          variant="outlined"
                          prepend-inner-icon="mdi-lock"
                          type="password"
                          class="mb-4"
                          hint="3 digits for Visa/MC, 4 for Amex"
                          persistent-hint
                          :error-messages="cardErrors.cvv"
                          @input="formatCvv"
                          @blur="validateCardField('cvv')"
                          maxlength="4"
                          inputmode="numeric"
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
                      variant="outlined"
                      prepend-inner-icon="mdi-account"
                      :rules="[rules.required]"
                      class="mb-4"
                    ></v-text-field>

                    <v-text-field
                      v-model="alipayDetails.alipayId"
                      label="Alipay ID / Email / Phone"
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
                      variant="outlined"
                      prepend-inner-icon="mdi-account"
                      :rules="[rules.required]"
                      class="mb-4"
                    ></v-text-field>

                    <v-text-field
                      v-model="cashappDetails.cashtag"
                      label="$Cashtag"
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

    <!-- Scroll indicator (mobile): hints that content continues below -->
    <transition name="scroll-indicator-fade">
      <div
        v-show="showScrollIndicator"
        class="scroll-indicator"
        aria-hidden="true"
      >
        <div class="scroll-indicator-inner">
          <v-icon icon="mdi-chevron-double-down" size="28" class="scroll-indicator-icon" />
          <span class="scroll-indicator-text">Scroll for more</span>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
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
    housekeeper: {
      title: 'Connect Your Payout Method',
      subtitle: 'Set up your bank account to receive weekly payments for your housekeeping services.',
      rateText: 'Earnings based on hours worked',
      apiEndpoint: '/api/stripe/housekeeper/connect-payout-method',
      redirectUrl: '/housekeeper/dashboard-vue?section=payment&success=true'
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

// Payout method options for mobile dropdown (same order as tabs)
const payoutMethodOptions = [
  { title: 'Debit Card', value: 'card', icon: 'mdi-credit-card' },
  { title: 'Alipay', value: 'alipay', icon: 'mdi-currency-cny' },
  { title: 'Cash App Pay', value: 'cashapp', icon: 'mdi-cash' },
  { title: 'Bank Account', value: 'bank', icon: 'mdi-bank' }
];
const currentPayoutMethodIcon = computed(() => {
  const o = payoutMethodOptions.find(i => i.value === selectedPayoutMethod.value);
  return o?.icon ?? 'mdi-bank';
});

// Scroll indicator: show on mobile until user scrolls down
const showScrollIndicator = ref(true);
const SCROLL_INDICATOR_THRESHOLD = 60;

function checkScrollIndicator() {
  const y = window.scrollY ?? document.documentElement.scrollTop;
  showScrollIndicator.value = y < SCROLL_INDICATOR_THRESHOLD;
}

onMounted(() => {
  window.addEventListener('scroll', checkScrollIndicator, { passive: true });
  checkScrollIndicator();
});
onUnmounted(() => {
  window.removeEventListener('scroll', checkScrollIndicator);
});

// Result modal (success/failure with animation, like client Stripe payment)
const resultModal = ref({
  show: false,
  state: 'processing', // 'processing' | 'success' | 'failed'
  errorMessage: ''
});
const redirectCountdown = ref(5);

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
  accountHolderName: value => {
    if (!value || !String(value).trim()) return true; // required is handled separately
    const cleaned = String(value).trim();
    if (/[0-9]/.test(cleaned)) return 'Account holder name cannot contain numbers';
    if (!/^[\p{L}\s\-'.]+$/u.test(cleaned)) return 'Use only letters, spaces, hyphens, or apostrophes';
    if (cleaned.length < 2) return 'Enter full name (at least 2 characters)';
    return true;
  },
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

// Account Holder Name: block digits and allow only letters, spaces, hyphen, apostrophe, period
const filterAccountHolderName = () => {
  let v = bankDetails.value.accountHolderName || '';
  v = v.replace(/[0-9]/g, '').replace(/[^\p{L}\s\-'.]/gu, '');
  bankDetails.value.accountHolderName = v;
};

// Cardholder Name: same as account holder (no numbers)
const filterCardholderName = () => {
  let v = cardDetails.value.cardholderName || '';
  v = v.replace(/[0-9]/g, '').replace(/[^\p{L}\s\-'.]/gu, '');
  cardDetails.value.cardholderName = v;
};

// Format card number with spaces (only digits, max 16 digits)
const formatCardNumber = (event) => {
  // Remove all non-digit characters
  let value = cardDetails.value.cardNumber.replace(/\D/g, '');
  // Limit to 16 digits
  value = value.slice(0, 16);
  // Format with spaces every 4 digits
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

// Format routing number - only allow digits, max 9 characters
const formatRoutingNumber = () => {
  const digits = bankDetails.value.routingNumber.replace(/\D/g, '').slice(0, 9);
  bankDetails.value.routingNumber = digits;
};

// Format account number - only allow digits, max 17 characters
const formatAccountNumber = () => {
  const digits = bankDetails.value.accountNumber.replace(/\D/g, '').slice(0, 17);
  bankDetails.value.accountNumber = digits;
};

// Format confirm account number - only allow digits, max 17 characters
const formatConfirmAccountNumber = () => {
  const digits = bankDetails.value.confirmAccountNumber.replace(/\D/g, '').slice(0, 17);
  bankDetails.value.confirmAccountNumber = digits;
};

// Validate individual card field
const validateCardField = (field) => {
  const value = cardDetails.value[field];
  
  switch (field) {
    case 'cardholderName': {
      const name = (value || '').trim();
      if (!name) {
        cardErrors.value.cardholderName = 'Cardholder name is required';
      } else if (/[0-9]/.test(name)) {
        cardErrors.value.cardholderName = 'Cardholder name cannot contain numbers';
      } else if (!/^[\p{L}\s\-'.]+$/u.test(name)) {
        cardErrors.value.cardholderName = 'Use only letters, spaces, hyphens, or apostrophes';
      } else if (name.length < 2) {
        cardErrors.value.cardholderName = 'Enter full name (at least 2 characters)';
      } else {
        cardErrors.value.cardholderName = '';
      }
      break;
    }
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
  const nameTrimmed = cardholderName.trim();
  if (nameTrimmed.length < 2 || /[0-9]/.test(nameTrimmed) || !/^[\p{L}\s\-'.]+$/u.test(nameTrimmed)) return false;
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
  
  resultModal.value = { show: true, state: 'processing', errorMessage: '' };
  loading.value = true;
  error.value = null;

  try {
    const response = await axios.post(roleConfig.value.apiEndpoint, {
      payoutMethod: 'bank',
      accountHolderName: bankDetails.value.accountHolderName,
      routingNumber: bankDetails.value.routingNumber,
      accountNumber: bankDetails.value.accountNumber,
      accountType: bankDetails.value.accountType.toLowerCase()
    });

    if (response.data.success) {
      resultModal.value = { show: true, state: 'success', errorMessage: '' };
      startRedirectCountdown();
    } else {
      throw new Error(response.data.message || 'Failed to connect bank account');
    }
  } catch (err) {
    resultModal.value = {
      show: true,
      state: 'failed',
      errorMessage: err.response?.data?.message || err.message || 'Failed to connect bank account. Please try again.'
    };
  } finally {
    loading.value = false;
  }
};

const submitCardDetails = async () => {
  validateCardField('cardholderName');
  validateCardField('cardNumber');
  validateCardField('expiryDate');
  validateCardField('cvv');
  if (cardErrors.value.cardholderName || cardErrors.value.cardNumber || cardErrors.value.expiryDate || cardErrors.value.cvv) {
    error.value = 'Please fix all validation errors before submitting.';
    return;
  }

  resultModal.value = { show: true, state: 'processing', errorMessage: '' };
  loading.value = true;
  error.value = null;

  try {
    const response = await axios.post(roleConfig.value.apiEndpoint, {
      payoutMethod: 'card',
      cardholderName: cardDetails.value.cardholderName,
      cardNumber: cardDetails.value.cardNumber.replace(/\s/g, ''),
      expiryDate: cardDetails.value.expiryDate,
      cvv: cardDetails.value.cvv
    });

    if (response.data.success) {
      resultModal.value = { show: true, state: 'success', errorMessage: '' };
      startRedirectCountdown();
    } else {
      throw new Error(response.data.message || 'Failed to connect debit card');
    }
  } catch (err) {
    resultModal.value = {
      show: true,
      state: 'failed',
      errorMessage: err.response?.data?.message || err.message || 'Failed to connect debit card. Please try again.'
    };
  } finally {
    loading.value = false;
  }
};

const submitAlipayDetails = async () => {
  if (!alipayForm.value.validate()) return;
  resultModal.value = { show: true, state: 'processing', errorMessage: '' };
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.post(roleConfig.value.apiEndpoint, {
      payoutMethod: 'alipay',
      accountName: alipayDetails.value.accountName,
      alipayId: alipayDetails.value.alipayId
    });
    if (response.data.success) {
      resultModal.value = { show: true, state: 'success', errorMessage: '' };
      startRedirectCountdown();
    } else {
      throw new Error(response.data.message || 'Failed to connect Alipay account');
    }
  } catch (err) {
    resultModal.value = { show: true, state: 'failed', errorMessage: err.response?.data?.message || err.message || 'Failed to connect Alipay. Please try again.' };
  } finally {
    loading.value = false;
  }
};

const submitCashAppDetails = async () => {
  if (!cashappForm.value.validate()) return;
  resultModal.value = { show: true, state: 'processing', errorMessage: '' };
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.post(roleConfig.value.apiEndpoint, {
      payoutMethod: 'cashapp',
      accountName: cashappDetails.value.accountName,
      cashtag: cashappDetails.value.cashtag
    });
    if (response.data.success) {
      resultModal.value = { show: true, state: 'success', errorMessage: '' };
      startRedirectCountdown();
    } else {
      throw new Error(response.data.message || 'Failed to connect Cash App');
    }
  } catch (err) {
    resultModal.value = { show: true, state: 'failed', errorMessage: err.response?.data?.message || err.message || 'Failed to connect Cash App. Please try again.' };
  } finally {
    loading.value = false;
  }
};

const goBack = () => {
  window.location.href = '/caregiver-dashboard?section=payment';
};

const closeResultModal = () => {
  if (resultModal.value.state !== 'processing') {
    resultModal.value.show = false;
  }
};

const startRedirectCountdown = () => {
  redirectCountdown.value = 5;
  const interval = setInterval(() => {
    redirectCountdown.value--;
    if (redirectCountdown.value <= 0) {
      clearInterval(interval);
      goToDashboard();
    }
  }, 1000);
};

const goToDashboard = () => {
  window.location.href = roleConfig.value.redirectUrl;
};

const contactSupport = () => {
  window.location.href = 'mailto:support@casprivatecare.com?subject=Payout Method Setup Issue';
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
    min-height: 380px;
    overflow: visible;
  }
  .branding-content {
    overflow: visible;
  }
  .welcome-subtitle {
    overflow-wrap: break-word;
    word-wrap: break-word;
    hyphens: auto;
    white-space: normal;
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
  max-width: 100%;
  overflow: visible;
  text-overflow: clip;
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.welcome-subtitle {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.9);
  line-height: 1.5;
  overflow-wrap: break-word;
  word-wrap: break-word;
  white-space: normal;
  max-width: 100%;
  overflow: visible;
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

/* Scroll indicator (mobile only): fixed at bottom, fades when user scrolls */
.scroll-indicator {
  display: none;
}
.scroll-indicator-inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 10px 20px 16px;
  background: linear-gradient(to top, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.92) 70%, transparent 100%);
  color: #64748b;
  border-radius: 16px 16px 0 0;
  box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.08);
  padding-bottom: calc(16px + env(safe-area-inset-bottom, 0));
}
.scroll-indicator-icon {
  color: #3b82f6;
  animation: scroll-indicator-bounce 2s ease-in-out infinite;
}
.scroll-indicator-text {
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.scroll-indicator-fade-enter-active,
.scroll-indicator-fade-leave-active {
  transition: opacity 0.35s ease, transform 0.35s ease;
}
.scroll-indicator-fade-enter-from,
.scroll-indicator-fade-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
@keyframes scroll-indicator-bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(4px); }
}
@media (max-width: 600px) {
  .scroll-indicator {
    display: block;
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 10;
    pointer-events: none;
  }
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
.payout-tabs :deep(.v-tab span) {
  white-space: normal;
  overflow: visible;
  text-overflow: clip;
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

/* Payout: dropdown on mobile, tabs on desktop */
.payout-method-dropdown {
  display: none;
}
.payout-tabs-desktop {
  display: block;
}

/* Mobile Responsive */
@media (max-width: 960px) {
  .welcome-title {
    font-size: 1.75rem;
  }
  
  .welcome-subtitle {
    font-size: 1rem;
  }
  
  .form-title {
    font-size: 1.5rem;
  }
  
  .form-content {
    padding: 0 8px;
  }
  
  .form-card {
    padding: 16px !important;
  }
  
  .payout-tabs :deep(.v-tab) {
    min-width: auto;
    padding: 0 12px;
    font-size: 0.85rem;
  }
  
  .card-logos {
    display: none;
  }
  
  .branding-content {
    padding: 16px;
  }
  
  .left-column {
    padding: 24px 16px !important;
  }
  
  .right-column {
    padding: 24px 16px !important;
  }
}

/* ========== CUSTOM MOBILE LAYOUT (≤600px) ========== */
@media (max-width: 600px) {
  /* Mobile: show dropdown, hide tabs */
  .payout-method-dropdown {
    display: block !important;
  }
  .payout-tabs-desktop {
    display: none !important;
  }

  .custom-onboarding-container {
    padding-top: env(safe-area-inset-top);
    padding-bottom: env(safe-area-inset-bottom);
    padding-left: env(safe-area-inset-left);
    padding-right: env(safe-area-inset-right);
  }

  /* Mobile hero: compact header, no truncation */
  .mobile-payout-hero.left-column {
    min-height: auto;
    padding: 20px 16px 24px !important;
    padding-left: calc(16px + env(safe-area-inset-left));
    padding-right: calc(16px + env(safe-area-inset-right));
    overflow: visible;
    justify-content: flex-start !important;
    align-items: center;
  }
  .mobile-payout-hero .branding-content {
    max-width: 100%;
    width: 100%;
    padding: 0;
  }
  .mobile-payout-hero .logo-container {
    margin-bottom: 16px;
  }
  .mobile-payout-hero .logo {
    max-width: 120px;
  }
  .mobile-payout-hero .welcome-title {
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1.35;
    margin-bottom: 12px !important;
    word-break: break-word;
    overflow: visible;
    text-overflow: unset;
    width: 100%;
  }
  .mobile-payout-hero .welcome-subtitle {
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 16px !important;
    width: 100%;
  }
  .mobile-payout-hero .benefits-list :deep(.v-list-item) {
    padding: 8px 0 !important;
    min-height: 40px;
  }
  .mobile-payout-hero .benefits-list :deep(.v-list-item-title) {
    font-size: 0.85rem !important;
  }
  .mobile-payout-hero .benefits-list :deep(.v-list-item-subtitle) {
    font-size: 0.75rem !important;
  }

  .form-title {
    font-size: 1.25rem;
  }

  .right-column {
    padding: 20px 16px 24px !important;
    padding-left: calc(16px + env(safe-area-inset-left));
    padding-right: calc(16px + env(safe-area-inset-right));
  }

  .form-content {
    padding: 0;
  }

  .form-card {
    padding: 16px !important;
    border-radius: 12px;
  }

  /* Payout method: 2x2 grid on mobile so all options visible (no cut-off "Bank") */
  .payout-method-section {
    width: 100%;
  }
  .payout-method-label {
    font-size: 0.95rem !important;
  }
  .payout-tabs {
    overflow: visible !important;
  }
  .payout-tabs :deep(.v-window),
  .payout-tabs :deep(.v-tabs-container),
  .payout-tabs :deep(.v-slide-group),
  .payout-tabs :deep([class*="slide-group"]) {
    width: 100% !important;
    overflow: visible !important;
  }
  .payout-tabs :deep(.v-slide-group__container),
  .payout-tabs :deep([class*="slide-group__container"]) {
    overflow: visible !important;
  }
  /* Force 2x2 grid so "Alipay" / "Bank" etc. are never truncated */
  .payout-tabs :deep(.v-slide-group__content),
  .payout-tabs :deep([class*="slide-group__content"]) {
    display: grid !important;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    flex-wrap: wrap !important;
    overflow: visible !important;
  }
  .payout-tabs :deep(.v-tab) {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    max-width: none !important;
    min-height: 48px;
    padding: 0 8px;
    font-size: 0.8rem;
    justify-content: center;
    overflow: visible !important;
    text-overflow: clip !important;
    white-space: normal !important;
  }
  .payout-tabs :deep(.v-tab .v-btn__content),
  .payout-tabs :deep(.v-tab .v-tab__slider) {
    overflow: visible !important;
    min-width: 0 !important;
  }
  .payout-tabs :deep(.v-tab span),
  .payout-tabs :deep(.v-tab .v-btn__content span) {
    white-space: normal !important;
    text-overflow: clip !important;
    overflow: visible !important;
    max-width: none !important;
  }
  .payout-tabs :deep(.v-tab *) {
    overflow: visible !important;
    text-overflow: clip !important;
  }

  /* Inputs: 16px font, 48px tap target */
  .form-card :deep(.v-field) {
    font-size: 16px !important;
  }
  .form-card :deep(.v-field__input) {
    min-height: 48px;
    padding: 12px 16px !important;
  }
  .form-card :deep(.v-btn) {
    min-height: 48px;
    font-size: 1rem;
  }
  .form-subtitle {
    font-size: 0.9rem;
  }
}

/* Small phones (e.g. iPhone 14 Pro Max 430px) – same custom mobile look, tighter */
@media (max-width: 430px) {
  .mobile-payout-hero.left-column {
    padding: 16px 12px 20px !important;
    padding-left: calc(12px + env(safe-area-inset-left));
    padding-right: calc(12px + env(safe-area-inset-right));
  }
  .mobile-payout-hero .logo {
    max-width: 100px;
  }
  .mobile-payout-hero .welcome-title {
    font-size: 1.15rem;
    line-height: 1.4;
  }
  .mobile-payout-hero .welcome-subtitle {
    font-size: 0.8125rem;
    line-height: 1.45;
  }
  .form-title {
    font-size: 1.1rem;
  }
  .payout-tabs :deep(.v-slide-group__content) {
    grid-template-columns: 1fr 1fr;
  }
  .payout-tabs :deep(.v-tab) {
    font-size: 0.75rem;
    min-width: 0 !important;
  }
  .payout-tabs :deep(.v-tab span),
  .payout-tabs :deep(.v-tab .v-btn__content span) {
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: clip !important;
  }
  .payment-modal-overlay {
    padding: 12px;
    align-items: flex-end;
  }
  .payment-modal {
    border-radius: 16px 16px 0 0;
    max-height: 90vh;
    overflow-y: auto;
  }
  .modal-content {
    padding: 32px 24px;
  }
}

/* Fix placeholder/label overlap - hide placeholder when label is floating */
:deep(.v-field--variant-outlined.v-field--active input::placeholder),
:deep(.v-field--variant-outlined.v-field--focused input::placeholder),
:deep(.v-field--variant-outlined.v-field--dirty input::placeholder) {
  opacity: 0 !important;
  color: transparent !important;
}

/* Ensure labels don't overlap with values */
:deep(.v-field__input) {
  padding-top: 8px !important;
}

:deep(.v-field--active .v-label.v-field-label) {
  transform: translateY(-50%) scale(0.75) !important;
  background: white;
  padding: 0 4px;
}

/* ========== Payout result modal (same as client Stripe payment modal) ========== */
.payment-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
  padding-top: max(20px, env(safe-area-inset-top));
  padding-bottom: max(20px, env(safe-area-inset-bottom));
  padding-left: max(20px, env(safe-area-inset-left));
  padding-right: max(20px, env(safe-area-inset-right));
}
.payment-modal {
  background: white;
  border-radius: 16px;
  max-width: 500px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
}
.modal-content {
  padding: 48px 40px;
  text-align: center;
}
.modal-fade-enter-active,
.modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from,
.modal-fade-leave-to { opacity: 0; }
.modal-fade-enter-active .payment-modal,
.modal-fade-leave-active .payment-modal { transition: transform 0.3s ease; }
.modal-fade-enter-from .payment-modal,
.modal-fade-leave-to .payment-modal { transform: scale(0.9); }

.spinner-container { display: flex; justify-content: center; margin-bottom: 24px; }
.payment-spinner {
  width: 64px;
  height: 64px;
  border: 4px solid #e5e7eb;
  border-top-color: #0F172A;
  border-radius: 50%;
  animation: payout-spin 1s linear infinite;
}
@keyframes payout-spin { to { transform: rotate(360deg); } }

.success-animation { display: flex; justify-content: center; margin-bottom: 24px; }
.checkmark-circle { width: 80px; height: 80px; }
.checkmark {
  width: 80px; height: 80px; border-radius: 50%; display: block;
  stroke-width: 3; stroke: #10b981; stroke-miterlimit: 10;
  animation: payout-fill-success 0.4s ease-in-out 0.4s forwards, payout-scale-success 0.3s ease-in-out 0.9s both;
}
.checkmark-circle-path {
  stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 3; stroke-miterlimit: 10;
  stroke: #10b981; fill: none;
  animation: payout-stroke-success 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}
.checkmark-check {
  transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48;
  stroke: #10b981; stroke-width: 3;
  animation: payout-stroke-check 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}
@keyframes payout-stroke-success { 100% { stroke-dashoffset: 0; } }
@keyframes payout-stroke-check { 100% { stroke-dashoffset: 0; } }
@keyframes payout-fill-success { 100% { box-shadow: inset 0px 0px 0px 30px #10b981; } }
@keyframes payout-scale-success { 0%, 100% { transform: none; } 50% { transform: scale3d(1.1, 1.1, 1); } }

.error-animation { display: flex; justify-content: center; margin-bottom: 24px; }
.error-circle { width: 80px; height: 80px; }
.error-icon {
  width: 80px; height: 80px; border-radius: 50%; display: block;
  stroke-width: 3; stroke: #ef4444; stroke-miterlimit: 10;
  animation: payout-fill-error 0.4s ease-in-out 0.4s forwards, payout-scale-error 0.3s ease-in-out 0.9s both;
}
.error-circle-path {
  stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 3; stroke-miterlimit: 10;
  stroke: #ef4444; fill: none;
  animation: payout-stroke-error 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}
.error-cross {
  transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48;
  stroke: #ef4444; stroke-width: 3;
  animation: payout-stroke-cross 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}
@keyframes payout-stroke-error { 100% { stroke-dashoffset: 0; } }
@keyframes payout-stroke-cross { 100% { stroke-dashoffset: 0; } }
@keyframes payout-fill-error { 100% { box-shadow: inset 0px 0px 0px 30px #ef4444; } }
@keyframes payout-scale-error { 0%, 100% { transform: none; } 50% { transform: scale3d(1.1, 1.1, 1); } }

.modal-title { font-size: 24px; font-weight: 700; color: #0F172A; margin-bottom: 12px; }
.processing-state .modal-title { color: #0F172A; }
.success-state .modal-title { color: #10b981; }
.failed-state .modal-title { color: #ef4444; }
.modal-description { font-size: 16px; color: #64748b; margin-bottom: 32px; line-height: 1.5; }
.error-text { color: #ef4444; font-weight: 500; }

.payment-details { background: #f8fafc; border-radius: 12px; padding: 20px; margin-top: 24px; text-align: left; }
.detail-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #e2e8f0; }
.detail-row:last-child { border-bottom: none; }
.detail-label { font-size: 14px; color: #64748b; font-weight: 500; }
.detail-value { font-size: 14px; color: #0F172A; font-weight: 600; }
.detail-value.status-active { color: #10b981; font-size: 16px; }
.redirect-message { margin-top: 20px; font-size: 14px; color: #64748b; font-style: italic; }
.modal-actions { display: flex; gap: 12px; margin-top: 24px; }
.modal-button {
  flex: 1; padding: 14px 24px; border-radius: 8px; font-size: 15px; font-weight: 600;
  cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px; border: none;
}
.retry-button { background: #0F172A; color: white; }
.retry-button:hover { background: #1e293b; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); }
.contact-button { background: #f1f5f9; color: #0F172A; }
.contact-button:hover { background: #e2e8f0; transform: translateY(-1px); }
.dashboard-button { background: #10b981; color: white; }
.dashboard-button:hover { background: #059669; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
.modal-button i { font-size: 18px; }
</style>
