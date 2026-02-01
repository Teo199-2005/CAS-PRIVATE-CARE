<template>
  <v-dialog v-model="showDialog" max-width="600" persistent>
    <v-card class="payment-retry-modal" elevation="8">
      <!-- Header -->
      <v-card-title class="payment-retry-header pa-6">
        <div class="d-flex align-center">
          <v-avatar color="error" size="48" class="mr-4">
            <v-icon color="white" size="28">mdi-credit-card-off</v-icon>
          </v-avatar>
          <div>
            <h2 class="text-h5 font-weight-bold text-white mb-1">Payment Failed</h2>
            <p class="text-body-2 text-grey-lighten-2 mb-0">Let's fix this together</p>
          </div>
        </div>
      </v-card-title>

      <!-- Content -->
      <v-card-text class="pa-6">
        <!-- Error Details -->
        <v-alert 
          type="error" 
          variant="tonal" 
          class="mb-6"
          border="start"
        >
          <div class="d-flex align-start">
            <div>
              <strong class="d-block mb-1">{{ errorTitle }}</strong>
              <span class="text-body-2">{{ errorMessage }}</span>
            </div>
          </div>
        </v-alert>

        <!-- Booking Summary -->
        <div v-if="booking" class="booking-summary mb-6 pa-4 rounded-lg" style="background: #f8fafc;">
          <div class="d-flex justify-space-between align-center mb-2">
            <span class="text-body-2 text-grey">Booking ID</span>
            <strong>#{{ booking.id }}</strong>
          </div>
          <div class="d-flex justify-space-between align-center mb-2">
            <span class="text-body-2 text-grey">Service</span>
            <strong>{{ booking.service_type }}</strong>
          </div>
          <div class="d-flex justify-space-between align-center mb-2">
            <span class="text-body-2 text-grey">Date</span>
            <strong>{{ formatDate(booking.service_date) }}</strong>
          </div>
          <v-divider class="my-3"></v-divider>
          <div class="d-flex justify-space-between align-center">
            <span class="text-body-1 font-weight-bold">Amount Due</span>
            <span class="text-h5 font-weight-bold text-primary">${{ formatAmount(booking.total_budget) }}</span>
          </div>
        </div>

        <!-- Payment Method Selection -->
        <div class="mb-6">
          <h4 class="text-subtitle-1 font-weight-bold mb-3">
            <v-icon size="20" class="mr-2">mdi-credit-card</v-icon>
            Select Payment Method
          </h4>

          <!-- Existing Payment Methods -->
          <div v-if="paymentMethods.length > 0" class="payment-methods-list mb-4">
            <v-radio-group v-model="selectedMethodId" hide-details>
              <div 
                v-for="method in paymentMethods" 
                :key="method.id"
                class="payment-method-option pa-3 mb-2 rounded-lg cursor-pointer"
                :class="{ 'selected': selectedMethodId === method.id }"
                @click="selectedMethodId = method.id"
              >
                <v-radio :value="method.id" class="ma-0">
                  <template #label>
                    <div class="d-flex align-center w-100">
                      <v-icon :icon="getCardIcon(method.card.brand)" class="mr-3" size="32"></v-icon>
                      <div class="flex-grow-1">
                        <div class="font-weight-medium">
                          {{ method.card.brand.toUpperCase() }} •••• {{ method.card.last4 }}
                        </div>
                        <div class="text-caption text-grey">
                          Expires {{ method.card.exp_month }}/{{ method.card.exp_year }}
                        </div>
                      </div>
                      <v-chip 
                        v-if="method.id === failedMethodId" 
                        color="error" 
                        size="x-small"
                        class="ml-2"
                      >
                        Failed
                      </v-chip>
                    </div>
                  </template>
                </v-radio>
              </div>
            </v-radio-group>
          </div>

          <!-- Add New Card Option -->
          <v-btn
            variant="outlined"
            color="primary"
            block
            class="mb-2"
            @click="showAddCard = true"
          >
            <v-icon start>mdi-plus</v-icon>
            Add New Payment Method
          </v-btn>
        </div>

        <!-- Retry Tips -->
        <v-expansion-panels variant="accordion" class="mb-4">
          <v-expansion-panel>
            <v-expansion-panel-title class="text-body-2">
              <v-icon size="18" color="info" class="mr-2">mdi-lightbulb-outline</v-icon>
              Common reasons for payment failure
            </v-expansion-panel-title>
            <v-expansion-panel-text>
              <ul class="text-body-2 pl-4">
                <li class="mb-2">Insufficient funds in account</li>
                <li class="mb-2">Card expired or about to expire</li>
                <li class="mb-2">Transaction blocked by bank (try calling them)</li>
                <li class="mb-2">Incorrect billing address or ZIP code</li>
                <li class="mb-2">Daily transaction limit reached</li>
              </ul>
            </v-expansion-panel-text>
          </v-expansion-panel>
        </v-expansion-panels>
      </v-card-text>

      <!-- Actions -->
      <v-card-actions class="pa-6 pt-0">
        <v-btn
          variant="text"
          color="grey"
          @click="handleClose"
          :disabled="processing"
        >
          Cancel
        </v-btn>
        <v-spacer></v-spacer>
        <v-btn
          variant="flat"
          color="success"
          size="large"
          :loading="processing"
          :disabled="!selectedMethodId || processing"
          @click="retryPayment"
        >
          <v-icon start>mdi-refresh</v-icon>
          Retry Payment
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- Add Card Dialog -->
  <v-dialog v-model="showAddCard" max-width="500">
    <v-card>
      <v-card-title class="pa-6">
        <v-icon class="mr-2">mdi-credit-card-plus</v-icon>
        Add Payment Method
      </v-card-title>
      <v-card-text class="pa-6 pt-0">
        <!-- Stripe Card Element Container -->
        <div id="payment-retry-card-element" class="stripe-element pa-4 rounded border mb-4"></div>
        <div id="payment-retry-card-errors" class="text-error text-caption"></div>
      </v-card-text>
      <v-card-actions class="pa-6 pt-0">
        <v-btn variant="text" @click="showAddCard = false">Cancel</v-btn>
        <v-spacer></v-spacer>
        <v-btn 
          color="primary" 
          variant="flat"
          :loading="addingCard"
          @click="addNewCard"
        >
          Add Card
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  booking: {
    type: Object,
    required: true
  },
  errorCode: {
    type: String,
    default: 'generic_decline'
  },
  errorMessage: {
    type: String,
    default: 'Your payment could not be processed. Please try again or use a different payment method.'
  },
  failedMethodId: {
    type: String,
    default: null
  }
});

