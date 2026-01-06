<template>
  <v-card>
    <v-card-title class="bg-primary text-white">
      <v-icon start>mdi-credit-card-plus</v-icon>
      Add Payment Method
    </v-card-title>

    <v-card-text class="pa-6">
      <!-- Success Message -->
      <v-alert v-if="successMessage" type="success" class="mb-4" dismissible @click:close="successMessage = ''">
        {{ successMessage }}
      </v-alert>

      <!-- Error Message -->
      <v-alert v-if="errorMessage" type="error" class="mb-4" dismissible @click:close="errorMessage = ''">
        {{ errorMessage }}
      </v-alert>

      <!-- Loading State -->
      <div v-if="loading" class="text-center pa-8">
        <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
        <p class="mt-4">Setting up payment form...</p>
      </div>

      <!-- Payment Form -->
      <div v-else>
        <v-alert type="info" class="mb-4" variant="tonal">
          <div class="d-flex align-center">
            <v-icon start>mdi-lock</v-icon>
            <div>
              <strong>Secure Payment Processing</strong><br>
              Your card will be saved securely. You'll only be charged after services are completed based on actual hours worked.
            </div>
          </div>
        </v-alert>

        <!-- Stripe Card Element -->
        <div class="stripe-card-wrapper">
          <label class="text-subtitle-2 mb-2 d-block">Card Information</label>
          <div id="card-element" class="stripe-card-element"></div>
          <div id="card-errors" class="text-error text-caption mt-2" role="alert"></div>
        </div>

        <!-- Billing Details -->
        <v-row class="mt-4">
          <v-col cols="12" md="6">
            <v-text-field
              v-model="billingDetails.name"
              label="Cardholder Name"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="mdi-account"
              required
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field
              v-model="billingDetails.email"
              label="Email"
              type="email"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="mdi-email"
              required
            ></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="billingDetails.address"
              label="Billing Address"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="mdi-map-marker"
              required
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="billingDetails.city"
              label="City"
              variant="outlined"
              density="comfortable"
              required
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="billingDetails.state"
              label="State"
              variant="outlined"
              density="comfortable"
              required
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="billingDetails.zip"
              label="ZIP Code"
              variant="outlined"
              density="comfortable"
              required
            ></v-text-field>
          </v-col>
        </v-row>

        <!-- Terms -->
        <v-checkbox v-model="agreedToTerms" class="mt-2">
          <template v-slot:label>
            <div class="text-caption">
              I agree to save this payment method for future charges based on services rendered
            </div>
          </template>
        </v-checkbox>
      </div>
    </v-card-text>

    <v-card-actions class="pa-6 pt-0">
      <v-spacer></v-spacer>
      <v-btn variant="text" @click="$emit('cancel')">Cancel</v-btn>
      <v-btn
        color="primary"
        :loading="processing"
        :disabled="!agreedToTerms || loading"
        @click="savePaymentMethod"
      >
        <v-icon start>mdi-lock</v-icon>
        Save Card Securely
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const emit = defineEmits(['success', 'cancel']);

// State
const loading = ref(true);
const processing = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const agreedToTerms = ref(false);

const billingDetails = ref({
  name: '',
  email: '',
  address: '',
  city: '',
  state: '',
  zip: ''
});

// Stripe elements
let stripe = null;
let cardElement = null;
let clientSecret = null;

onMounted(async () => {
  try {
    // Load Stripe.js
    stripe = Stripe(import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY);
    
    // Create Setup Intent
    const response = await fetch('/api/stripe/create-setup-intent', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });

    const data = await response.json();

    if (!data.success) {
      throw new Error(data.error || 'Failed to initialize payment form');
    }

    clientSecret = data.client_secret;

    // Create card element
    const elements = stripe.elements();
    cardElement = elements.create('card', {
      style: {
        base: {
          fontSize: '16px',
          color: '#32325d',
          fontFamily: '"Roboto", sans-serif',
          '::placeholder': {
            color: '#aab7c4'
          }
        },
        invalid: {
          color: '#fa755a',
          iconColor: '#fa755a'
        }
      }
    });

    cardElement.mount('#card-element');

    // Handle real-time validation errors
    cardElement.on('change', (event) => {
      const displayError = document.getElementById('card-errors');
      if (event.error) {
        displayError.textContent = event.error.message;
      } else {
        displayError.textContent = '';
      }
    });

    loading.value = false;
  } catch (error) {
    errorMessage.value = error.message;
    loading.value = false;
  }
});

const savePaymentMethod = async () => {
  if (!agreedToTerms.value) {
    errorMessage.value = 'Please agree to the terms';
    return;
  }

  processing.value = true;
  errorMessage.value = '';

  try {
    // Confirm Setup Intent
    const { setupIntent, error } = await stripe.confirmCardSetup(clientSecret, {
      payment_method: {
        card: cardElement,
        billing_details: {
          name: billingDetails.value.name,
          email: billingDetails.value.email,
          address: {
            line1: billingDetails.value.address,
            city: billingDetails.value.city,
            state: billingDetails.value.state,
            postal_code: billingDetails.value.zip,
            country: 'US'
          }
        }
      }
    });

    if (error) {
      throw new Error(error.message);
    }

    // Save payment method ID to backend
    const saveResponse = await fetch('/api/stripe/save-payment-method', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        payment_method_id: setupIntent.payment_method
      })
    });

    const saveData = await saveResponse.json();

    if (!saveData.success) {
      throw new Error(saveData.error || 'Failed to save payment method');
    }

    successMessage.value = 'Payment method saved successfully!';
    
    setTimeout(() => {
      emit('success');
    }, 1500);

  } catch (error) {
    errorMessage.value = error.message;
  } finally {
    processing.value = false;
  }
};
</script>

<style scoped>
.stripe-card-wrapper {
  margin-bottom: 16px;
}

.stripe-card-element {
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  padding: 12px;
  background-color: white;
  transition: border-color 0.2s;
}

.stripe-card-element:hover {
  border-color: #9e9e9e;
}

.stripe-card-element:focus-within {
  border-color: #1976d2;
  border-width: 2px;
  padding: 11px;
}
</style>
