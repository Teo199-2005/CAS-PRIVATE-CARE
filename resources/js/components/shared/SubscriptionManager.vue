<template>
  <div class="subscription-manager">
    <!-- Loading State -->
    <div v-if="loading" class="d-flex justify-center align-center py-8">
      <v-progress-circular
        indeterminate
        color="primary"
        size="48"
        aria-label="Loading subscription information"
      />
    </div>

    <!-- Error State -->
    <v-alert
      v-else-if="error"
      type="error"
      variant="tonal"
      class="mb-4"
      closable
      @click:close="error = null"
    >
      {{ error }}
    </v-alert>

    <!-- Main Content -->
    <template v-else>
      <!-- Current Subscription Status -->
      <v-card class="mb-4" :class="statusCardClass">
        <v-card-item>
          <template #prepend>
            <v-avatar :color="statusColor" size="48">
              <v-icon :icon="statusIcon" color="white" />
            </v-avatar>
          </template>
          
          <v-card-title class="text-h6">
            {{ statusTitle }}
          </v-card-title>
          
          <v-card-subtitle>
            {{ statusSubtitle }}
          </v-card-subtitle>

          <template #append>
            <v-chip
              :color="subscription?.status === 'active' ? 'success' : 'warning'"
              variant="flat"
              size="small"
            >
              {{ subscription?.status || 'No subscription' }}
            </v-chip>
          </template>
        </v-card-item>

        <!-- Subscription Details -->
        <v-card-text v-if="subscription">
          <v-row>
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis">Plan</div>
              <div class="text-body-1 font-weight-medium">
                {{ subscription.plan_name || 'Standard' }}
              </div>
            </v-col>
            
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis">Amount</div>
              <div class="text-body-1 font-weight-medium">
                {{ formatCurrency(subscription.amount) }}/{{ subscription.interval || 'month' }}
              </div>
            </v-col>
            
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis">Next Billing</div>
              <div class="text-body-1 font-weight-medium">
                {{ formatDate(subscription.next_billing_date) }}
              </div>
            </v-col>
            
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis">Since</div>
              <div class="text-body-1 font-weight-medium">
                {{ formatDate(subscription.created_at) }}
              </div>
            </v-col>
          </v-row>

          <!-- Trial Notice -->
          <v-alert
            v-if="isInTrial"
            type="info"
            variant="tonal"
            density="compact"
            class="mt-4"
          >
            <strong>Trial Period:</strong> {{ trialDaysRemaining }} days remaining.
            Your subscription will begin on {{ formatDate(subscription.trial_ends_at) }}.
          </v-alert>

          <!-- Cancellation Notice -->
          <v-alert
            v-if="isCancelling"
            type="warning"
            variant="tonal"
            density="compact"
            class="mt-4"
          >
            <strong>Cancellation Scheduled:</strong> Your subscription will end on
            {{ formatDate(subscription.ends_at) }}. You can resume before this date.
          </v-alert>
        </v-card-text>

        <!-- Actions -->
        <v-card-actions class="pa-4">
          <v-spacer />
          
          <template v-if="!subscription">
            <v-btn
              color="primary"
              variant="flat"
              @click="showPlans = true"
            >
              <v-icon start>mdi-plus</v-icon>
              Subscribe Now
            </v-btn>
          </template>
          
          <template v-else-if="subscription.status === 'active'">
            <v-btn
              variant="outlined"
              color="primary"
              @click="showPlans = true"
            >
              <v-icon start>mdi-swap-horizontal</v-icon>
              Change Plan
            </v-btn>
            
            <v-btn
              variant="outlined"
              color="warning"
              @click="showCancelDialog = true"
            >
              <v-icon start>mdi-cancel</v-icon>
              Cancel
            </v-btn>
          </template>
          
          <template v-else-if="isCancelling">
            <v-btn
              color="success"
              variant="flat"
              :loading="resuming"
              @click="resumeSubscription"
            >
              <v-icon start>mdi-play</v-icon>
              Resume Subscription
            </v-btn>
          </template>
        </v-card-actions>
      </v-card>

      <!-- Payment Method -->
      <v-card class="mb-4">
        <v-card-title class="d-flex align-center">
          <v-icon start>mdi-credit-card</v-icon>
          Payment Method
        </v-card-title>
        
        <v-card-text>
          <template v-if="paymentMethod">
            <div class="d-flex align-center">
              <v-icon
                :icon="getCardIcon(paymentMethod.brand)"
                size="32"
                class="mr-3"
              />
              <div>
                <div class="text-body-1 font-weight-medium">
                  {{ paymentMethod.brand }} •••• {{ paymentMethod.last4 }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  Expires {{ paymentMethod.exp_month }}/{{ paymentMethod.exp_year }}
                </div>
              </div>
              <v-spacer />
              <v-btn
                variant="text"
                color="primary"
                @click="showUpdatePayment = true"
              >
                Update
              </v-btn>
            </div>
          </template>
          
          <template v-else>
            <div class="text-center py-4">
              <v-icon size="48" color="grey-lighten-1" class="mb-2">
                mdi-credit-card-off
              </v-icon>
              <div class="text-body-2 text-medium-emphasis mb-3">
                No payment method on file
              </div>
              <v-btn
                color="primary"
                variant="outlined"
                @click="showUpdatePayment = true"
              >
                Add Payment Method
              </v-btn>
            </div>
          </template>
        </v-card-text>
      </v-card>

      <!-- Billing History -->
      <v-card>
        <v-card-title class="d-flex align-center">
          <v-icon start>mdi-receipt</v-icon>
          Billing History
          <v-spacer />
          <v-btn
            variant="text"
            color="primary"
            size="small"
            @click="downloadAllInvoices"
          >
            Download All
          </v-btn>
        </v-card-title>
        
        <v-divider />
        
        <v-list v-if="invoices.length > 0" density="compact">
          <v-list-item
            v-for="invoice in invoices"
            :key="invoice.id"
            class="py-2"
          >
            <template #prepend>
              <v-avatar
                :color="invoice.status === 'paid' ? 'success' : 'warning'"
                size="36"
              >
                <v-icon
                  :icon="invoice.status === 'paid' ? 'mdi-check' : 'mdi-clock'"
                  color="white"
                  size="18"
                />
              </v-avatar>
            </template>
            
            <v-list-item-title>
              {{ formatCurrency(invoice.amount) }}
            </v-list-item-title>
            
            <v-list-item-subtitle>
              {{ formatDate(invoice.created_at) }} • {{ invoice.description }}
            </v-list-item-subtitle>
            
            <template #append>
              <v-btn
                variant="text"
                icon="mdi-download"
                size="small"
                :href="invoice.invoice_url"
                target="_blank"
                :aria-label="`Download invoice for ${formatDate(invoice.created_at)}`"
              />
            </template>
          </v-list-item>
        </v-list>
        
        <v-card-text v-else class="text-center py-8">
          <v-icon size="48" color="grey-lighten-1" class="mb-2">
            mdi-receipt-text
          </v-icon>
          <div class="text-body-2 text-medium-emphasis">
            No billing history available
          </div>
        </v-card-text>
      </v-card>
    </template>

    <!-- Plans Dialog -->
    <v-dialog v-model="showPlans" max-width="800" scrollable>
      <v-card>
        <v-toolbar color="primary" density="compact">
          <v-toolbar-title>Choose a Plan</v-toolbar-title>
          <v-btn icon="mdi-close" @click="showPlans = false" />
        </v-toolbar>
        
        <v-card-text class="pa-4">
          <v-row>
            <v-col
              v-for="plan in availablePlans"
              :key="plan.id"
              cols="12"
              md="4"
            >
              <v-card
                :variant="plan.recommended ? 'flat' : 'outlined'"
                :color="plan.recommended ? 'primary' : undefined"
                class="h-100"
              >
                <v-chip
                  v-if="plan.recommended"
                  color="warning"
                  size="small"
                  class="position-absolute"
                  style="top: -8px; right: 16px;"
                >
                  Recommended
                </v-chip>
                
                <v-card-item>
                  <v-card-title class="text-h6">{{ plan.name }}</v-card-title>
                  <v-card-subtitle>{{ plan.description }}</v-card-subtitle>
                </v-card-item>
                
                <v-card-text>
                  <div class="text-h4 font-weight-bold mb-4">
                    {{ formatCurrency(plan.price) }}
                    <span class="text-body-2 font-weight-regular">/{{ plan.interval }}</span>
                  </div>
                  
                  <v-list density="compact" class="bg-transparent">
                    <v-list-item
                      v-for="feature in plan.features"
                      :key="feature"
                      :prepend-icon="'mdi-check'"
                      class="px-0"
                    >
                      {{ feature }}
                    </v-list-item>
                  </v-list>
                </v-card-text>
                
                <v-card-actions class="pa-4">
                  <v-btn
                    :color="plan.recommended ? 'warning' : 'primary'"
                    :variant="plan.recommended ? 'flat' : 'outlined'"
                    block
                    :loading="subscribing === plan.id"
                    @click="selectPlan(plan)"
                  >
                    {{ subscription ? 'Switch to Plan' : 'Select Plan' }}
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Cancel Subscription Dialog -->
    <v-dialog v-model="showCancelDialog" max-width="500">
      <v-card>
        <v-card-title class="text-h6">
          <v-icon start color="warning">mdi-alert</v-icon>
          Cancel Subscription
        </v-card-title>
        
        <v-card-text>
          <p class="mb-4">
            Are you sure you want to cancel your subscription? You'll continue to have access
            until the end of your current billing period.
          </p>
          
          <v-textarea
            v-model="cancelReason"
            label="Reason for cancellation (optional)"
            hint="Your feedback helps us improve"
            rows="3"
            variant="outlined"
          />
        </v-card-text>
        
        <v-card-actions>
          <v-spacer />
          <v-btn
            variant="text"
            @click="showCancelDialog = false"
          >
            Keep Subscription
          </v-btn>
          <v-btn
            color="error"
            variant="flat"
            :loading="cancelling"
            @click="cancelSubscription"
          >
            Cancel Subscription
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Update Payment Method Dialog -->
    <v-dialog v-model="showUpdatePayment" max-width="500">
      <v-card>
        <v-card-title class="text-h6">
          Update Payment Method
        </v-card-title>
        
        <v-card-text>
          <div id="card-element" class="stripe-card-element mb-4" />
          
          <v-alert
            v-if="cardError"
            type="error"
            variant="tonal"
            density="compact"
            class="mb-0"
          >
            {{ cardError }}
          </v-alert>
        </v-card-text>
        
        <v-card-actions>
          <v-spacer />
          <v-btn
            variant="text"
            @click="showUpdatePayment = false"
          >
            Cancel
          </v-btn>
          <v-btn
            color="primary"
            variant="flat"
            :loading="updatingPayment"
            @click="updatePaymentMethod"
          >
            Update
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Success Snackbar -->
    <v-snackbar
      v-model="showSuccess"
      color="success"
      timeout="4000"
    >
      {{ successMessage }}
    </v-snackbar>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { loadStripe } from '@stripe/stripe-js';

const props = defineProps({
  userId: {
    type: [Number, String],
    required: true,
  },
});

const emit = defineEmits(['subscription-changed', 'error']);

// State
const loading = ref(true);
const error = ref(null);
const subscription = ref(null);
const paymentMethod = ref(null);
const invoices = ref([]);
const availablePlans = ref([]);

// Dialog states
const showPlans = ref(false);
const showCancelDialog = ref(false);
const showUpdatePayment = ref(false);

// Action states
const subscribing = ref(null);
const cancelling = ref(false);
const resuming = ref(false);
const updatingPayment = ref(false);
const cancelReason = ref('');
const cardError = ref(null);

// Success feedback
const showSuccess = ref(false);
const successMessage = ref('');

// Stripe elements
let stripe = null;
let cardElement = null;

// Computed properties
const statusColor = computed(() => {
  if (!subscription.value) return 'grey';
  if (subscription.value.status === 'active') return 'success';
  if (subscription.value.status === 'trialing') return 'info';
  if (subscription.value.status === 'past_due') return 'error';
  return 'warning';
});

const statusIcon = computed(() => {
  if (!subscription.value) return 'mdi-credit-card-off';
  if (subscription.value.status === 'active') return 'mdi-check-circle';
  if (subscription.value.status === 'trialing') return 'mdi-clock-outline';
  if (subscription.value.status === 'past_due') return 'mdi-alert-circle';
  return 'mdi-pause-circle';
});

const statusTitle = computed(() => {
  if (!subscription.value) return 'No Active Subscription';
  if (subscription.value.status === 'active') return 'Subscription Active';
  if (subscription.value.status === 'trialing') return 'Trial Period';
  if (subscription.value.status === 'past_due') return 'Payment Past Due';
  return 'Subscription Paused';
});

const statusSubtitle = computed(() => {
  if (!subscription.value) return 'Subscribe to access premium features';
  return `Billing ${subscription.value.interval || 'monthly'}`;
});

const statusCardClass = computed(() => {
  if (subscription.value?.status === 'past_due') return 'border-error';
  return '';
});

const isInTrial = computed(() => {
  return subscription.value?.status === 'trialing';
});

const trialDaysRemaining = computed(() => {
  if (!subscription.value?.trial_ends_at) return 0;
  const trialEnd = new Date(subscription.value.trial_ends_at);
  const now = new Date();
  return Math.max(0, Math.ceil((trialEnd - now) / (1000 * 60 * 60 * 24)));
});

const isCancelling = computed(() => {
  return subscription.value?.ends_at && subscription.value?.status !== 'canceled';
});

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount / 100);
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const getCardIcon = (brand) => {
  const icons = {
    visa: 'mdi-credit-card',
    mastercard: 'mdi-credit-card',
    amex: 'mdi-credit-card',
    discover: 'mdi-credit-card',
  };
  return icons[brand?.toLowerCase()] || 'mdi-credit-card';
};

