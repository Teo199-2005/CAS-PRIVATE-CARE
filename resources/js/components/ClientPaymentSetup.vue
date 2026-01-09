<template>
  <div class="client-payment-setup-container">
    <v-app>
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
                Securely connect your card or bank account for seamless automatic payments
              </p>
              
              <v-list class="benefits-list" lines="two">
                <v-list-item prepend-icon="mdi-shield-lock-outline" class="px-0">
                  <v-list-item-title class="text-white font-weight-medium">Bank-Level Security</v-list-item-title>
                  <v-list-item-subtitle class="text-grey-lighten-1">256-bit SSL encryption protects your data</v-list-item-subtitle>
                </v-list-item>
                
                <v-list-item prepend-icon="mdi-sync" class="px-0">
                  <v-list-item-title class="text-white font-weight-medium">Auto-Pay Available</v-list-item-title>
                  <v-list-item-subtitle class="text-grey-lighten-1">Enable recurring payments for bookings</v-list-item-subtitle>
                </v-list-item>
                
                <v-list-item prepend-icon="mdi-credit-card-multiple" class="px-0">
                  <v-list-item-title class="text-white font-weight-medium">Multiple Methods</v-list-item-title>
                  <v-list-item-subtitle class="text-grey-lighten-1">Link multiple cards and bank accounts</v-list-item-subtitle>
                </v-list-item>

                <v-list-item prepend-icon="mdi-receipt" class="px-0">
                  <v-list-item-title class="text-white font-weight-medium">Instant Receipts</v-list-item-title>
                  <v-list-item-subtitle class="text-grey-lighten-1">Automatic receipt generation after payment</v-list-item-subtitle>
                </v-list-item>
              </v-list>

              <div class="security-badges mt-8">
                <v-chip size="small" variant="outlined" class="ma-1 text-white border-white">
                  <v-icon start size="16">mdi-shield-check</v-icon>
                  PCI DSS Level 1
                </v-chip>
                <v-chip size="small" variant="outlined" class="ma-1 text-white border-white">
                  <v-icon start size="16">mdi-lock</v-icon>
                  Stripe Certified
                </v-chip>
              </div>
            </div>
          </v-col>

          <!-- Right Column - Form (White) -->
          <v-col cols="12" md="7" class="right-column d-flex flex-column justify-center pa-8">
            <div class="form-content">
              <!-- Header -->
              <div class="d-flex justify-space-between align-center mb-6">
                <div>
                  <h2 class="form-title mb-2">Payment Method Setup</h2>
                  <p class="form-subtitle text-grey-darken-1 mb-0">
                    Add your card or bank account information securely
                  </p>
                </div>
                <v-btn
                  variant="text"
                  prepend-icon="mdi-arrow-left"
                  @click="goToDashboard"
                  class="text-grey-darken-2"
                >
                  Back to Dashboard
                </v-btn>
              </div>

              <!-- Success State -->
              <v-expand-transition>
                <v-card v-if="showSuccess" class="success-card mb-6" color="success" variant="tonal">
                  <v-card-text class="text-center pa-8">
                    <v-icon size="80" color="success" class="mb-4">mdi-check-circle</v-icon>
                    <h3 class="text-h5 font-weight-bold mb-3">Payment Method Added Successfully!</h3>
                    <p class="text-body-1 mb-4">
                      Your {{ successDetails.type }} ending in {{ successDetails.last4 }} has been linked to your account.
                    </p>
                    <v-btn
                      color="success"
                      size="large"
                      prepend-icon="mdi-view-dashboard"
                      @click="goToDashboard"
                      class="mr-2"
                    >
                      Go to Dashboard
                    </v-btn>
                    <v-btn
                      variant="outlined"
                      size="large"
                      prepend-icon="mdi-plus"
                      @click="addAnother"
                    >
                      Add Another
                    </v-btn>
                  </v-card-text>
                </v-card>
              </v-expand-transition>

              <!-- Main Form Card -->
              <v-card elevation="3" class="form-card pa-8" :loading="loading" v-if="!showSuccess">
                <!-- Saved Methods (if any) -->
                <div v-if="savedMethods.length > 0" class="mb-6">
                  <h3 class="text-h6 mb-4 d-flex align-center">
                    <v-icon class="mr-2" color="success">mdi-check-circle</v-icon>
                    Your Saved Payment Methods
                  </h3>
                  <v-card
                    v-for="method in savedMethods"
                    :key="method.id"
                    variant="outlined"
                    class="mb-3 pa-4"
                  >
                    <div class="d-flex align-center justify-space-between">
                      <div class="d-flex align-center">
                        <v-icon :color="getCardColor(method.card.brand)" size="40" class="mr-4">
                          mdi-credit-card
                        </v-icon>
                        <div>
                          <p class="text-h6 mb-0">{{ capitalize(method.card.brand) }} •••• {{ method.card.last4 }}</p>
                          <p class="text-caption text-grey mb-0">Expires {{ method.card.exp_month }}/{{ method.card.exp_year }}</p>
                        </div>
                      </div>
                      <v-chip color="success" size="small">
                        <v-icon start size="14">mdi-check</v-icon>
                        Active
                      </v-chip>
                    </div>
                  </v-card>
                  <v-divider class="my-6"></v-divider>
                </div>

                <!-- Add New Payment Method -->
                <h3 class="text-h6 mb-4 text-grey-darken-3">
                  <v-icon class="mr-2" color="primary">mdi-plus-circle</v-icon>
                  Add New Payment Method
                </h3>

                <!-- Info Alert -->
                <v-alert type="info" variant="tonal" class="mb-6" prominent border="start">
                  <template #text>
                    <div class="d-flex align-center">
                      <v-icon size="28" class="mr-3">mdi-shield-lock-outline</v-icon>
                      <div>
                        <p class="text-body-2 font-weight-bold mb-1">Secure Payment Processing</p>
                        <p class="text-caption mb-0">
                          Your payment information is encrypted and securely processed by Stripe. 
                          We never store your full card or bank details on our servers.
                        </p>
                      </div>
                    </div>
                  </template>
                </v-alert>

                <!-- Stripe Payment Element Container -->
                <div class="stripe-element-wrapper mb-6">
                  <label class="stripe-label mb-3 d-flex align-center">
                    <v-icon size="20" class="mr-2">mdi-credit-card-outline</v-icon>
                    <span class="text-h6">Card or Bank Account Information</span>
                  </label>
                  <div id="payment-element" class="stripe-element"></div>
                  <p class="text-caption text-grey mt-2 mb-0">
                    <v-icon size="14" class="mr-1">mdi-information</v-icon>
                    Enter your card details or choose bank payment option
                  </p>
                </div>

                <!-- Terms Checkbox -->
                <v-checkbox
                  v-model="agreeToTerms"
                  color="primary"
                  class="mb-4"
                  hide-details
                >
                  <template v-slot:label>
                    <div class="text-body-2">
                      I authorize CAS Private Care to charge this payment method for my bookings.
                      <a href="#" class="text-primary text-decoration-none">Terms & Conditions</a>
                    </div>
                  </template>
                </v-checkbox>

                <!-- Submit Button -->
                <v-btn
                  color="primary"
                  size="x-large"
                  block
                  :loading="saving"
                  :disabled="saving || !agreeToTerms"
                  @click="savePaymentMethod"
                  prepend-icon="mdi-content-save"
                  class="save-btn mt-4"
                  elevation="4"
                >
                  {{ saving ? 'Saving Payment Method...' : 'Save Payment Method' }}
                </v-btn>

                <!-- Security Footer -->
                <div class="security-footer mt-6 pt-4 text-center">
                  <v-chip size="small" variant="text" class="ma-1">
                    <v-icon start size="16">mdi-lock</v-icon>
                    256-bit SSL
                  </v-chip>
                  <v-chip size="small" variant="text" class="ma-1">
                    <v-icon start size="16">mdi-shield-check</v-icon>
                    PCI Compliant
                  </v-chip>
                  <v-chip size="small" variant="text" class="ma-1">
                    <v-icon start size="16">mdi-bank</v-icon>
                    Stripe Verified
                  </v-chip>
                  <p class="text-caption text-grey mt-3 mb-0">
                    Powered by 
                    <img src="https://js.stripe.com/v3/fingerprinted/img/stripe_logo_slate-22e8f5d3e.svg" alt="Stripe" height="16" class="mx-1" style="vertical-align: middle;">
                  </p>
                </div>
              </v-card>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </v-app>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(false);
