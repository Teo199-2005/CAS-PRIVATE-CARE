<template>
  <div class="payment-section">
    <v-row>
      <!-- Main Payment Content -->
      <v-col cols="12" :md="showSidebar ? 8 : 12">
        <!-- Payout Methods Card -->
        <v-card elevation="0" class="mb-4">
          <v-card-title class="payment-card-header pa-4 d-flex justify-space-between align-center">
            <span class="payment-section-title">Payout Methods</span>
            <v-btn 
              :color="themeColor" 
              size="small"
              variant="flat"
              prepend-icon="mdi-plus" 
              @click="addPayoutMethod"
            >
              Add Method
            </v-btn>
          </v-card-title>
          <v-card-text class="pa-4">
            <!-- No Payout Methods -->
            <div v-if="payoutMethods.length === 0" class="text-center py-6">
              <v-icon size="48" color="grey-lighten-1" class="mb-3">mdi-wallet-outline</v-icon>
              <h3 class="text-subtitle-1 font-weight-bold mb-1">No Payout Method Connected</h3>
              <p class="text-body-2 text-grey mb-4">
                Add a payout method to receive weekly payments.
              </p>
              <v-btn :color="themeColor" variant="flat" prepend-icon="mdi-plus" @click="addPayoutMethod">
                Add Payout Method
              </v-btn>
            </div>

            <!-- Payout Methods List -->
            <div v-else class="payout-methods-list">
              <div 
                v-for="method in payoutMethods" 
                :key="method.id"
                class="payout-method-item"
                :class="{ 'is-active': method.is_active }"
              >
                <div class="d-flex align-center">
                  <v-avatar :color="method.is_active ? themeColor : 'grey-lighten-2'" size="40" class="mr-3">
                    <v-icon :color="method.is_active ? 'white' : 'grey'" size="20">
                      {{ getMethodIcon(method.type) }}
                    </v-icon>
                  </v-avatar>
                  <div class="flex-grow-1">
                    <div class="d-flex align-center">
                      <span class="font-weight-bold">{{ method.name }}</span>
                      <v-chip 
                        v-if="method.is_active" 
                        :color="themeColor" 
                        size="x-small" 
                        class="ml-2"
                      >
                        Active
                      </v-chip>
                    </div>
                    <div class="text-caption text-grey">{{ method.details }}</div>
                  </div>
                  <div class="method-actions">
                    <v-btn 
                      v-if="!method.is_active"
                      size="x-small" 
                      variant="tonal"
                      :color="themeColor"
                      @click="setActiveMethod(method)"
                    >
                      Set Active
                    </v-btn>
                    <v-btn 
                      icon 
                      size="x-small" 
                      variant="text"
                      color="error"
                      @click="removeMethod(method)"
                    >
                      <v-icon size="16">mdi-delete</v-icon>
                    </v-btn>
                  </div>
                </div>
              </div>
            </div>
          </v-card-text>
        </v-card>

        <!-- Account Balance Card -->
        <v-card elevation="0" class="mb-4">
          <v-card-title class="payment-card-header pa-4">
            <span class="payment-section-title">Account Balance</span>
          </v-card-title>
          <v-card-text class="pa-4">
            <v-row class="balance-stats">
              <v-col cols="6" sm="3">
                <div class="balance-stat">
                  <div class="balance-label">Current Balance</div>
                  <div class="balance-value" :style="{ color: themeColorHex }">
                    ${{ formatMoney(balance.current) }}
                  </div>
                </div>
              </v-col>
              <v-col cols="6" sm="3">
                <div class="balance-stat">
                  <div class="balance-label">Total Paid Out</div>
                  <div class="balance-value">${{ formatMoney(balance.totalPaid) }}</div>
                </div>
              </v-col>
              <v-col cols="6" sm="3">
                <div class="balance-stat">
                  <div class="balance-label">Last Payment</div>
                  <div class="balance-value">${{ formatMoney(balance.lastPayment) }}</div>
                </div>
              </v-col>
              <v-col cols="6" sm="3">
                <div class="balance-stat">
                  <div class="balance-label">Next Payout</div>
                  <div class="balance-value" :style="{ color: themeColorHex }">{{ nextPayoutDate }}</div>
                </div>
              </v-col>
            </v-row>
            
            <v-alert v-if="balance.current === 0" type="info" variant="tonal" density="compact" class="mt-4">
              <span class="text-body-2">All Caught Up! You have no pending payments at this time.</span>
            </v-alert>
          </v-card-text>
        </v-card>

        <!-- Payment Settings Card -->
        <v-card elevation="0" class="mb-4">
          <v-card-title class="payment-card-header pa-4">
            <span class="payment-section-title">Payment Settings</span>
          </v-card-title>
          <v-card-text class="pa-4">
            <v-row>
              <v-col cols="12" sm="6">
                <v-select
                  v-model="settings.payoutFrequency"
                  :items="['Weekly', 'Bi-Weekly', 'Monthly']"
                  label="Payout Frequency"
                  variant="outlined"
                  density="compact"
                  hide-details
                  disabled
                />
              </v-col>
              <v-col cols="12" sm="6">
                <v-select
                  v-model="settings.payoutMethod"
                  :items="['Bank Transfer', 'Instant Transfer']"
                  label="Payout Method"
                  variant="outlined"
                  density="compact"
                  hide-details
                  disabled
                />
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <!-- Past Bookings Card -->
        <v-card elevation="0">
          <v-card-title class="payment-card-header pa-4 d-flex justify-space-between align-center">
            <div>
              <span class="payment-section-title">Past Bookings</span>
              <div class="text-caption text-grey mt-1">View your completed contract details and time tracking history</div>
            </div>
            <v-chip size="small" variant="tonal">{{ pastBookings.length }} Total</v-chip>
          </v-card-title>
          <v-card-text class="pa-0">
            <v-data-table
              v-if="pastBookings.length > 0"
              :headers="bookingHeaders"
              :items="pastBookings"
              :items-per-page="10"
              class="elevation-0"
            >
              <template v-slot:item.status="{ item }">
                <v-chip :color="getStatusColor(item.status)" size="x-small">
                  {{ item.status }}
                </v-chip>
              </template>
              <template v-slot:item.amount="{ item }">
                <span class="font-weight-bold">${{ formatMoney(item.amount) }}</span>
              </template>
            </v-data-table>
            <div v-else class="text-center py-8">
              <v-icon size="48" color="grey-lighten-1" class="mb-3">mdi-calendar-blank</v-icon>
              <p class="text-body-2 text-grey">No Past Bookings</p>
              <p class="text-caption text-grey">Your completed bookings will appear here with time tracking details.</p>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Sidebar (optional) -->
      <v-col v-if="showSidebar" cols="12" md="4">
        <slot name="sidebar">
          <!-- Default sidebar content -->
          <v-card elevation="0" class="mb-4">
            <v-card-text class="pa-4 text-center">
              <v-icon size="48" :color="themeColor" class="mb-3">mdi-shield-check</v-icon>
              <h4 class="text-subtitle-2 font-weight-bold mb-2">Secure Payments</h4>
              <p class="text-caption text-grey">
                Your payment information is encrypted and securely processed through Stripe.
              </p>
            </v-card-text>
          </v-card>
        </slot>
      </v-col>
    </v-row>

    <!-- Add Payout Method Dialog -->
    <v-dialog v-model="showAddDialog" max-width="500">
      <v-card>
        <v-card-title class="pa-4" :style="{ background: themeColorHex, color: 'white' }">
          <span class="text-h6">Add Payout Method</span>
        </v-card-title>
        <v-card-text class="pa-4">
          <v-list>
            <v-list-item 
              v-for="option in payoutOptions" 
              :key="option.type"
              @click="selectPayoutOption(option)"
              class="payout-option-item mb-2"
            >
              <template v-slot:prepend>
                <v-avatar :color="themeColor" size="40">
                  <v-icon color="white">{{ option.icon }}</v-icon>
                </v-avatar>
              </template>
              <v-list-item-title class="font-weight-bold">{{ option.name }}</v-list-item-title>
              <v-list-item-subtitle>{{ option.description }}</v-list-item-subtitle>
              <template v-slot:append>
                <v-icon>mdi-chevron-right</v-icon>
              </template>
            </v-list-item>
          </v-list>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Confirm Remove Dialog -->
    <v-dialog v-model="showRemoveDialog" max-width="400">
      <v-card>
        <v-card-title class="pa-4 bg-error text-white">
          <span class="text-h6">Remove Payout Method</span>
        </v-card-title>
        <v-card-text class="pa-4">
          <p>Are you sure you want to remove this payout method?</p>
          <p v-if="methodToRemove?.is_active" class="text-error font-weight-bold mt-2">
            Warning: This is your active payout method. You'll need to set another method as active.
          </p>
        </v-card-text>
        <v-card-actions class="pa-4 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="showRemoveDialog = false">Cancel</v-btn>
          <v-btn color="error" variant="flat" @click="confirmRemove">Remove</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  themeColor: { type: String, default: 'success' },
  role: { type: String, default: 'caregiver' },
  showSidebar: { type: Boolean, default: true },
  apiEndpoint: { type: String, default: '/api/payout-methods' }
});