const fetchSubscriptionData = async () => {
  try {
    loading.value = true;
    error.value = null;
    
    const response = await fetch(`/api/subscriptions/status`, {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
    });
    
    if (!response.ok) throw new Error('Failed to fetch subscription data');
    
    const data = await response.json();
    subscription.value = data.subscription;
    paymentMethod.value = data.payment_method;
    invoices.value = data.invoices || [];
    availablePlans.value = data.plans || [];
  } catch (err) {
    error.value = err.message;
    emit('error', err);
  } finally {
    loading.value = false;
  }
};

const selectPlan = async (plan) => {
  try {
    subscribing.value = plan.id;
    error.value = null;
    
    const response = await fetch('/api/subscriptions/subscribe', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
      body: JSON.stringify({ plan_id: plan.id }),
    });
    
    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || 'Failed to subscribe');
    }
    
    const data = await response.json();
    
    if (data.requires_action) {
      // Handle 3D Secure authentication
      const result = await stripe.confirmCardPayment(data.client_secret);
      if (result.error) {
        throw new Error(result.error.message);
      }
    }
    
    subscription.value = data.subscription;
    showPlans.value = false;
    showSuccessMessage('Subscription updated successfully!');
    emit('subscription-changed', data.subscription);
  } catch (err) {
    error.value = err.message;
  } finally {
    subscribing.value = null;
  }
};

