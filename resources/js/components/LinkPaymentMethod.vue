<template>
  <div class="link-payment-container">
    <v-container fluid class="fill-height pa-0">
      <v-row no-gutters class="fill-height">
        <!-- Left Column - Branding (Dark Blue) -->
        <v-col cols="12" md="5" class="left-column d-flex flex-column justify-center align-center pa-8">
          <div class="branding-content text-center">
            <div class="logo-container mb-8">
              <img src="/logo.png" alt="CAS Private Care" class="logo" />
            </div>
            
            <h1 class="welcome-title mb-4">Link Your Payment Method</h1>
            <p class="welcome-subtitle mb-8">
              Connect your card or bank account to enable automatic recurring payments for your bookings
            </p>
            
            <v-list class="benefits-list" lines="two">
              <v-list-item prepend-icon="mdi-shield-lock-outline" class="px-0">
                <v-list-item-title class="text-white font-weight-medium">Bank-Level Security</v-list-item-title>
                <v-list-item-subtitle class="text-grey-lighten-1">256-bit SSL encryption protects your data</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item prepend-icon="mdi-sync" class="px-0">
                <v-list-item-title class="text-white font-weight-medium">Automatic Payments</v-list-item-title>
                <v-list-item-subtitle class="text-grey-lighten-1">Set up once and never miss a payment</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item prepend-icon="mdi-credit-card-check-outline" class="px-0">
                <v-list-item-title class="text-white font-weight-medium">Easy Management</v-list-item-title>
                <v-list-item-subtitle class="text-grey-lighten-1">Update or remove payment methods anytime</v-list-item-subtitle>
              </v-list-item>

              <v-list-item prepend-icon="mdi-receipt-text-check-outline" class="px-0">
                <v-list-item-title class="text-white font-weight-medium">Instant Receipts</v-list-item-title>
                <v-list-item-subtitle class="text-grey-lighten-1">Automated receipts sent to your email</v-list-item-subtitle>
              </v-list-item>
            </v-list>

            <div class="powered-by mt-8">
              <p class="text-grey-lighten-1 text-caption mb-2">Powered by</p>
              <v-icon size="60" color="white">mdi-bitcoin</v-icon>
              <p class="text-white text-caption mt-1">Stripe</p>
            </div>
          </div>
        </v-col>

        <!-- Right Column - Payment Form (White) -->
        <v-col cols="12" md="7" class="right-column d-flex flex-column justify-center pa-8">
          <div class="form-content">
            <!-- Back Button -->
            <v-btn
              variant="text"
              prepend-icon="mdi-arrow-left"
              @click="goBack"
              class="mb-4"
              color="primary"
            >
              Back to Dashboard
            </v-btn>

            <h2 class="form-title mb-2">Payment Method</h2>
            <p class="form-subtitle text-grey-darken-1 mb-6">
              Your payment information is securely processed by Stripe. We never store your full card details.
            </p>

            <!-- Success State -->
            <v-card v-if="paymentSaved" elevation="0" class="success-card pa-8 text-center mb-4" color="success" variant="tonal">
              <v-icon size="80" color="success" class="mb-4">mdi-check-circle</v-icon>
              <h3 class="text-h5 mb-2">Payment Method Linked!</h3>
              <p class="text-body-1 mb-4">Your {{ savedCardBrand }} ending in {{ savedCardLast4 }} has been successfully added.</p>
              <v-btn
                color="success"
                variant="flat"
                size="large"
                @click="goToDashboard"
                prepend-icon="mdi-view-dashboard"
              >
                Go to Dashboard
              </v-btn>
              <v-btn
                color="success"
                variant="text"
                size="large"
                @click="addAnother"
                prepend-icon="mdi-plus"
                class="ml-2"
              >
                Add Another Card
              </v-btn>
            </v-card>

            <!-- Payment Form -->
            <v-card v-else elevation="0" class="form-card pa-6" :loading="loading">
              <!-- Security Badge -->
              <v-alert
                type="info"
                variant="tonal"
                prominent
                class="mb-6"
              >
                <template #prepend>
                  <v-icon size="32">mdi-shield-check-outline</v-icon>
                </template>
                <template #text>
                  <p class="text-body-2 font-weight-bold mb-1">PCI DSS Level 1 Certified</p>
                  <p class="text-caption mb-0">
                    Your card information is encrypted and securely transmitted directly to Stripe. 
                    CAS Private Care never stores your full card details on our servers.
                  </p>
                </template>
              </v-alert>

              <!-- Card Logos -->
              <div class="accepted-cards mb-6">
                <p class="text-caption text-grey-darken-2 mb-2 font-weight-medium">We Accept:</p>
                <div class="d-flex align-center gap-2">
                  <!-- Mastercard -->
                  <svg width="38" height="24" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg">
                    <rect width="38" height="24" rx="3" fill="#f5f5f5"/>
                    <circle cx="15" cy="12" r="7" fill="#eb001b"/>
                    <circle cx="23" cy="12" r="7" fill="#f79e1b"/>
                    <path d="M19 6.5a7 7 0 0 0 0 11 7 7 0 0 0 0-11z" fill="#ff5f00"/>
                  </svg>
                  <!-- Visa -->
                  <svg width="38" height="24" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg">
                    <rect width="38" height="24" rx="3" fill="#f5f5f5"/>
                    <text x="19" y="15" text-anchor="middle" font-family="Arial, sans-serif" font-size="10" font-weight="bold" font-style="italic" fill="#1a1f71">VISA</text>
                  </svg>
                  <!-- Amex -->
                  <svg width="38" height="24" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg">
                    <rect width="38" height="24" rx="3" fill="#006fcf"/>
                    <text x="19" y="15" text-anchor="middle" font-family="Arial, sans-serif" font-size="7" font-weight="bold" fill="white">AMEX</text>
                  </svg>
                  <!-- JCB -->
                  <svg width="38" height="24" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg">
                    <rect width="38" height="24" rx="3" fill="#f5f5f5"/>
                    <rect x="4" y="4" width="10" height="16" rx="2" fill="#0f4c81"/>
                    <rect x="14" y="4" width="10" height="16" rx="2" fill="#c41230"/>
                    <rect x="24" y="4" width="10" height="16" rx="2" fill="#00a94f"/>
                    <text x="19" y="14" text-anchor="middle" font-family="Arial, sans-serif" font-size="6" font-weight="bold" fill="white">JCB</text>
                  </svg>
                </div>
              </div>

              <!-- Stripe Payment Element -->
              <div class="payment-element-section mb-6">
                <label class="element-label mb-3 d-block">
                  <v-icon size="20" class="mr-2" color="primary">mdi-credit-card-outline</v-icon>
                  <span class="font-weight-medium">Card Information</span>
                </label>
                <div class="stripe-element-wrapper">
                  <div id="payment-element" class="stripe-payment-element"></div>
                </div>
              </div>

              <!-- Cardholder Name -->
              <v-text-field
                v-model="cardholderName"
                label="Cardholder Name"
                variant="outlined"
                prepend-inner-icon="mdi-account"
                class="mb-6"
                :rules="[v => !!v || 'Name is required']"
              ></v-text-field>

              <!-- Terms Agreement -->
              <v-checkbox
                v-model="agreeToTerms"
                class="mb-4"
              >
                <template v-slot:label>
                  <div class="text-body-2">
                    I authorize CAS Private Care to securely store my payment method and charge it for future bookings when I enable auto-pay.
                    <a href="/terms" target="_blank" class="text-primary ml-1">Terms & Conditions</a>
                  </div>
                </template>
              </v-checkbox>

              <!-- Submit Button -->
              <v-btn
                color="primary"
                size="x-large"
                block
                :loading="loading"
                :disabled="!agreeToTerms || loading"
                @click="savePaymentMethod"
                prepend-icon="mdi-credit-card-plus"
                class="submit-btn"
                elevation="4"
              >
                {{ loading ? 'Saving Payment Method...' : 'Link Payment Method' }}
              </v-btn>

              <!-- Security Badges -->
              <div class="security-badges mt-6 text-center">
                <v-divider class="mb-4"></v-divider>
                <p class="text-caption text-grey-darken-1 mb-3">Protected by industry-leading security</p>
                <div class="d-flex justify-center flex-wrap gap-2">
                  <v-chip size="x-small" variant="text">
                    <v-icon start size="14">mdi-lock</v-icon>
                    256-bit SSL
                  </v-chip>
                  <v-chip size="x-small" variant="text">
                    <v-icon start size="14">mdi-shield-check</v-icon>
                    PCI DSS Level 1
                  </v-chip>
                  <v-chip size="x-small" variant="text">
                    <v-icon start size="14">mdi-bank</v-icon>
                    Bank-Grade Encryption
                  </v-chip>
                </div>
              </div>
            </v-card>

            <!-- Help Text -->
            <v-alert
              v-if="!paymentSaved"
              type="info"
              variant="text"
              density="compact"
              class="mt-4"
            >
              <template #text>
                <p class="text-caption">
                  <v-icon size="14" class="mr-1">mdi-information-outline</v-icon>
                  Having trouble? Contact support at <a href="mailto:support@casprivatecare.com" class="text-primary">support@casprivatecare.com</a>
                </p>
              </template>
            </v-alert>
          </div>
        </v-col>
      </v-row>
    </v-container>

    <!-- Error Dialog -->
    <v-dialog v-model="showError" max-width="400">
      <v-card>
        <v-card-title class="bg-error text-white">
          <v-icon class="mr-2">mdi-alert-circle</v-icon>
          Error
        </v-card-title>
        <v-card-text class="pt-4">
          {{ errorMessage }}
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="error" variant="text" @click="showError = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(false);
const paymentSaved = ref(false);
const cardholderName = ref('');
const agreeToTerms = ref(false);
const showError = ref(false);
const errorMessage = ref('');
const savedCardBrand = ref('');
const savedCardLast4 = ref('');