const emit = defineEmits(['add-method', 'remove-method', 'set-active']);

// Theme color hex values
const themeColors = {
  success: '#10b981',
  'deep-purple': '#673ab7',
  primary: '#3b82f6',
  warning: '#f59e0b'
};
const themeColorHex = computed(() => themeColors[props.themeColor] || themeColors.success);

// State
const payoutMethods = ref([]);
const balance = ref({ current: 0, totalPaid: 0, lastPayment: 0 });
const settings = ref({ payoutFrequency: 'Weekly', payoutMethod: 'Bank Transfer' });
const pastBookings = ref([]);
const showAddDialog = ref(false);
const showRemoveDialog = ref(false);
const methodToRemove = ref(null);

// Computed
const nextPayoutDate = computed(() => {
  const today = new Date();
  const daysUntilFriday = (5 - today.getDay() + 7) % 7 || 7;
  const nextFriday = new Date(today);
  nextFriday.setDate(today.getDate() + daysUntilFriday);
  return nextFriday.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
});

// Payout options
const payoutOptions = [
  { type: 'bank', name: 'Bank Account', description: 'Direct deposit (ACH)', icon: 'mdi-bank' },
  { type: 'card', name: 'Debit Card', description: 'Instant transfer to card', icon: 'mdi-credit-card' },
  { type: 'cashapp', name: 'Cash App', description: 'Transfer to Cash App', icon: 'mdi-cash' },
  { type: 'venmo', name: 'Venmo', description: 'Transfer to Venmo', icon: 'mdi-alpha-v-circle' }
];