const cancelSubscription = async () => {
  try {
    cancelling.value = true;
    error.value = null;
    
    const response = await fetch('/api/subscriptions/cancel', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
      body: JSON.stringify({ reason: cancelReason.value }),
    });
    
    if (!response.ok) throw new Error('Failed to cancel subscription');
    
    const data = await response.json();
    subscription.value = data.subscription;
    showCancelDialog.value = false;
    cancelReason.value = '';
    showSuccessMessage('Subscription will be cancelled at end of billing period.');
    emit('subscription-changed', data.subscription);
  } catch (err) {
    error.value = err.message;
  } finally {
    cancelling.value = false;
  }
};

const resumeSubscription = async () => {
  try {
    resuming.value = true;
    error.value = null;
    
    const response = await fetch('/api/subscriptions/resume', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
    });
    
    if (!response.ok) throw new Error('Failed to resume subscription');
    
    const data = await response.json();
    subscription.value = data.subscription;
    showSuccessMessage('Subscription resumed successfully!');
    emit('subscription-changed', data.subscription);
  } catch (err) {
    error.value = err.message;
  } finally {
    resuming.value = false;
  }
};

const updatePaymentMethod = async () => {
  try {
    updatingPayment.value = true;
    cardError.value = null;
    
    const { token, error: stripeError } = await stripe.createToken(cardElement);
    
    if (stripeError) {
      cardError.value = stripeError.message;
      return;
    }
    
    const response = await fetch('/api/subscriptions/payment-method', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
      body: JSON.stringify({ token: token.id }),
    });
    
    if (!response.ok) throw new Error('Failed to update payment method');
    
    const data = await response.json();
    paymentMethod.value = data.payment_method;
    showUpdatePayment.value = false;
    showSuccessMessage('Payment method updated successfully!');
  } catch (err) {
    cardError.value = err.message;
  } finally {
    updatingPayment.value = false;
  }
};