let stripe = null;
let elements = null;
let paymentElement = null;

const initializeStripe = async () => {
  loading.value = true;
  try {
    // Get SetupIntent client secret
    const res = await axios.post('/api/client/payments/setup-intent');
    const clientSecret = res.data.client_secret;

    // Initialize Stripe
    if (!window.Stripe) {
      throw new Error('Stripe.js failed to load');
    }

    stripe = window.Stripe(import.meta.env.VITE_STRIPE_KEY || window.STRIPE_PUBLISHABLE_KEY);
    
    // Create Elements instance
    elements = stripe.elements({
      clientSecret,
      appearance: {
        theme: 'stripe',
        variables: {
          colorPrimary: '#1976d2',
          colorBackground: '#ffffff',
          colorText: '#1a1a1a',
          colorDanger: '#df1b41',
          fontFamily: 'Roboto, sans-serif',
          spacingUnit: '4px',
          borderRadius: '8px',
        },
      },
    });

    // Create and mount Payment Element
    paymentElement = elements.create('payment', {
      layout: {
        type: 'tabs',
        defaultCollapsed: false,
      },
    });
    
    paymentElement.mount('#payment-element');
  } catch (error) {
    console.error('Error initializing Stripe:', error);
    errorMessage.value = 'Failed to load payment form. Please refresh the page and try again.';
    showError.value = true;
  } finally {
    loading.value = false;
  }
};