const saving = ref(false);
const showSuccess = ref(false);
const agreeToTerms = ref(false);
const savedMethods = ref([]);
const successDetails = ref({ type: '', last4: '' });

let stripe = null;
let elements = null;
let paymentElement = null;

const loadSavedMethods = async () => {
  try {
    const res = await axios.get('/api/client/payments/methods');
    savedMethods.value = res.data.data || [];
  } catch (e) {
    console.error('Error loading saved methods:', e);
  }
};

const initStripe = async () => {
  loading.value = true;
  try {
    // Get setup intent client secret
    const res = await axios.post('/api/client/payments/setup-intent');
    const clientSecret = res.data.client_secret;

    // Initialize Stripe
    if (!window.Stripe) {
      alert('Stripe.js failed to load. Please refresh the page.');
      return;
    }

    stripe = window.Stripe(window.STRIPE_PUBLISHABLE_KEY || import.meta.env.VITE_STRIPE_KEY);
    
    // Create Stripe Elements
    elements = stripe.elements({
      clientSecret,
      appearance: {
        theme: 'stripe',
        variables: {
          colorPrimary: '#1976d2',
          colorBackground: '#ffffff',
          colorText: '#1a1a1a',
          colorDanger: '#f44336',
          fontFamily: 'Roboto, sans-serif',
          spacingUnit: '4px',
          borderRadius: '12px',
        },
      },
    });

    // Create and mount payment element
    paymentElement = elements.create('payment', {
      layout: {
        type: 'tabs',
        defaultCollapsed: false,
      },
    });
    paymentElement.mount('#payment-element');
  } catch (e) {
    console.error('Error initializing Stripe:', e);
    alert('Failed to initialize payment form. Please try again.');
  } finally {
    loading.value = false;
  }
};