const downloadAllInvoices = async () => {
  try {
    const response = await fetch('/api/subscriptions/invoices/download-all', {
      headers: {
        'Accept': 'application/pdf',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
    });
    
    if (!response.ok) throw new Error('Failed to download invoices');
    
    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'invoices.zip';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
  } catch (err) {
    error.value = err.message;
  }
};

const showSuccessMessage = (message) => {
  successMessage.value = message;
  showSuccess.value = true;
};

const initializeStripe = async () => {
  try {
    const stripeKey = document.querySelector('meta[name="stripe-key"]')?.content;
    if (stripeKey) {
      stripe = await loadStripe(stripeKey);
    }
  } catch (err) {
    console.error('Failed to initialize Stripe:', err);
  }
};

// Watch for payment dialog to mount card element
watch(showUpdatePayment, async (show) => {
  if (show && stripe) {
    await nextTick();
    const elements = stripe.elements();
    cardElement = elements.create('card', {
      style: {
        base: {
          fontSize: '16px',
          color: '#424770',
          '::placeholder': {
            color: '#aab7c4',
          },
        },
        invalid: {
          color: '#9e2146',
        },
      },
    });
    cardElement.mount('#card-element');
  }
});

// Lifecycle
onMounted(async () => {
  await Promise.all([
    initializeStripe(),
    fetchSubscriptionData(),
  ]);
});
</script>

<style scoped>
.subscription-manager {
  max-width: 800px;
  margin: 0 auto;
}

.stripe-card-element {
  padding: 12px;
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 4px;
  background: white;
}

.border-error {
  border: 2px solid rgb(var(--v-theme-error));
}
</style>
