<template>
  <div class="client-payment-methods">
    <!-- Notification Toast -->
    <notification-toast
      v-model="notification.show"
      :type="notification.type"
      :title="notification.title"
      :message="notification.message"
      :timeout="notification.timeout"
    />

    <!-- Confirmation Modal -->
    <alert-modal
      v-model="confirmDialog"
      :title="confirmData.title"
      :message="confirmData.message"
      :type="confirmData.type"
      :confirm-text="confirmData.confirmText"
      :show-cancel="true"
      @confirm="confirmData.callback"
    />

    <!-- Header -->
    <div class="payment-header">
      <div class="header-left">
        <v-icon color="primary" size="28" class="mr-3">mdi-credit-card-multiple</v-icon>
        <div>
          <h3 class="payment-title">Saved Payment Methods</h3>
          <p class="payment-subtitle">Manage your cards for recurring payments</p>
        </div>
      </div>
      <v-chip color="success" variant="outlined" v-if="methods.length > 0">
        <v-icon start size="16">mdi-shield-check</v-icon>
        {{ methods.length }} Card{{ methods.length > 1 ? 's' : '' }} Saved
      </v-chip>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
      <p class="mt-3">Loading payment methods...</p>
    </div>

    <!-- Content -->
    <div v-else>
      <!-- No Methods - Empty State -->
      <v-card v-if="methods.length === 0" class="no-methods-card" elevation="0" outlined>
        <v-card-text class="text-center pa-8">
          <div class="empty-state-icon mb-4">
            <v-icon size="100" color="primary" class="pulse-icon">mdi-credit-card-plus-outline</v-icon>
          </div>
          <h4 class="text-h5 font-weight-bold mb-3">Link Your Payment Method</h4>
          <p class="text-body-1 text-grey mb-2">
            Connect your card or bank account to enable automatic recurring payments
          </p>
          <p class="text-body-2 text-grey-darken-1 mb-5">
            <v-icon size="16" class="mr-1">mdi-shield-check</v-icon>
            Secure • Encrypted • PCI Compliant
          </p>
          <v-btn 
            color="primary" 
            size="x-large" 
            prepend-icon="mdi-bank"
            href="/connect-payment-method"
            class="add-payment-btn px-8"
            elevation="4"
          >
            Link Bank Account or Card
          </v-btn>
          <div class="accepted-cards mt-6">
            <p class="text-caption text-grey mb-2">We accept:</p>
            <div class="d-flex justify-center gap-2">
              <v-icon size="32" color="blue-darken-4">mdi-credit-card</v-icon>
              <v-icon size="32" color="red-darken-2">mdi-credit-card</v-icon>
              <v-icon size="32" color="blue">mdi-credit-card</v-icon>
              <v-icon size="32" color="orange-darken-2">mdi-credit-card</v-icon>
            </div>
          </div>
        </v-card-text>
      </v-card>

      <!-- Saved Methods List -->
      <div v-else class="methods-container">
        <v-card 
          v-for="pm in methods" 
          :key="pm.id" 
          class="method-card mb-4" 
          elevation="2"
          :class="{ 'default-card': pm.id === defaultPaymentMethod }"
        >
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between">
              <!-- Card Info -->
              <div class="d-flex align-center flex-grow-1">
                <!-- Card Icon -->
                <div class="card-icon-wrapper mr-4">
                  <v-icon size="40" :color="getCardColor(pm.card.brand)">
                    {{ getCardIcon(pm.card.brand) }}
                  </v-icon>
                </div>
                
                <!-- Card Details -->
                <div class="card-details">
                  <div class="d-flex align-center mb-1">
                    <h4 class="text-h6 mb-0 mr-2">{{ capitalize(pm.card.brand) }}</h4>
                    <v-chip 
                      v-if="pm.id === defaultPaymentMethod" 
                      color="success" 
                      size="small"
                      variant="flat"
                    >
                      <v-icon start size="14">mdi-star</v-icon>
                      Default
                    </v-chip>
                  </div>
                  <p class="text-body-2 text-grey mb-1">
                    <v-icon size="16" class="mr-1">mdi-numeric</v-icon>
                    •••• •••• •••• {{ pm.card.last4 }}
                  </p>
                  <p class="text-caption text-grey mb-0">
                    <v-icon size="14" class="mr-1">mdi-calendar</v-icon>
                    Expires {{ pm.card.exp_month }}/{{ pm.card.exp_year }}
                  </p>
                </div>
              </div>

              <!-- Actions -->
              <div class="card-actions">
                <v-btn
                  color="error"
                  variant="text"
                  size="small"
                  prepend-icon="mdi-delete"
                  @click="removeMethod(pm.id)"
                >
                  Remove
                </v-btn>
              </div>
            </div>
          </v-card-text>
        </v-card>

        <!-- Add Another Card Button -->
        <v-btn 
          color="primary" 
          variant="outlined" 
          block
          size="large"
          prepend-icon="mdi-plus"
          href="/connect-payment-method"
          class="add-another-btn"
        >
          Add Another Card
        </v-btn>
      </div>
    </div>

    <!-- Recurring Bookings Manager -->
    <div class="recurring-section mt-8">
      <recurring-bookings-manager />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import NotificationToast from './shared/NotificationToast.vue';
import AlertModal from './shared/AlertModal.vue';
import RecurringBookingsManager from './RecurringBookingsManager.vue';
import { useNotification } from '../composables/useNotification.js';

const { notification, success, error } = useNotification();