const emit = defineEmits(['update:modelValue', 'retry-success', 'retry-failed', 'close']);

// State
const showDialog = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const paymentMethods = ref([]);
const selectedMethodId = ref(null);
const processing = ref(false);
const showAddCard = ref(false);
const addingCard = ref(false);
let stripe = null;
let cardElement = null;

// Error title based on error code
const errorTitle = computed(() => {
  const errorTitles = {
    'card_declined': 'Card Declined',
    'insufficient_funds': 'Insufficient Funds',
    'expired_card': 'Card Expired',
    'incorrect_cvc': 'Incorrect Security Code',
    'processing_error': 'Processing Error',
    'generic_decline': 'Payment Declined'
  };
  return errorTitles[props.errorCode] || 'Payment Failed';
});

// Load payment methods
const loadPaymentMethods = async () => {
  try {
    const response = await fetch('/api/stripe/payment-methods', {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
      },
      credentials: 'same-origin'
    });
    
    if (response.ok) {
      const data = await response.json();
      paymentMethods.value = data.payment_methods || [];
      
      // Pre-select a method (prefer one that wasn't the failed one)
      if (paymentMethods.value.length > 0) {
        const nonFailedMethod = paymentMethods.value.find(m => m.id !== props.failedMethodId);
        selectedMethodId.value = nonFailedMethod?.id || paymentMethods.value[0].id;
      }
    }
  } catch (error) {
    console.error('Failed to load payment methods:', error);
  }
};

// Initialize Stripe when add card dialog opens
watch(showAddCard, async (show) => {
  if (show) {
    await nextTick();
    initializeStripeElement();
  }
});