// Table headers
const bookingHeaders = [
  { title: 'Date', key: 'date' },
  { title: 'Client', key: 'client' },
  { title: 'Hours', key: 'hours' },
  { title: 'Amount', key: 'amount' },
  { title: 'Status', key: 'status' }
];

// Methods
const formatMoney = (amount) => {
  return (parseFloat(amount) || 0).toFixed(2);
};

const getMethodIcon = (type) => {
  const icons = {
    bank: 'mdi-bank',
    card: 'mdi-credit-card',
    cashapp: 'mdi-cash',
    venmo: 'mdi-alpha-v-circle',
    alipay: 'mdi-wallet'
  };
  return icons[type] || 'mdi-wallet';
};

const getStatusColor = (status) => {
  const colors = { Paid: 'success', Pending: 'warning', Cancelled: 'error' };
  return colors[status] || 'grey';
};

const addPayoutMethod = () => {
  showAddDialog.value = true;
};

const selectPayoutOption = (option) => {
  showAddDialog.value = false;
  emit('add-method', option.type);
};

const setActiveMethod = async (method) => {
  emit('set-active', method);
};

const removeMethod = (method) => {
  methodToRemove.value = method;
  showRemoveDialog.value = true;
};

const confirmRemove = () => {
  emit('remove-method', methodToRemove.value);
  showRemoveDialog.value = false;
  methodToRemove.value = null;
};

// Load data
const loadPayoutMethods = async () => {
  try {
    const response = await fetch(`${props.apiEndpoint}?role=${props.role}`);
    if (response.ok) {
      const data = await response.json();
      payoutMethods.value = data.methods || [];
      balance.value = data.balance || balance.value;
    }
  } catch (error) {
    console.error('Failed to load payout methods:', error);
  }
};

// Expose methods for parent component
defineExpose({
  loadPayoutMethods,
  payoutMethods,
  balance
});

onMounted(() => {
  loadPayoutMethods();
});
</script>

<style scoped>
.payment-section {
  width: 100%;
}

.payment-card-header {
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.payment-section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
}

.payout-methods-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.payout-method-item {
  padding: 12px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: white;
  transition: all 0.2s ease;
}

.payout-method-item:hover {
  border-color: #d1d5db;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.payout-method-item.is-active {
  border-color: v-bind(themeColorHex);
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, transparent 100%);
}

.method-actions {
  display: flex;
  gap: 8px;
  align-items: center;
}

.balance-stats {
  margin: 0 -8px;
}

.balance-stat {
  text-align: center;
  padding: 12px;
}

.balance-label {
  font-size: 12px;
  color: #6b7280;
  margin-bottom: 4px;
}

.balance-value {
  font-size: 18px;
  font-weight: 700;
  color: #374151;
}

.payout-option-item {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.payout-option-item:hover {
  border-color: v-bind(themeColorHex);
  background: rgba(16, 185, 129, 0.05);
}

/* Mobile responsive */
@media (max-width: 600px) {
  .payment-card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .balance-value {
    font-size: 16px;
  }
  
  .method-actions {
    flex-direction: column;
    gap: 4px;
  }
}
</style>