const methods = ref([]);
const loading = ref(false);
const defaultPaymentMethod = ref('');
const confirmDialog = ref(false);
const confirmData = ref({
  title: '',
  message: '',
  type: 'warning',
  confirmText: 'Confirm',
  callback: null
});

const loadMethods = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/client/payments/methods');
    methods.value = res.data.data || [];
    // If customer default exists, find it
    if (methods.value.length > 0) {
      defaultPaymentMethod.value = methods.value[0].id; // fallback
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const removeMethod = async (id) => {
  confirmData.value = {
    title: 'Remove Payment Method',
    message: 'Are you sure you want to remove this payment method? This action cannot be undone.',
    type: 'warning',
    confirmText: 'Remove',
    callback: async () => {
      try {
        await axios.post(`/api/client/payments/detach/${id}`);
        await loadMethods();
        success('Card removed successfully', 'Success');
      } catch (e) {
        console.error(e);
        error('Failed to remove card', 'Error');
      }
    }
  };
  confirmDialog.value = true;
};

const capitalize = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1).toLowerCase() : s;

const getCardIcon = (brand) => {
  const icons = {
    'visa': 'mdi-credit-card',
    'mastercard': 'mdi-credit-card',
    'amex': 'mdi-credit-card',
    'discover': 'mdi-credit-card',
    'diners': 'mdi-credit-card',
    'jcb': 'mdi-credit-card',
    'unionpay': 'mdi-credit-card'
  };
  return icons[brand?.toLowerCase()] || 'mdi-credit-card';
};

const getCardColor = (brand) => {
  const colors = {
    'visa': '#1A1F71',
    'mastercard': '#EB001B',
    'amex': '#006FCF',
    'discover': '#FF6000',
    'diners': '#0079BE',
    'jcb': '#0E4C96',
    'unionpay': '#E21836'
  };
  return colors[brand?.toLowerCase()] || 'primary';
};

onMounted(async () => {
  await loadMethods();
});
</script>

<style scoped>
.client-payment-methods {
  padding: 0;
  margin-bottom: 24px;
}

.payment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 2px solid #f0f0f0;
}

.header-left {
  display: flex;
  align-items: center;
}

.payment-title {
  font-size: 24px;
  font-weight: 600;
  margin: 0;
  color: #1a1a1a;
}

.payment-subtitle {
  font-size: 14px;
  color: #666;
  margin: 4px 0 0 0;
}

.loading-state {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.no-methods-card {
  border: 3px dashed #1976d2 !important;
  background: linear-gradient(135deg, #f5f9ff 0%, #e3f2fd 100%);
  transition: all 0.3s ease;
  border-radius: 16px !important;
}

.no-methods-card:hover {
  border-color: #1565c0 !important;
  background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(25, 118, 210, 0.2) !important;
}

.empty-state-icon {
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.pulse-icon {
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.05);
  }
}

.add-payment-btn {
  font-size: 16px !important;
  font-weight: 600 !important;
  text-transform: none !important;
  letter-spacing: 0.5px !important;
  border-radius: 12px !important;
  transition: all 0.3s ease !important;
}

.add-payment-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(25, 118, 210, 0.4) !important;
}

.accepted-cards {
  padding-top: 16px;
  border-top: 1px solid #e0e0e0;
}

.gap-2 {
  gap: 8px;
}

.methods-container {
  margin-top: 16px;
}

.method-card {
  transition: all 0.3s ease;
  border-left: 4px solid transparent;
}

.method-card:hover {
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}

.method-card.default-card {
  border-left-color: #4caf50;
  background: #f9fff9;
}

.card-icon-wrapper {
  background: #f5f5f5;
  border-radius: 12px;
  padding: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-details h4 {
  font-weight: 600;
  color: #1a1a1a;
}

.add-another-btn {
  margin-top: 16px;
  border: 2px dashed #1976d2 !important;
  transition: all 0.3s ease;
}

.add-another-btn:hover {
  background: #e3f2fd !important;
  border-style: solid !important;
}

.add-card-section {
  border: 2px solid #1976d2;
  border-radius: 16px !important;
  animation: slideDown 0.3s ease;
  overflow: hidden;
}

.bg-gradient {
  background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%) !important;
}

.save-card-btn {
  font-size: 16px !important;
  font-weight: 600 !important;
  text-transform: none !important;
  border-radius: 12px !important;
  transition: all 0.3s ease !important;
}

.save-card-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(25, 118, 210, 0.4) !important;
}

.security-badges {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  padding-top: 12px;
  border-top: 1px solid #e0e0e0;
}

.stripe-label {
  display: flex;
  align-items: center;
  font-size: 14px;
  font-weight: 600;
  color: #424242;
  margin-bottom: 8px;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.stripe-element-container {
  background: #ffffff;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
}

.stripe-element-container:focus-within {
  border-color: #1976d2;
  box-shadow: 0 0 0 4px rgba(25, 118, 210, 0.1);
  background: #fafbff;
}

.stripe-element {
  min-height: 44px;
}

/* Responsive */
@media (max-width: 600px) {
  .payment-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .method-card .d-flex {
    flex-direction: column;
    align-items: flex-start !important;
    gap: 12px;
  }
  
  .card-actions {
    width: 100%;
  }
  
  .card-actions button {
    width: 100%;
  }
}

.recurring-section {
  margin-top: 48px;
  padding-top: 32px;
  border-top: 2px solid #f0f0f0;
}
</style>
