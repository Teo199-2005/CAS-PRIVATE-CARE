<template>
  <v-container fluid class="admin-payouts-tab pa-0">
    <v-row>
      <v-col cols="12">
        <!-- Summary Cards -->
        <v-row class="mb-4">
          <v-col cols="12" sm="6" md="3">
            <v-card elevation="2" rounded="lg" class="pa-4">
              <div class="d-flex align-center">
                <v-avatar color="success" size="48" class="mr-3">
                  <v-icon color="white">mdi-cash-multiple</v-icon>
                </v-avatar>
                <div>
                  <p class="text-caption text-grey mb-0">Total Pending</p>
                  <p class="text-h5 font-weight-bold text-success mb-0">${{ formatMoney(pendingTotal) }}</p>
                </div>
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-card elevation="2" rounded="lg" class="pa-4">
              <div class="d-flex align-center">
                <v-avatar color="primary" size="48" class="mr-3">
                  <v-icon color="white">mdi-account-group</v-icon>
                </v-avatar>
                <div>
                  <p class="text-caption text-grey mb-0">Caregivers Due</p>
                  <p class="text-h5 font-weight-bold mb-0">{{ caregiversPending }}</p>
                </div>
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-card elevation="2" rounded="lg" class="pa-4">
              <div class="d-flex align-center">
                <v-avatar color="warning" size="48" class="mr-3">
                  <v-icon color="white">mdi-bullhorn</v-icon>
                </v-avatar>
                <div>
                  <p class="text-caption text-grey mb-0">Marketing Due</p>
                  <p class="text-h5 font-weight-bold text-warning mb-0">${{ formatMoney(marketingTotal) }}</p>
                </div>
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-card elevation="2" rounded="lg" class="pa-4">
              <div class="d-flex align-center">
                <v-avatar color="info" size="48" class="mr-3">
                  <v-icon color="white">mdi-school</v-icon>
                </v-avatar>
                <div>
                  <p class="text-caption text-grey mb-0">Training Due</p>
                  <p class="text-h5 font-weight-bold text-info mb-0">${{ formatMoney(trainingTotal) }}</p>
                </div>
              </div>
            </v-card>
          </v-col>
        </v-row>
        
        <!-- Payouts Table -->
        <v-card elevation="2" rounded="lg">
          <v-card-title class="d-flex align-center flex-wrap py-4 px-6">
            <v-icon color="primary" class="mr-3">mdi-bank-transfer</v-icon>
            <span class="text-h6 font-weight-bold">Pending Payouts</span>
            <v-spacer></v-spacer>
            
            <!-- Type Filter -->
            <v-select
              v-model="typeFilter"
              :items="typeOptions"
              label="Recipient Type"
              density="compact"
              variant="outlined"
              hide-details
              class="max-width-200 mr-4"
              clearable
              aria-label="Filter payouts by recipient type"
            ></v-select>
            
            <!-- Batch Actions -->
            <v-btn 
              color="primary" 
              variant="flat" 
              prepend-icon="mdi-cash-fast"
              :disabled="selectedPayouts.length === 0"
              :loading="processing"
              @click="processBatch"
              class="mr-2"
            >
              Process Selected ({{ selectedPayouts.length }})
            </v-btn>
            
            <v-btn 
              color="success" 
              variant="flat" 
              prepend-icon="mdi-cash-multiple"
              :loading="processingAll"
              @click="processAllPayouts"
            >
              Process All
            </v-btn>
          </v-card-title>
          
          <v-divider></v-divider>
          
          <v-card-text class="pa-0">
            <v-data-table
              v-model="selectedPayouts"
              :headers="payoutHeaders"
              :items="filteredPayouts"
              :loading="loading"
              :items-per-page="10"
              show-select
              class="elevation-0"
              hover
              item-value="id"
            >
              <!-- Recipient Column -->
              <template v-slot:item.recipient="{ item }">
                <div class="d-flex align-center py-2">
                  <v-avatar :color="getRecipientColor(item.type)" size="36" class="mr-2">
                    <v-icon size="18" color="white">{{ getRecipientIcon(item.type) }}</v-icon>
                  </v-avatar>
                  <div>
                    <div class="font-weight-medium">{{ item.recipient_name || 'N/A' }}</div>
                    <div class="text-caption text-grey">{{ item.recipient_email }}</div>
                  </div>
                </div>
              </template>
              
              <!-- Type Column -->
              <template v-slot:item.type="{ item }">
                <v-chip :color="getRecipientColor(item.type)" size="small" variant="flat">
                  <v-icon start size="14">{{ getRecipientIcon(item.type) }}</v-icon>
                  {{ formatType(item.type) }}
                </v-chip>
              </template>
              
              <!-- Amount Column -->
              <template v-slot:item.amount="{ item }">
                <span class="font-weight-bold text-success text-h6">${{ formatMoney(item.amount) }}</span>
              </template>
              
              <!-- Period Column -->
              <template v-slot:item.period="{ item }">
                <div>
                  <div class="font-weight-medium">{{ formatDate(item.period_start) }}</div>
                  <div class="text-caption text-grey">to {{ formatDate(item.period_end) }}</div>
                </div>
              </template>
              
              <!-- Stripe Status Column -->
              <template v-slot:item.stripe_status="{ item }">
                <v-chip 
                  :color="item.stripe_account_id ? 'success' : 'error'" 
                  size="small" 
                  variant="outlined"
                >
                  <v-icon start size="14">
                    {{ item.stripe_account_id ? 'mdi-check-circle' : 'mdi-alert-circle' }}
                  </v-icon>
                  {{ item.stripe_account_id ? 'Connected' : 'Not Connected' }}
                </v-chip>
              </template>
              
              <!-- Actions Column -->
              <template v-slot:item.actions="{ item }">
                <v-btn 
                  color="primary" 
                  size="small" 
                  variant="flat"
                  :disabled="!item.stripe_account_id"
                  :loading="item.processing"
                  @click="processPayout(item)"
                  :aria-label="`Process payout for ${item.recipient_name}`"
                >
                  <v-icon start size="18">mdi-cash-fast</v-icon>
                  Process
                </v-btn>
              </template>
              
              <!-- Loading State -->
              <template v-slot:loading>
                <v-skeleton-loader type="table-row@5"></v-skeleton-loader>
              </template>
              
              <!-- Empty State -->
              <template v-slot:no-data>
                <div class="text-center py-8">
                  <v-icon size="64" color="grey-lighten-1">mdi-cash-check</v-icon>
                  <p class="text-grey mt-4">No pending payouts</p>
                </div>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    
    <!-- Confirmation Dialog -->
    <v-dialog 
      v-model="confirmDialog" 
      max-width="400" 
      role="dialog" 
      aria-modal="true" 
      aria-labelledby="confirm-dialog-title"
    >
      <v-card>
        <v-card-title id="confirm-dialog-title">
          <v-icon class="mr-2" color="warning">mdi-alert</v-icon>
          Confirm Payout
        </v-card-title>
        <v-card-text>
          <p>Are you sure you want to process <strong>{{ payoutsToProcess.length }}</strong> payout(s) totaling <strong class="text-success">${{ formatMoney(batchTotal) }}</strong>?</p>
          <p class="text-caption text-grey mt-2">This action cannot be undone.</p>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="confirmDialog = false">Cancel</v-btn>
          <v-btn 
            color="success" 
            variant="flat" 
            @click="confirmProcess" 
            :loading="processing"
          >
            Confirm & Process
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
/**
 * AdminPayoutsTab Component
 * Manages commission payouts to caregivers, marketing agents, and training centers
 */