const savePaymentMethod = async () => {
  if (!agreeToTerms.value) {
    alert('Please agree to the terms and conditions');
    return;
  }

  saving.value = true;
  try {
    // Confirm the setup
    const { setupIntent, error } = await stripe.confirmSetup({
      elements,
      redirect: 'if_required',
      confirmParams: {
        return_url: window.location.href,
      },
    });

    if (error) {
      alert(error.message || 'Failed to save payment method');
      saving.value = false;
      return;
    }

    // Attach payment method to customer on server
    await axios.post('/api/client/payments/attach', {
      payment_method: setupIntent.payment_method,
    });

    // Get the payment method details for success message
    const pmRes = await axios.get('/api/client/payments/methods');
    const methods = pmRes.data.data || [];
    if (methods.length > 0) {
      const latestMethod = methods[0];
      successDetails.value = {
        type: capitalize(latestMethod.card?.brand || 'Card'),
        last4: latestMethod.card?.last4 || '****',
      };
    }

    // Show success
    showSuccess.value = true;
    await loadSavedMethods();
  } catch (e) {
    console.error('Error saving payment method:', e);
    alert(e.response?.data?.message || 'Failed to save payment method. Please try again.');
  } finally {
    saving.value = false;
  }
};

const addAnother = () => {
  showSuccess.value = false;
  agreeToTerms.value = false;
  initStripe(); // Reinitialize for next card
};

const goToDashboard = () => {
  window.location.href = '/client/dashboard';
};

const capitalize = (str) => {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

const getCardColor = (brand) => {
  const colors = {
    visa: '#1A1F71',
    mastercard: '#EB001B',
    amex: '#006FCF',
    discover: '#FF6000',
  };
  return colors[brand?.toLowerCase()] || '#1976d2';
};

onMounted(async () => {
  await loadSavedMethods();
  await initStripe();
});
</script>

<style scoped>
.client-payment-setup-container {
  min-height: 100vh;
  background: #f5f5f5;
}

/* Left Column - Branding */
.left-column {
  background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
  min-height: 100vh;
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
  background: 
    radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
  pointer-events: none;
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
  color: #ffffff;
  line-height: 1.2;
}

.welcome-subtitle {
  font-size: 1.1rem;
  color: #e3f2fd;
  line-height: 1.6;
}

.benefits-list {
  background: transparent !important;
}

.benefits-list .v-list-item {
  padding: 16px 0 !important;
}

.security-badges {
  padding-top: 24px;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

/* Right Column - Form */
.right-column {
  background: #ffffff;
  min-height: 100vh;
  overflow-y: auto;
}

.form-content {
  max-width: 650px;
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
  line-height: 1.5;
}

.form-card {
  border-radius: 16px !important;
  border: 1px solid #e0e0e0;
}

.success-card {
  border-radius: 16px !important;
  animation: slideIn 0.5s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.stripe-element-wrapper {
  position: relative;
}

.stripe-label {
  font-weight: 600;
  color: #424242;
}

.stripe-element {
  background: #fafafa;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
  min-height: 44px;
}

#payment-element {
  background: #fafafa;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
}

#payment-element:focus-within {
  border-color: #1976d2;
  box-shadow: 0 0 0 4px rgba(25, 118, 210, 0.1);
  background: #ffffff;
}

.save-btn {
  font-size: 18px !important;
  font-weight: 600 !important;
  text-transform: none !important;
  letter-spacing: 0.5px !important;
  border-radius: 12px !important;
  transition: all 0.3s ease !important;
}

.save-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(25, 118, 210, 0.4) !important;
}

.security-footer {
  border-top: 1px solid #e0e0e0;
}

/* Responsive */
@media (max-width: 960px) {
  .left-column {
    min-height: auto;
    padding: 40px 24px !important;
  }

  .welcome-title {
    font-size: 2rem;
  }

  .right-column {
    min-height: auto;
  }

  .form-content {
    padding: 24px 16px;
  }
}
</style>