const initializeStripeElement = () => {
  if (!window.Stripe) {
    console.error('Stripe.js not loaded');
    return;
  }

  const stripeKey = document.querySelector('meta[name="stripe-key"]')?.content;
  if (!stripeKey) {
    console.error('Stripe key not found');
    return;
  }

  stripe = window.Stripe(stripeKey);
  const elements = stripe.elements();
  
  cardElement = elements.create('card', {
    style: {
      base: {
        fontSize: '16px',
        color: '#0f172a',
        '::placeholder': { color: '#94a3b8' }
      },
      invalid: {
        color: '#ef4444'
      }
    }
  });

  const container = document.getElementById('payment-retry-card-element');
  if (container) {
    cardElement.mount('#payment-retry-card-element');
    
    cardElement.on('change', (event) => {
      const errorElement = document.getElementById('payment-retry-card-errors');
      if (errorElement) {
        errorElement.textContent = event.error ? event.error.message : '';
      }
    });
  }
};

// Add new card
const addNewCard = async () => {
  if (!stripe || !cardElement) return;
  
  addingCard.value = true;
  
  try {
    // Create setup intent first
    const setupResponse = await fetch('/api/stripe/create-setup-intent', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
      },
      credentials: 'same-origin'
    });
    
    const setupData = await setupResponse.json();
    
    if (!setupData.client_secret) {
      throw new Error('Failed to create setup intent');
    }
    
    // Confirm card setup
    const { error, setupIntent } = await stripe.confirmCardSetup(setupData.client_secret, {
      payment_method: { card: cardElement }
    });
    
    if (error) {
      throw error;
    }
    
    // Reload payment methods and select the new one
    await loadPaymentMethods();
    selectedMethodId.value = setupIntent.payment_method;
    showAddCard.value = false;
    
  } catch (error) {
    console.error('Failed to add card:', error);
    const errorElement = document.getElementById('payment-retry-card-errors');
    if (errorElement) {
      errorElement.textContent = error.message || 'Failed to add card';
    }
  } finally {
    addingCard.value = false;
  }
};

// Retry payment
const retryPayment = async () => {
  if (!selectedMethodId.value || !props.booking) return;
  
  processing.value = true;
  
  try {
    const response = await fetch('/api/stripe/setup-intent', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        payment_method_id: selectedMethodId.value,
        booking_id: props.booking.id
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      emit('retry-success', data);
      showDialog.value = false;
    } else {
      emit('retry-failed', data);
    }
    
  } catch (error) {
    console.error('Payment retry failed:', error);
    emit('retry-failed', { message: error.message });
  } finally {
    processing.value = false;
  }
};

// Close handler
const handleClose = () => {
  if (!processing.value) {
    showDialog.value = false;
    emit('close');
  }
};

// Card icon helper
const getCardIcon = (brand) => {
  const icons = {
    visa: 'mdi-credit-card',
    mastercard: 'mdi-credit-card',
    amex: 'mdi-credit-card',
    discover: 'mdi-credit-card',
    default: 'mdi-credit-card'
  };
  return icons[brand?.toLowerCase()] || icons.default;
};

// Format helpers
const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const formatAmount = (amount) => {
  if (!amount) return '0.00';
  return parseFloat(amount).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

// Initialize
onMounted(() => {
  if (props.modelValue) {
    loadPaymentMethods();
  }
});

watch(() => props.modelValue, (show) => {
  if (show) {
    loadPaymentMethods();
  }
});
</script>

<style scoped>
.payment-retry-modal {
  border-radius: 16px;
  overflow: hidden;
}

.payment-retry-header {
  background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
}

.payment-method-option {
  border: 2px solid #e2e8f0;
  transition: all 0.2s ease;
}

.payment-method-option:hover {
  border-color: #94a3b8;
  background: #f8fafc;
}

.payment-method-option.selected {
  border-color: #0B4FA2;
  background: #eff6ff;
}

.stripe-element {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  min-height: 44px;
}

.cursor-pointer {
  cursor: pointer;
}
</style>
