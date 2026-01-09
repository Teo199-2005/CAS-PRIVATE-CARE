<template>
  <div class="auto-pay-toggle-container">
    <v-card outlined class="pa-4 mb-3">
      <div class="d-flex align-center justify-space-between">
        <div class="flex-grow-1">
          <h4 class="text-subtitle-1 font-weight-bold mb-1">
            <v-icon size="20" color="primary" class="mr-1">mdi-sync</v-icon>
            Auto-Pay for this Booking
          </h4>
          <p class="text-caption text-grey mb-0">
            {{ booking.auto_pay_enabled 
              ? 'Recurring payments are active' 
              : 'Enable automatic recurring payments' }}
          </p>
          <p v-if="booking.next_payment_date" class="text-caption text-success mt-1">
            Next payment: {{ formatDate(booking.next_payment_date) }}
          </p>
        </div>
        
        <div class="ml-4">
          <v-switch
            v-model="autoPayEnabled"
            :loading="loading"
            :disabled="loading || !hasPaymentMethod"
            color="success"
            hide-details
            @update:model-value="toggleAutoPay"
          ></v-switch>
        </div>
      </div>

      <!-- Warning if no payment method -->
      <v-alert
        v-if="!hasPaymentMethod && !autoPayEnabled"
        type="warning"
        density="compact"
        class="mt-3 mb-0"
        border="start"
      >
        <template #text>
          <span class="text-caption">
            Add a payment method in the 
            <a href="#" @click.prevent="$emit('goToPayments')" class="font-weight-bold">Payment Information</a> 
            section to enable auto-pay
          </span>
        </template>
      </v-alert>

      <!-- Auto-pay active info -->
      <v-alert
        v-if="autoPayEnabled && booking.stripe_subscription_id"
        type="success"
        density="compact"
        class="mt-3 mb-0"
        border="start"
      >
        <template #text>
          <div class="d-flex align-center justify-space-between">
            <span class="text-caption">
              <v-icon size="16" class="mr-1">mdi-check-circle</v-icon>
              Auto-pay is active - Subscription ID: {{ booking.stripe_subscription_id.substring(0, 20) }}...
            </span>
            <v-btn
              size="x-small"
              color="error"
              variant="text"
              @click="cancelSubscription"
              :loading="cancelling"
            >
              Cancel
            </v-btn>
          </div>
        </template>
      </v-alert>
    </v-card>

    <!-- Create Price Dialog (for first-time setup) -->
    <v-dialog v-model="showPriceDialog" max-width="500">
      <v-card>
        <v-card-title class="text-h6 bg-primary text-white">
          <v-icon class="mr-2">mdi-currency-usd</v-icon>
          Set Up Recurring Payment
        </v-card-title>
        
        <v-card-text class="pt-4">
          <p class="mb-4">Configure the recurring payment schedule for this booking.</p>
          
          <v-text-field
            v-model="recurringAmount"
            label="Monthly Amount"
            prefix="$"
            type="number"
            variant="outlined"
            :rules="[v => !!v || 'Amount is required', v => v > 0 || 'Amount must be greater than 0']"
            hint="This amount will be charged automatically each month"
            persistent-hint
          ></v-text-field>

          <v-alert type="info" density="compact" class="mt-3">
            Based on booking: ${{ booking.total_budget }} total budget
          </v-alert>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey" variant="text" @click="showPriceDialog = false">Cancel</v-btn>
          <v-btn color="primary" variant="flat" @click="createSubscription" :loading="loading">
            Enable Auto-Pay
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  booking: {
    type: Object,
    required: true
  },
  hasPaymentMethod: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['updated', 'goToPayments']);

const autoPayEnabled = ref(props.booking.auto_pay_enabled || false);
const loading = ref(false);
const cancelling = ref(false);
const showPriceDialog = ref(false);
const recurringAmount = ref((props.booking.total_budget / 30).toFixed(2)); // Estimate daily rate * 30

// Watch for booking updates from parent
watch(() => props.booking.auto_pay_enabled, (newVal) => {
  autoPayEnabled.value = newVal;
});

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
};

const toggleAutoPay = async (enabled) => {
  if (!props.hasPaymentMethod) {
    autoPayEnabled.value = false;
    return;
  }

  if (enabled) {
    // Show dialog to set up subscription
    showPriceDialog.value = true;
  } else {
    // Disable auto-pay (cancel subscription)
    await cancelSubscription();
  }
};

const createSubscription = async () => {
  if (!recurringAmount.value || recurringAmount.value <= 0) {
    alert('Please enter a valid amount');
    return;
  }

  loading.value = true;
  try {
    // First create a Stripe Price (or use existing price_id)
    // For now, we'll pass the amount and let backend create the price
    const response = await axios.post('/api/client/subscriptions', {
      booking_id: props.booking.id,
      amount: parseFloat(recurringAmount.value) * 100, // Convert to cents
      interval: 'month'
    });

    if (response.data.success) {
      autoPayEnabled.value = true;
      showPriceDialog.value = false;
      emit('updated', response.data.booking);
      
      // Success notification
      alert('Auto-pay enabled successfully! Your card will be charged automatically each month.');
    }
  } catch (error) {
    console.error('Error creating subscription:', error);
    autoPayEnabled.value = false;
    alert(error.response?.data?.message || 'Failed to enable auto-pay. Please try again.');
  } finally {
    loading.value = false;
  }
};

const cancelSubscription = async () => {
  if (!props.booking.stripe_subscription_id) {
    autoPayEnabled.value = false;
    return;
  }

  if (!confirm('Are you sure you want to cancel automatic payments for this booking?')) {
    autoPayEnabled.value = true; // Reset toggle
    return;
  }

  cancelling.value = true;
  loading.value = true;
  
  try {
    const response = await axios.post(`/api/client/subscriptions/${props.booking.stripe_subscription_id}/cancel`);
    
    if (response.data.success) {
      autoPayEnabled.value = false;
      emit('updated', response.data.booking);
      alert('Auto-pay has been canceled. No further automatic charges will occur.');
    }
  } catch (error) {
    console.error('Error canceling subscription:', error);
    autoPayEnabled.value = true; // Keep it enabled if cancel failed
    alert(error.response?.data?.message || 'Failed to cancel subscription. Please try again.');
  } finally {
    cancelling.value = false;
    loading.value = false;
  }
};
</script>

<style scoped>
.auto-pay-toggle-container {
  margin: 12px 0;
}

.detail-row {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
}

.detail-text {
  margin-left: 8px;
  font-size: 14px;
}
</style>