const savePaymentMethod = async () => {
  if (!agreeToTerms.value) {
    errorMessage.value = 'Please agree to the terms and conditions';
    showError.value = true;
    return;
  }

  if (!cardholderName.value) {
    errorMessage.value = 'Please enter the cardholder name';
    showError.value = true;
    return;
  }

  loading.value = true;

  try {
    // Confirm the SetupIntent
    const { setupIntent, error } = await stripe.confirmSetup({
      elements,
      redirect: 'if_required',
      confirmParams: {
        payment_method_data: {
          billing_details: {
            name: cardholderName.value,
          },
        },
        return_url: window.location.href,
      },
    });

    if (error) {
      throw new Error(error.message || 'Failed to save payment method');
    }

    // Attach payment method to customer
    const attachRes = await axios.post('/api/client/payments/attach', {
      payment_method: setupIntent.payment_method,
    });

    // Get card details for success message
    const pm = attachRes.data.payment_method;
    savedCardBrand.value = pm.card.brand.charAt(0).toUpperCase() + pm.card.brand.slice(1);
    savedCardLast4.value = pm.card.last4;

    paymentSaved.value = true;
  } catch (error) {
    console.error('Error saving payment method:', error);
    errorMessage.value = error.response?.data?.message || error.message || 'Failed to save payment method. Please try again.';
    showError.value = true;
  } finally {
    loading.value = false;
  }
};

