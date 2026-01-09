<template>
  <!-- Payment Processing Modal -->
  <transition name="modal-fade">
    <div v-if="processingModal.show" class="payment-modal-overlay" @click.self="closeModal">
      <div class="payment-modal">
        <!-- Processing State -->
        <div v-if="processingModal.state === 'processing'" class="modal-content processing-state">
          <div class="spinner-container">
            <div class="payment-spinner"></div>
          </div>
          <h3 class="modal-title">Processing Payment Method</h3>
          <p class="modal-description">Please wait while we securely save your payment information...</p>
        </div>

        <!-- Success State -->
        <div v-if="processingModal.state === 'success'" class="modal-content success-state">
          <div class="success-animation">
            <div class="checkmark-circle">
              <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="checkmark-circle-path" cx="26" cy="26" r="25" fill="none"/>
                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
              </svg>
            </div>
          </div>
          <h3 class="modal-title">Payment Method Saved!</h3>
          <p class="modal-description">Your card has been successfully linked to your account.</p>
          <div class="payment-details">
            <div class="detail-row">
              <span class="detail-label">Cardholder:</span>
              <span class="detail-value">{{ cardholderName }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Status:</span>
              <span class="detail-value status-active">✓ Active</span>
            </div>
          </div>
          
          <!-- Action Buttons -->
          <div class="modal-actions">
            <button class="modal-button dashboard-button" @click="goToDashboard">
              <i class="mdi mdi-speedometer2"></i>
              Go to Dashboard
            </button>
          </div>
          
          <p class="redirect-message">Auto-redirecting in {{ redirectCountdown }} seconds...</p>
        </div>

        <!-- Failure State -->
        <div v-if="processingModal.state === 'failed'" class="modal-content failed-state">
          <div class="error-animation">
            <div class="error-circle">
              <svg class="error-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="error-circle-path" cx="26" cy="26" r="25" fill="none"/>
                <path class="error-cross" fill="none" d="M16 16 36 36 M36 16 16 36"/>
              </svg>
            </div>
          </div>
          <h3 class="modal-title">Setup Failed</h3>
          <p class="modal-description error-text">{{ processingModal.errorMessage }}</p>
          <div class="modal-actions">
            <button class="modal-button retry-button" @click="closeModal">
              <i class="mdi mdi-refresh"></i>
              Try Again
            </button>
            <button class="modal-button contact-button" @click="contactSupport">
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
        <!-- Left Column - Branding (Dark Blue) -->
        <v-col cols="12" md="5" class="left-column d-flex flex-column justify-center align-center pa-8">
          <div class="branding-content text-center">
            <div class="logo-container mb-8">
              <img src="/logo.png" alt="CAS Private Care" class="logo" />
            </div>
            
            <h1 class="welcome-title mb-4">Link Your Payment Method</h1>
            <p class="welcome-subtitle mb-8">
              Connect your card to enable automatic recurring payments for your bookings
            </p>
            
            <v-list class="benefits-list" lines="two">
              <v-list-item prepend-icon="mdi-shield-lock" class="px-0">
                <v-list-item-title class="text-white font-weight-medium">Bank-Level Security</v-list-item-title>
                <v-list-item-subtitle class="text-grey-lighten-1">256-bit SSL encryption protects your data</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item prepend-icon="mdi-sync" class="px-0">
                <v-list-item-title class="text-white font-weight-medium">Automatic Payments</v-list-item-title>
                <v-list-item-subtitle class="text-grey-lighten-1">Set up once and never miss a payment</v-list-item-subtitle>
              </v-list-item>
              
              <v-list-item prepend-icon="mdi-cog-outline" class="px-0">
                <v-list-item-title class="text-white font-weight-medium">Easy Management</v-list-item-title>
                <v-list-item-subtitle class="text-grey-lighten-1">Update or remove payment methods anytime</v-list-item-subtitle>
              </v-list-item>

              <v-list-item prepend-icon="mdi-receipt-text-check" class="px-0">
                <v-list-item-title class="text-white font-weight-medium">Instant Receipts</v-list-item-title>
                <v-list-item-subtitle class="text-grey-lighten-1">Automated receipts sent to your email</v-list-item-subtitle>
              </v-list-item>
            </v-list>

            <!-- Powered by Stripe -->
            <div class="powered-by mt-8" style="text-align: center;">
              <p class="text-grey-lighten-1 text-caption mb-2" style="text-align: center;">Powered by</p>
              <img 
                src="https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg" 
                alt="Stripe" 
                style="height: 28px; filter: brightness(0) invert(1); display: block; margin: 0 auto;"
              >
            </div>
          </div>
        </v-col>

        <!-- Right Column - Form (White) -->
        <v-col cols="12" md="7" class="right-column d-flex flex-column justify-center pa-8">
          <div class="form-content">
            <h2 class="form-title mb-2">Payment Information</h2>
            <p class="form-subtitle text-grey-darken-1 mb-6">
              Enter your payment details below. This information is securely transmitted to Stripe.
            </p>

            <v-card elevation="0" class="form-card pa-6" :loading="loading">
              <!-- Error Alert -->
              <v-alert
                v-if="error"
                type="error"
                variant="tonal"
                closable
                @click:close="error = ''"
                class="mb-6"
              >
                <template #title>Error</template>
                {{ error }}
              </v-alert>

              <!-- Security Notice -->
              <v-alert
                type="info"
                variant="tonal"
                class="mb-6"
              >
                <template #prepend>
                  <v-icon size="28">mdi-shield-check</v-icon>
                </template>
                <template #title>
                  <strong>PCI DSS Level 1 Certified</strong>
                </template>
                <template #text>
                  <p class="mb-0 text-body-2">
                    Your card information is encrypted and securely transmitted directly to Stripe. 
                    CAS Private Care never stores your full card details on our servers.
                  </p>
                </template>
              </v-alert>

              <!-- Loading State -->
              <div v-if="loading" class="text-center py-12">
                <v-progress-circular
                  indeterminate
                  color="primary"
                  size="64"
                  width="6"
                ></v-progress-circular>
                <p class="text-body-2 text-grey-darken-1 mt-4">Loading secure payment form...</p>
              </div>

              <!-- Payment Form -->
              <v-form v-else @submit.prevent="savePaymentMethod">
                <h3 class="text-h6 mb-4 text-grey-darken-3">Card Information</h3>
                
                <!-- Stripe Payment Element -->
                <div class="stripe-payment-element mb-4">
                  <div id="payment-element"></div>
                </div>

                <!-- Cardholder Name -->
                <v-text-field
                  v-model="cardholderName"
                  label="Cardholder Name"
                  placeholder="John Doe"
                  variant="outlined"
                  prepend-inner-icon="mdi-account"
                  class="mb-6"
                ></v-text-field>

                <!-- Submit Button -->
                <v-btn
                  type="submit"
                  color="primary"
                  size="large"
                  block
                  :loading="submitting"
                  :disabled="submitting || loading"
                  class="text-none mb-4"
                >
                  <v-icon start>mdi-check-circle</v-icon>
                  {{ submitting ? 'Securely Saving...' : 'Save Payment Method' }}
                </v-btn>

                <!-- Security Badges -->
                <div class="security-badges text-center">
                  <p class="text-caption text-grey-darken-1 mb-2">Protected by industry-leading security</p>
                  <div class="d-flex justify-center gap-2 flex-wrap">
                    <v-chip size="x-small" variant="text" class="text-grey">
                      <v-icon start size="14">mdi-lock</v-icon>
                      256-bit SSL
                    </v-chip>
                    <v-chip size="x-small" variant="text" class="text-grey">
                      <v-icon start size="14">mdi-shield-check</v-icon>
                      PCI DSS Level 1
                    </v-chip>
                    <v-chip size="x-small" variant="text" class="text-grey">
                      <v-icon start size="14">mdi-bank</v-icon>
                      Bank-Grade Encryption
                    </v-chip>
                  </div>
                </div>
              </v-form>
            </v-card>

            <!-- Help Text -->
            <div class="help-text text-center mt-6">
              <v-icon size="16" class="mr-1 text-grey">mdi-help-circle-outline</v-icon>
              <span class="text-body-2 text-grey">
                Having trouble? Contact support at 
                <a href="mailto:support@casprivatecare.com" class="text-primary text-decoration-none">support@casprivatecare.com</a>
              </span>
            </div>
          </div>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';

const loading = ref(true);
const submitting = ref(false);
const error = ref('');
const cardholderName = ref('');
const redirectCountdown = ref(5);

// Processing Modal State
const processingModal = ref({
  show: false,
  state: 'processing', // 'processing', 'success', 'failed'
  errorMessage: ''
});

let stripe = null;
let elements = null;
let paymentElement = null;

onMounted(async () => {
  try {
    loading.value = true;
    error.value = '';

    // Get setup intent from backend
    const response = await axios.post('/api/client/payments/setup-intent');
    const clientSecret = response.data.client_secret;

    // Initialize Stripe
    if (!window.Stripe) {
      throw new Error('Stripe.js failed to load. Please refresh the page.');
    }

    stripe = window.Stripe(import.meta.env.VITE_STRIPE_KEY || window.STRIPE_PUBLISHABLE_KEY);
    
    // Create Stripe Elements
    elements = stripe.elements({ 
      clientSecret,
      appearance: {
        theme: 'stripe',
        variables: {
          colorPrimary: '#1976d2',
          colorBackground: '#ffffff',
          colorText: '#424242',
          colorDanger: '#d32f2f',
          fontFamily: 'Roboto, sans-serif',
          spacingUnit: '4px',
          borderRadius: '8px',
        }
      }
    });

    // Create payment element (but don't mount yet)
    paymentElement = elements.create('payment', {
      layout: {
        type: 'tabs',
        defaultCollapsed: false
      }
    });
    
    // Set loading to false to show the form
    loading.value = false;
    
    // Wait for Vue to render the DOM with the payment-element div
    await nextTick();
    
    // Now mount the Stripe Element
    paymentElement.mount('#payment-element');
    
  } catch (err) {
    console.error('Initialization error:', err);
    error.value = err.response?.data?.message || err.message || 'Failed to load payment form. Please refresh the page and try again.';
    loading.value = false;
  }
});

const savePaymentMethod = async () => {
  if (!cardholderName.value.trim()) {
    error.value = 'Please enter the cardholder name';
    return;
  }

  submitting.value = true;
  error.value = '';

  // Show processing modal
  processingModal.value = {
    show: true,
    state: 'processing',
    errorMessage: ''
  };

  try {
    // Confirm setup with Stripe
    const { setupIntent, error: stripeError } = await stripe.confirmSetup({
      elements,
      confirmParams: {
        payment_method_data: {
          billing_details: {
            name: cardholderName.value.trim()
          }
        }
      },
      redirect: 'if_required'
    });

    if (stripeError) {
      // Show failure modal
      processingModal.value = {
        show: true,
        state: 'failed',
        errorMessage: stripeError.message
      };
      submitting.value = false;
      return;
    }

    // Attach payment method to customer on backend
    await axios.post('/api/client/payments/attach', {
      payment_method: setupIntent.payment_method
    });

    // Show success modal
    processingModal.value = {
      show: true,
      state: 'success',
      errorMessage: ''
    };

    // Start countdown and redirect
    startRedirectCountdown();

  } catch (err) {
    console.error('Save error:', err);
    
    // Show failure modal
    processingModal.value = {
      show: true,
      state: 'failed',
      errorMessage: err.response?.data?.message || err.message || 'Failed to save payment method. Please try again.'
    };
    submitting.value = false;
  }
};

const closeModal = () => {
  if (processingModal.value.state !== 'processing') {
    processingModal.value.show = false;
    submitting.value = false;
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
  window.location.href = '/client/dashboard';
};

const contactSupport = () => {
  window.location.href = 'mailto:support@casprivatecare.com?subject=Payment Method Setup Issue';
};
</script>

<style scoped>
/* Container */
.custom-onboarding-container {
  min-height: 100vh;
  background: #f9fafb;
  display: flex;
}

/* Left Column - Dark Slate (matching bank account page) */
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

.powered-by {
  margin-top: 2rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.powered-by p {
  text-align: center !important;
  margin: 0 auto;
}

.powered-by img {
  display: block;
  margin: 0 auto;
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

.form-subtitle {
  font-size: 1rem;
  line-height: 1.5;
}

.form-card {
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  background: white;
}

/* Stripe Payment Element */
.stripe-payment-element {
  background: #fafafa;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  padding: 16px;
  transition: all 0.3s ease;
}

.stripe-payment-element:focus-within {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  background: #ffffff;
}

#payment-element {
  min-height: 120px;
}

/* Security Badges */
.security-badges {
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
}

/* Help Text */
.help-text {
  color: #6b7280;
}

.help-text a {
  text-decoration: none;
  font-weight: 500;
}

.help-text a:hover {
  text-decoration: underline;
}

/* Mobile Responsive */
@media (max-width: 960px) {
  .welcome-title {
    font-size: 2rem;
  }
  
  .form-title {
    font-size: 1.75rem;
  }
  
  .accepted-cards .d-flex {
    flex-wrap: wrap;
  }
}

/* ============================================
   PAYMENT MODAL STYLES
   ============================================ */

/* Modal Overlay */
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

/* Modal Transitions */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-active .payment-modal,
.modal-fade-leave-active .payment-modal {
  transition: transform 0.3s ease;
}

.modal-fade-enter-from .payment-modal,
.modal-fade-leave-to .payment-modal {
  transform: scale(0.9);
}

/* Processing State */
.spinner-container {
  display: flex;
  justify-content: center;
  margin-bottom: 24px;
}

.payment-spinner {
  width: 64px;
  height: 64px;
  border: 4px solid #e5e7eb;
  border-top-color: #0F172A;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Success State */
.success-animation {
  display: flex;
  justify-content: center;
  margin-bottom: 24px;
}

.checkmark-circle {
  width: 80px;
  height: 80px;
}

.checkmark {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: block;
  stroke-width: 3;
  stroke: #10b981;
  stroke-miterlimit: 10;
  animation: fill-success 0.4s ease-in-out 0.4s forwards, scale-success 0.3s ease-in-out 0.9s both;
}

.checkmark-circle-path {
  stroke-dasharray: 166;
  stroke-dashoffset: 166;
  stroke-width: 3;
  stroke-miterlimit: 10;
  stroke: #10b981;
  fill: none;
  animation: stroke-success 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.checkmark-check {
  transform-origin: 50% 50%;
  stroke-dasharray: 48;
  stroke-dashoffset: 48;
  stroke: #10b981;
  stroke-width: 3;
  animation: stroke-check 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke-success {
  100% {
    stroke-dashoffset: 0;
  }
}

@keyframes stroke-check {
  100% {
    stroke-dashoffset: 0;
  }
}

@keyframes fill-success {
  100% {
    box-shadow: inset 0px 0px 0px 30px #10b981;
  }
}

@keyframes scale-success {
  0%, 100% {
    transform: none;
  }
  50% {
    transform: scale3d(1.1, 1.1, 1);
  }
}

/* Error State */
.error-animation {
  display: flex;
  justify-content: center;
  margin-bottom: 24px;
}

.error-circle {
  width: 80px;
  height: 80px;
}

.error-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: block;
  stroke-width: 3;
  stroke: #ef4444;
  stroke-miterlimit: 10;
  animation: fill-error 0.4s ease-in-out 0.4s forwards, scale-error 0.3s ease-in-out 0.9s both;
}

.error-circle-path {
  stroke-dasharray: 166;
  stroke-dashoffset: 166;
  stroke-width: 3;
  stroke-miterlimit: 10;
  stroke: #ef4444;
  fill: none;
  animation: stroke-error 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.error-cross {
  transform-origin: 50% 50%;
  stroke-dasharray: 48;
  stroke-dashoffset: 48;
  stroke: #ef4444;
  stroke-width: 3;
  animation: stroke-cross 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke-error {
  100% {
    stroke-dashoffset: 0;
  }
}

@keyframes stroke-cross {
  100% {
    stroke-dashoffset: 0;
  }
}

@keyframes fill-error {
  100% {
    box-shadow: inset 0px 0px 0px 30px #ef4444;
  }
}

@keyframes scale-error {
  0%, 100% {
    transform: none;
  }
  50% {
    transform: scale3d(1.1, 1.1, 1);
  }
}

/* Modal Text */
.modal-title {
  font-size: 24px;
  font-weight: 700;
  color: #0F172A;
  margin-bottom: 12px;
}

.processing-state .modal-title {
  color: #0F172A;
}

.success-state .modal-title {
  color: #10b981;
}

.failed-state .modal-title {
  color: #ef4444;
}

.modal-description {
  font-size: 16px;
  color: #64748b;
  margin-bottom: 32px;
  line-height: 1.5;
}

.error-text {
  color: #ef4444;
  font-weight: 500;
}

/* Payment Details in Modal */
.payment-details {
  background: #f8fafc;
  border-radius: 12px;
  padding: 20px;
  margin-top: 24px;
  text-align: left;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #e2e8f0;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-size: 14px;
  color: #64748b;
  font-weight: 500;
}

.detail-value {
  font-size: 14px;
  color: #0F172A;
  font-weight: 600;
}

.detail-value.status-active {
  color: #10b981;
  font-size: 16px;
}

/* Redirect Message */
.redirect-message {
  margin-top: 20px;
  font-size: 14px;
  color: #64748b;
  font-style: italic;
}

/* Modal Actions */
.modal-actions {
  display: flex;
  gap: 12px;
  margin-top: 24px;
}

.modal-button {
  flex: 1;
  padding: 14px 24px;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border: none;
}

.retry-button {
  background: #0F172A;
  color: white;
}

.retry-button:hover {
  background: #1e293b;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
}

.contact-button {
  background: #f1f5f9;
  color: #0F172A;
}

.contact-button:hover {
  background: #e2e8f0;
  transform: translateY(-1px);
}

.dashboard-button {
  background: #10b981;
  color: white;
}

.dashboard-button:hover {
  background: #059669;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

.receipt-button {
  background: #3b82f6;
  color: white;
}

.receipt-button:hover {
  background: #2563eb;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.modal-button i {
  font-size: 18px;
}
</style>