import { ref, computed } from 'vue';

// Props
const props = defineProps({
  payouts: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false }
});

// Emits
const emit = defineEmits(['process', 'process-batch', 'process-all']);

// State
const typeFilter = ref(null);
const selectedPayouts = ref([]);
const processing = ref(false);
const processingAll = ref(false);
const confirmDialog = ref(false);
const payoutsToProcess = ref([]);

const typeOptions = [
  { title: 'Caregiver', value: 'caregiver' },
  { title: 'Marketing', value: 'marketing' },
  { title: 'Training Center', value: 'training' }
];

const payoutHeaders = [
  { title: 'Recipient', key: 'recipient', sortable: true },
  { title: 'Type', key: 'type', sortable: true },
  { title: 'Amount', key: 'amount', sortable: true },
  { title: 'Period', key: 'period', sortable: true },
  { title: 'Stripe', key: 'stripe_status', sortable: false },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center', width: '120px' }
];

// Computed
const filteredPayouts = computed(() => {
  if (!typeFilter.value) return props.payouts;
  return props.payouts.filter(p => p.type === typeFilter.value);
});

const pendingTotal = computed(() => {
  return props.payouts.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
});

const caregiversPending = computed(() => {
  return props.payouts.filter(p => p.type === 'caregiver').length;
});

const marketingTotal = computed(() => {
  return props.payouts
    .filter(p => p.type === 'marketing')
    .reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
});

const trainingTotal = computed(() => {
  return props.payouts
    .filter(p => p.type === 'training')
    .reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
});

const batchTotal = computed(() => {
  return payoutsToProcess.value.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
});

// Methods
const formatMoney = (amount) => {
  return (parseFloat(amount) || 0).toFixed(2);
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric' 
  });
};

const formatType = (type) => {
  const types = {
    caregiver: 'Caregiver',
    marketing: 'Marketing',
    training: 'Training Center'
  };
  return types[type] || type;
};

const getRecipientColor = (type) => {
  const colors = { caregiver: 'green', marketing: 'warning', training: 'info' };
  return colors[type] || 'grey';
};

const getRecipientIcon = (type) => {
  const icons = {
    caregiver: 'mdi-account-heart',
    marketing: 'mdi-bullhorn',
    training: 'mdi-school'
  };
  return icons[type] || 'mdi-account';
};

const processPayout = (payout) => {
  payoutsToProcess.value = [payout];
  confirmDialog.value = true;
};

const processBatch = () => {
  const selected = props.payouts.filter(p => selectedPayouts.value.includes(p.id));
  payoutsToProcess.value = selected;
  confirmDialog.value = true;
};

const processAllPayouts = () => {
  payoutsToProcess.value = props.payouts.filter(p => p.stripe_account_id);
  confirmDialog.value = true;
};

const confirmProcess = () => {
  processing.value = true;
  
  if (payoutsToProcess.value.length === 1) {
    emit('process', payoutsToProcess.value[0]);
  } else if (payoutsToProcess.value.length === props.payouts.length) {
    emit('process-all', payoutsToProcess.value);
  } else {
    emit('process-batch', payoutsToProcess.value);
  }
  
  confirmDialog.value = false;
  processing.value = false;
  selectedPayouts.value = [];
};
</script>

<style scoped>
.max-width-200 {
  max-width: 200px;
}

/* Focus visible for accessibility */
.admin-payouts-tab :deep(.v-btn:focus-visible) {
  outline: 2px solid var(--v-theme-primary);
  outline-offset: 2px;
}
</style>
