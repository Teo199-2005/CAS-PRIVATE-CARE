<template>
  <div class="admin-payments-section">
    <!-- Tab Navigation -->
    <v-tabs v-model="activeTab" color="error" class="mb-6" show-arrows>
      <v-tab value="overview" aria-label="Payments Overview">
        <v-icon start>mdi-chart-box-outline</v-icon>
        Overview
      </v-tab>
      <v-tab value="company-account" aria-label="Company Account">
        <v-icon start>mdi-domain</v-icon>
        Company Account
      </v-tab>
      <v-tab value="client-payments" aria-label="Client Payments">
        <v-icon start>mdi-account-cash</v-icon>
        Client Payments
      </v-tab>
      <v-tab value="caregiver-payments" aria-label="Caregiver Payments">
        <v-icon start>mdi-hand-heart</v-icon>
        Caregiver Payments
      </v-tab>
      <v-tab value="housekeeper-payments" aria-label="Housekeeper Payments">
        <v-icon start>mdi-broom</v-icon>
        Housekeeper Payments
      </v-tab>
      <v-tab value="marketing-commissions" aria-label="Marketing Commissions">
        <v-icon start>mdi-bullhorn</v-icon>
        Marketing Commissions
      </v-tab>
      <v-tab value="training-commissions" aria-label="Training Commissions">
        <v-icon start>mdi-school</v-icon>
        Training Commissions
      </v-tab>
      <v-tab value="transactions" aria-label="All Transactions">
        <v-icon start>mdi-swap-horizontal</v-icon>
        All Transactions
      </v-tab>
    </v-tabs>

    <v-tabs-window v-model="activeTab">
      <!-- Overview Tab -->
      <v-tabs-window-item value="overview">
        <PaymentsOverviewTab
          :payment-stats="paymentStats"
          :money-flow="moneyFlow"
          :recent-transactions="recentTransactions"
          :payment-methods="paymentMethods"
          :loading="loading"
          @refresh-data="$emit('refresh-money-flow')"
          @export-pdf="$emit('export-financial-report')"
        />
      </v-tabs-window-item>

      <!-- Company Account Tab -->
      <v-tabs-window-item value="company-account">
        <CompanyAccountTab
          :company-account="companyAccount"
          :money-flow="moneyFlow"
          :recent-payouts="recentPlatformPayouts"
          :loading="loading"
        />
      </v-tabs-window-item>

      <!-- Client Payments Tab -->
      <v-tabs-window-item value="client-payments">
        <ClientPaymentsTab
          :payments="clientPayments"
          :is-mobile="isMobile"
          :loading="loading"
          @view-payment="viewPayment"
          @mark-paid="openMarkPaidDialog"
          @add-payment="$emit('add-payment')"
        />
      </v-tabs-window-item>

      <!-- Caregiver Payments Tab -->
      <v-tabs-window-item value="caregiver-payments">
        <CaregiverPaymentsTab
          :payments="caregiverPayments"
          :is-mobile="isMobile"
          :is-friday="isFriday"
          :loading="loading"
          @view-details="viewPaymentDetails"
          @pay="payCaregiver"
          @pay-all="$emit('process-salaries')"
          @export-pdf="$emit('export-caregiver-payments')"
        />
      </v-tabs-window-item>

      <!-- Housekeeper Payments Tab -->
      <v-tabs-window-item value="housekeeper-payments">
        <HousekeeperPaymentsTab
          :payments="housekeeperPayments"
          :is-mobile="isMobile"
          :is-friday="isFriday"
          :loading="loading"
          @view-details="viewHousekeeperPaymentDetails"
          @pay="payHousekeeper"
          @export-pdf="$emit('export-housekeeper-payments')"
        />
      </v-tabs-window-item>

      <!-- Marketing Commissions Tab -->
      <v-tabs-window-item value="marketing-commissions">
        <MarketingCommissionsTab
          :commissions="marketingCommissions"
          :is-mobile="isMobile"
          :is-friday="isFriday"
          :loading="loadingMarketingCommissions"
          @view-details="viewMarketingCommissionDetails"
          @pay="payMarketingCommission"
          @pay-all="$emit('pay-all-marketing-commissions')"
          @export-pdf="$emit('export-marketing-commissions')"
        />
      </v-tabs-window-item>

      <!-- Training Commissions Tab -->
      <v-tabs-window-item value="training-commissions">
        <TrainingCommissionsTab
          :commissions="trainingCommissions"
          :is-mobile="isMobile"
          :is-friday="isFriday"
          :loading="loadingTrainingCommissions"
          @view-details="viewTrainingCommissionDetails"
          @pay="payTrainingCommission"
          @pay-all="$emit('pay-all-training-commissions')"
          @export-pdf="$emit('export-training-commissions')"
        />
      </v-tabs-window-item>

      <!-- All Transactions Tab -->
      <v-tabs-window-item value="transactions">
        <AllTransactionsTab
          :transactions="allTransactions"
          :is-mobile="isMobile"
          :loading="loading"
          @export="$emit('export-transactions')"
        />
      </v-tabs-window-item>
    </v-tabs-window>

    <!-- Payment Confirmation Dialog -->
    <v-dialog 
      v-model="paymentConfirmDialog" 
      :max-width="isMobile ? undefined : 550" 
      :fullscreen="isMobile" 
      persistent 
      scrollable
    >
      <v-card elevation="8" class="rounded-lg">
        <v-card-title class="pa-4" style="background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);">
          <div class="d-flex align-center">
            <v-icon size="28" color="white" class="mr-2">mdi-cash-check</v-icon>
            <span class="text-h6 font-weight-bold text-white">Confirm Payment</span>
          </div>
        </v-card-title>

        <v-card-text class="pa-4" v-if="selectedPayment">
          <!-- Payment Amount Highlight -->
          <div class="text-center mb-3 pa-4 rounded-lg" style="background: linear-gradient(135deg, #c62828 0%, #b71c1c 100%); box-shadow: 0 3px 15px rgba(198, 40, 40, 0.3);">
            <div class="text-caption text-white mb-1" style="letter-spacing: 1px; opacity: 0.9;">PAYMENT AMOUNT</div>
            <div class="text-h4 font-weight-bold text-white">{{ selectedPayment.unpaid_display || formatCurrency(selectedPayment.amount) }}</div>
          </div>

          <!-- Recipient Info -->
          <v-card variant="tonal" color="blue-darken-3" class="mb-3">
            <v-card-text class="pa-3">
              <div class="d-flex align-center mb-2">
                <v-avatar color="blue-darken-1" size="32" class="mr-2">
                  <v-icon size="18" color="white">mdi-account</v-icon>
                </v-avatar>
                <div class="flex-grow-1">
                  <div class="text-caption text-grey-darken-1">Recipient</div>
                  <div class="font-weight-bold">{{ selectedPayment.name || selectedPayment.caregiver }}</div>
                </div>
              </div>
              <div class="d-flex align-center">
                <v-avatar color="blue-darken-1" size="32" class="mr-2">
                  <v-icon size="18" color="white">mdi-email</v-icon>
                </v-avatar>
                <div class="flex-grow-1">
                  <div class="text-caption text-grey-darken-1">Email</div>
                  <div class="text-body-2">{{ selectedPayment.email || selectedPayment.caregiver_email }}</div>
                </div>
              </div>
            </v-card-text>
          </v-card>

          <!-- Payment Details -->
          <v-card variant="outlined" class="mb-3" style="border: 1px solid #e0e0e0;">
            <v-card-text class="pa-3">
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="16" class="mr-1">mdi-clock-outline</v-icon>
                  Hours:
                </span>
                <span class="font-weight-bold">{{ selectedPayment.hours_display || selectedPayment.hours_worked + ' hrs' }}</span>
              </div>
              <v-divider class="my-1"></v-divider>
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="16" class="mr-1">mdi-currency-usd</v-icon>
                  Rate:
                </span>
                <span class="font-weight-bold">{{ selectedPayment.rate || '$' + selectedPayment.hourly_rate + '/hr' }}</span>
              </div>
              <v-divider class="my-1"></v-divider>
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="16" class="mr-1">mdi-calendar</v-icon>
                  Period:
                </span>
                <span class="font-weight-bold">{{ selectedPayment.period || 'Current Period' }}</span>
              </div>
              <v-divider class="my-2" style="border-color: #c62828;"></v-divider>
              <div class="d-flex justify-space-between align-center pa-2 rounded" style="background: #ffebee;">
                <span class="font-weight-bold">Total:</span>
                <span class="text-h6 font-weight-bold" style="color: #c62828;">{{ selectedPayment.unpaid_display || formatCurrency(selectedPayment.amount) }}</span>
              </div>
            </v-card-text>
          </v-card>

          <!-- Bank Transfer Info -->
          <v-alert variant="tonal" class="mb-2 py-2" color="blue-darken-3" density="compact">
            <div class="d-flex align-center">
              <v-icon size="20" class="mr-2">mdi-bank-transfer</v-icon>
              <span class="text-caption">Bank transfer via Stripe to connected account</span>
            </div>
          </v-alert>

          <!-- Security Notice -->
          <v-alert variant="tonal" density="compact" color="green-darken-1" class="py-1">
            <div class="d-flex align-center">
              <v-icon size="16" class="mr-1">mdi-shield-check</v-icon>
              <span class="text-caption">Secure payment • Bank-level encryption</span>
            </div>
          </v-alert>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn 
            variant="outlined" 
            size="default"
            @click="cancelPayment" 
            class="px-4"
            color="grey-darken-1"
            :disabled="processingPayment"
          >
            <v-icon start size="20">mdi-close</v-icon>
            Cancel
          </v-btn>
          <v-btn 
            size="default"
            variant="elevated" 
            @click="confirmPayment" 
            class="px-6"
            style="background: linear-gradient(135deg, #c62828 0%, #b71c1c 100%); color: white;"
            :loading="processingPayment"
          >
            <v-icon start size="20">mdi-check-circle</v-icon>
            Confirm Payment
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Payment Details Dialog -->
    <v-dialog 
      v-model="paymentDetailsDialog" 
      :max-width="isMobile ? undefined : 550" 
      :fullscreen="isMobile" 
      scrollable
    >
      <v-card elevation="8" class="rounded-lg">
        <v-card-title class="pa-4" style="background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);">
          <div class="d-flex align-center">
            <v-icon size="28" color="white" class="mr-2">mdi-information</v-icon>
            <span class="text-h6 font-weight-bold text-white">Payment Details</span>
          </div>
        </v-card-title>

        <v-card-text class="pa-4" v-if="selectedPaymentDetails">
          <!-- Worker Info -->
          <v-card variant="tonal" color="blue-darken-3" class="mb-3">
            <v-card-text class="pa-3">
              <div class="d-flex align-center mb-2">
                <v-avatar color="blue-darken-1" size="40" class="mr-3">
                  <v-icon size="20" color="white">mdi-account</v-icon>
                </v-avatar>
                <div class="flex-grow-1">
                  <div class="text-caption text-grey-darken-1">{{ selectedPaymentDetails.type || 'Worker' }}</div>
                  <div class="font-weight-bold text-h6">{{ selectedPaymentDetails.name || selectedPaymentDetails.caregiver }}</div>
                </div>
              </div>
              <div class="d-flex align-center">
                <v-avatar color="blue-darken-1" size="40" class="mr-3">
                  <v-icon size="20" color="white">mdi-email</v-icon>
                </v-avatar>
                <div class="flex-grow-1">
                  <div class="text-caption text-grey-darken-1">Email</div>
                  <div class="text-body-2">{{ selectedPaymentDetails.email || selectedPaymentDetails.caregiver_email }}</div>
                </div>
              </div>
            </v-card-text>
          </v-card>

          <!-- Work Details -->
          <v-card variant="outlined" class="mb-3" style="border: 2px solid #1565c0;">
            <v-card-text class="pa-3">
              <div class="text-caption text-grey-darken-2 mb-2 font-weight-bold" style="letter-spacing: 1px;">WORK DETAILS</div>
              
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="18" class="mr-1" color="blue-darken-1">mdi-clock-outline</v-icon>
                  Total Hours:
                </span>
                <span class="font-weight-bold">{{ selectedPaymentDetails.hours_display || (selectedPaymentDetails.hours_worked + ' hrs') }}</span>
              </div>
              
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="18" class="mr-1" color="blue-darken-1">mdi-currency-usd</v-icon>
                  Hourly Rate:
                </span>
                <span class="font-weight-bold">{{ selectedPaymentDetails.rate || ('$' + selectedPaymentDetails.hourly_rate) }}</span>
              </div>
              
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="18" class="mr-1" color="blue-darken-1">mdi-calendar-multiple</v-icon>
                  Days Worked:
                </span>
                <span class="font-weight-bold">{{ selectedPaymentDetails.days_worked || 'N/A' }}</span>
              </div>
              
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="18" class="mr-1" color="blue-darken-1">mdi-calendar-range</v-icon>
                  Period:
                </span>
                <span class="font-weight-bold">{{ selectedPaymentDetails.period || 'Current Period' }}</span>
              </div>
            </v-card-text>
          </v-card>

          <!-- Payment Status -->
          <v-card variant="outlined" class="mb-3" style="border: 2px solid #2e7d32;">
            <v-card-text class="pa-3">
              <div class="text-caption text-grey-darken-2 mb-2 font-weight-bold" style="letter-spacing: 1px;">PAYMENT SUMMARY</div>
              
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="18" class="mr-1" color="green-darken-2">mdi-cash-multiple</v-icon>
                  Total Earned:
                </span>
                <span class="font-weight-bold text-success">{{ selectedPaymentDetails.amount_display || formatCurrency(selectedPaymentDetails.total_earned || selectedPaymentDetails.amount) }}</span>
              </div>
              
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="18" class="mr-1" color="orange-darken-2">mdi-clock-alert</v-icon>
                  Unpaid:
                </span>
                <span class="font-weight-bold text-warning">{{ selectedPaymentDetails.unpaid_display || formatCurrency(selectedPaymentDetails.pending_commission || selectedPaymentDetails.unpaid || 0) }}</span>
              </div>
              
              <v-divider class="my-2"></v-divider>
              
              <div class="d-flex justify-space-between align-center pa-2 rounded" style="background: #e8f5e9;">
                <span class="text-body-2 text-grey-darken-2">
                  <v-icon size="18" class="mr-1" color="green-darken-3">mdi-bank</v-icon>
                  Bank Status:
                </span>
                <v-chip 
                  :color="selectedPaymentDetails.bank_connected ? 'success' : 'error'" 
                  size="small" 
                  variant="flat"
                >
                  <v-icon start size="16">{{ selectedPaymentDetails.bank_connected ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
                  {{ selectedPaymentDetails.bank_status }}
                </v-chip>
              </div>
            </v-card-text>
          </v-card>

          <!-- Stripe Info (if connected) -->
          <v-alert 
            v-if="selectedPaymentDetails.bank_connected && selectedPaymentDetails.stripe_connect_id" 
            type="info" 
            variant="tonal" 
            density="compact"
            class="mb-0"
          >
            <div class="text-body-2">
              <v-icon size="16" class="mr-1">mdi-information</v-icon>
              Stripe Connect ID: <code class="text-caption">{{ selectedPaymentDetails.stripe_connect_id }}</code>
            </div>
          </v-alert>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn 
            variant="elevated" 
            color="primary"
            @click="closeDetailsDialog" 
            class="px-6"
          >
            <v-icon start size="20">mdi-check</v-icon>
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, defineAsyncComponent } from 'vue';

// Lazy load sub-components
const PaymentsOverviewTab = defineAsyncComponent(() => 
  import('./payments/PaymentsOverviewTab.vue')
);
const CompanyAccountTab = defineAsyncComponent(() => 
  import('./payments/CompanyAccountTab.vue')
);
const ClientPaymentsTab = defineAsyncComponent(() => 
  import('./payments/ClientPaymentsTab.vue')
);
const CaregiverPaymentsTab = defineAsyncComponent(() => 
  import('./payments/CaregiverPaymentsTab.vue')
);
const HousekeeperPaymentsTab = defineAsyncComponent(() => 
  import('./payments/HousekeeperPaymentsTab.vue')
);
const MarketingCommissionsTab = defineAsyncComponent(() => 
  import('./payments/MarketingCommissionsTab.vue')
);
const TrainingCommissionsTab = defineAsyncComponent(() => 
  import('./payments/TrainingCommissionsTab.vue')
);
const AllTransactionsTab = defineAsyncComponent(() => 
  import('./payments/AllTransactionsTab.vue')
);

/**
 * Props from parent component
 */
const props = defineProps({
  /** Payment statistics for overview */
  paymentStats: {
    type: Array,
    default: () => []
  },
  /** Money flow data for the overview */
  moneyFlow: {
    type: Object,
    default: () => ({
      today: { payments_in: 0, payouts_out: 0, net_change: 0 },
      totals: { total_payments_in: 0, total_payouts_out: 0, pending_payouts: 0, expected_balance: 0, stripe_balance: null, balance_difference: 0 },
      failed_payouts: []
    })
  },
  /** Recent transactions list */
  recentTransactions: {
    type: Array,
    default: () => []
  },
  /** Payment methods list */
  paymentMethods: {
    type: Array,
    default: () => []
  },
  /** Company Stripe account info */
  companyAccount: {
    type: Object,
    default: () => ({})
  },
  /** Recent platform payouts */
  recentPlatformPayouts: {
    type: Array,
    default: () => []
  },
  /** Client payments list */
  clientPayments: {
    type: Array,
    default: () => []
  },
  /** Caregiver payments list */
  caregiverPayments: {
    type: Array,
    default: () => []
  },
  /** Housekeeper payments list */
  housekeeperPayments: {
    type: Array,
    default: () => []
  },
  /** Marketing commissions list */
  marketingCommissions: {
    type: Array,
    default: () => []
  },
  /** Training commissions list */
  trainingCommissions: {
    type: Array,
    default: () => []
  },
  /** All transactions list */
  allTransactions: {
    type: Array,
    default: () => []
  },
  /** Whether currently on mobile */
  isMobile: {
    type: Boolean,
    default: false
  },
  /** Whether today is Friday (payout day) */
  isFriday: {
    type: Boolean,
    default: false
  },
  /** Loading state */
  loading: {
    type: Boolean,
    default: false
  },
  /** Loading state for marketing commissions */
  loadingMarketingCommissions: {
    type: Boolean,
    default: false
  },
  /** Loading state for training commissions */
  loadingTrainingCommissions: {
    type: Boolean,
    default: false
  }
});

/**
 * Emitted events
 */
const emit = defineEmits([
  'refresh-money-flow',
  'export-financial-report',
  'add-payment',
  'view-payment',
  'mark-paid',
  'pay-caregiver',
  'pay-housekeeper',
  'pay-marketing-commission',
  'pay-training-commission',
  'process-salaries',
  'pay-all-marketing-commissions',
  'pay-all-training-commissions',
  'export-caregiver-payments',
  'export-housekeeper-payments',
  'export-marketing-commissions',
  'export-training-commissions',
  'export-transactions',
  'confirm-payment',
  'tab-change'
]);

// Active tab state
const activeTab = ref('overview');

// Dialog states
const paymentConfirmDialog = ref(false);
const paymentDetailsDialog = ref(false);
const selectedPayment = ref(null);
const selectedPaymentDetails = ref(null);
const processingPayment = ref(false);

// Watch tab changes
const onTabChange = (tab) => {
  emit('tab-change', tab);
};

/**
 * Format currency value
 */
const formatCurrency = (value) => {
  if (value === null || value === undefined) return '$0.00';
  return '$' + Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

/**
 * View client payment details
 */
const viewPayment = (payment) => {
  emit('view-payment', payment);
};

/**
 * Open mark as paid dialog
 */
const openMarkPaidDialog = (payment) => {
  emit('mark-paid', payment);
};

/**
 * View caregiver payment details
 */
const viewPaymentDetails = (payment) => {
  selectedPaymentDetails.value = { ...payment, type: 'Caregiver' };
  paymentDetailsDialog.value = true;
};

/**
 * View housekeeper payment details
 */
const viewHousekeeperPaymentDetails = (payment) => {
  selectedPaymentDetails.value = { ...payment, type: 'Housekeeper' };
  paymentDetailsDialog.value = true;
};

/**
 * View marketing commission details
 */
const viewMarketingCommissionDetails = (commission) => {
  selectedPaymentDetails.value = { ...commission, type: 'Marketing Staff' };
  paymentDetailsDialog.value = true;
};

/**
 * View training commission details
 */
const viewTrainingCommissionDetails = (commission) => {
  selectedPaymentDetails.value = { ...commission, type: 'Training Center' };
  paymentDetailsDialog.value = true;
};

/**
 * Initiate caregiver payment
 */
const payCaregiver = (caregiver) => {
  selectedPayment.value = { ...caregiver, paymentType: 'caregiver' };
  paymentConfirmDialog.value = true;
};

/**
 * Initiate housekeeper payment
 */
const payHousekeeper = (housekeeper) => {
  selectedPayment.value = { ...housekeeper, paymentType: 'housekeeper' };
  paymentConfirmDialog.value = true;
};

/**
 * Initiate marketing commission payment
 */
const payMarketingCommission = (staff) => {
  selectedPayment.value = { ...staff, paymentType: 'marketing' };
  paymentConfirmDialog.value = true;
};

/**
 * Initiate training commission payment
 */
const payTrainingCommission = (center) => {
  selectedPayment.value = { ...center, paymentType: 'training' };
  paymentConfirmDialog.value = true;
};

/**
 * Cancel payment dialog
 */
const cancelPayment = () => {
  if (!processingPayment.value) {
    paymentConfirmDialog.value = false;
    selectedPayment.value = null;
  }
};

/**
 * Confirm and process payment
 */
const confirmPayment = async () => {
  if (!selectedPayment.value || processingPayment.value) return;
  
  processingPayment.value = true;
  
  try {
    const paymentType = selectedPayment.value.paymentType;
    
    switch (paymentType) {
      case 'caregiver':
        emit('pay-caregiver', selectedPayment.value);
        break;
      case 'housekeeper':
        emit('pay-housekeeper', selectedPayment.value);
        break;
      case 'marketing':
        emit('pay-marketing-commission', selectedPayment.value);
        break;
      case 'training':
        emit('pay-training-commission', selectedPayment.value);
        break;
    }
    
    paymentConfirmDialog.value = false;
    selectedPayment.value = null;
  } finally {
    processingPayment.value = false;
  }
};

/**
 * Close details dialog
 */
const closeDetailsDialog = () => {
  paymentDetailsDialog.value = false;
  selectedPaymentDetails.value = null;
};

// Expose methods for parent access
defineExpose({
  openPaymentConfirmDialog: (payment) => {
    selectedPayment.value = payment;
    paymentConfirmDialog.value = true;
  },
  closeDialogs: () => {
    paymentConfirmDialog.value = false;
    paymentDetailsDialog.value = false;
    selectedPayment.value = null;
    selectedPaymentDetails.value = null;
  }
});
</script>

<style scoped>
.admin-payments-section {
  width: 100%;
}

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
  .v-tabs-window-item,
  .v-dialog {
    transition: none !important;
  }
}
</style>