const goBack = () => {
  window.location.href = '/client-dashboard';
};

const goToDashboard = () => {
  window.location.href = '/client-dashboard#payment-info';
};

const addAnother = () => {
  paymentSaved.value = false;
  cardholderName.value = '';
  agreeToTerms.value = false;
  initializeStripe();
};

onMounted(() => {
  initializeStripe();
});
</script>

<style scoped>
.link-payment-container {
  min-height: 100vh;
  background: #ffffff;
}

/* Left Column Styling */
.left-column {
  background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
  color: white;
  position: relative;
  overflow: hidden;
}

.left-column::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
  opacity: 0.4;
}

.branding-content {
  position: relative;
  z-index: 1;
  max-width: 500px;
}

.logo {
  max-width: 180px;
  height: auto;
  filter: brightness(0) invert(1);
}

.welcome-title {
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1.2;
  color: white;
}

.welcome-subtitle {
  font-size: 1.125rem;
  color: rgba(255, 255, 255, 0.9);
  line-height: 1.6;
}

.benefits-list {
  background: transparent !important;
}

.benefits-list :deep(.v-list-item) {
  padding: 16px 0;
}

.benefits-list :deep(.v-list-item__prepend) {
  color: white !important;
}

.powered-by {
  opacity: 0.7;
}

/* Right Column Styling */
.right-column {
  background: #f8f9fa;
}

.form-content {
  max-width: 600px;
  width: 100%;
  margin: 0 auto;
}

.form-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1a1a1a;
}

.form-subtitle {
  font-size: 1rem;
  line-height: 1.6;
}

.form-card {
  background: white;
  border-radius: 16px !important;
  border: 1px solid #e0e0e0;
}

.success-card {
  border-radius: 16px !important;
}

.stripe-element-wrapper {
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 16px;
  transition: all 0.3s ease;
}

.stripe-element-wrapper:focus-within {
  border-color: #1976d2;
  box-shadow: 0 0 0 4px rgba(25, 118, 210, 0.1);
}

.stripe-payment-element {
  min-height: 40px;
}

.element-label {
  font-size: 16px;
  color: #424242;
}

.submit-btn {
  font-size: 16px !important;
  font-weight: 600 !important;
  text-transform: none !important;
  letter-spacing: 0.5px !important;
  border-radius: 12px !important;
  transition: all 0.3s ease !important;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(25, 118, 210, 0.4) !important;
}

.gap-2 {
  gap: 8px;
}

/* Responsive */
@media (max-width: 960px) {
  .left-column {
    min-height: 400px;
  }

  .welcome-title {
    font-size: 2rem;
  }

  .form-title {
    font-size: 1.75rem;
  }
}

@media (max-width: 600px) {
  .left-column,
  .right-column {
    padding: 24px !important;
  }

  .welcome-title {
    font-size: 1.75rem;
  }

  .form-title {
    font-size: 1.5rem;
  }

  .accepted-cards .d-flex {
    flex-wrap: wrap;
  }
}
</style>
