<template>
  <div class="payments-overview-tab">
    <!-- Stats Cards -->
    <v-row class="mb-4">
      <v-col v-for="stat in paymentStats" :key="stat.title" cols="12" sm="6" md="3">
        <v-card elevation="0" class="compact-stat-card h-100">
          <v-card-text class="pa-4">
            <div class="d-flex align-center">
              <v-icon :color="stat.color" size="24" class="mr-3">{{ stat.icon }}</v-icon>
              <div>
                <div class="stat-value text-h5 font-weight-bold" :class="stat.color + '--text'">{{ stat.value }}</div>
                <div class="stat-label text-caption text-grey-darken-1">{{ stat.title }}</div>
                <div v-if="stat.change" class="stat-change text-caption" :class="stat.changeColor">{{ stat.change }}</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Money Flow Monitoring Card -->
    <v-row class="mb-4">
      <v-col cols="12">
        <v-card elevation="2" class="money-flow-card" style="border: 2px solid #1565c0;">
          <v-card-title class="pa-4" style="background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);">
            <div class="d-flex align-center justify-space-between w-100 flex-wrap" style="gap: 12px;">
              <div class="d-flex align-center">
                <v-icon size="32" color="white" class="mr-3">mdi-cash-sync</v-icon>
                <div>
                  <span class="text-h5 font-weight-bold text-white">Money Flow Monitor</span>
                  <div class="text-caption text-white" style="opacity: 0.9;">Real-time tracking of all payments</div>
                </div>
              </div>
              <v-chip color="success" variant="flat" size="large">
                <v-icon start>mdi-check-circle</v-icon>
                System Active
              </v-chip>
            </div>
          </v-card-title>
          
          <v-card-text class="pa-4">
            <!-- Today's Activity -->
            <div class="mb-6">
              <div class="text-h6 mb-3 d-flex align-center">
                <v-icon color="primary" class="mr-2">mdi-calendar-today</v-icon>
                Today's Activity
              </div>
              <v-row>
                <v-col cols="12" md="4">
                  <v-card variant="tonal" color="success" class="pa-4">
                    <div class="d-flex align-center justify-space-between">
                      <div>
                        <div class="text-caption text-grey-darken-2">Money In (Clients)</div>
                        <div class="text-h4 font-weight-bold success--text">
                          {{ formatCurrency(moneyFlow.today?.payments_in || 0) }}
                        </div>
                      </div>
                      <v-icon size="48" color="success" style="opacity: 0.3;">mdi-arrow-down-bold-circle</v-icon>
                    </div>
                  </v-card>
                </v-col>
                <v-col cols="12" md="4">
                  <v-card variant="tonal" color="error" class="pa-4">
                    <div class="d-flex align-center justify-space-between">
                      <div>
                        <div class="text-caption text-grey-darken-2">Money Out (Contractors)</div>
                        <div class="text-h4 font-weight-bold error--text">
                          {{ formatCurrency(moneyFlow.today?.payouts_out || 0) }}
                        </div>
                      </div>
                      <v-icon size="48" color="error" style="opacity: 0.3;">mdi-arrow-up-bold-circle</v-icon>
                    </div>
                  </v-card>
                </v-col>
                <v-col cols="12" md="4">
                  <v-card variant="tonal" color="primary" class="pa-4">
                    <div class="d-flex align-center justify-space-between">
                      <div>
                        <div class="text-caption text-grey-darken-2">Net Change</div>
                        <div class="text-h4 font-weight-bold primary--text">
                          {{ (moneyFlow.today?.net_change || 0) >= 0 ? '+' : '' }}{{ formatCurrency(moneyFlow.today?.net_change || 0) }}
                        </div>
                      </div>
                      <v-icon size="48" color="primary" style="opacity: 0.3;">mdi-scale-balance</v-icon>
                    </div>
                  </v-card>
                </v-col>
              </v-row>
            </div>

            <!-- All-Time Totals -->
            <div class="mb-6">
              <div class="text-h6 mb-3 d-flex align-center">
                <v-icon color="primary" class="mr-2">mdi-chart-line</v-icon>
                All-Time Totals
              </div>
              <v-row>
                <v-col cols="12" sm="6" md="3">
                  <div class="text-caption text-grey-darken-2">Total Received</div>
                  <div class="text-h5 font-weight-bold">
                    {{ formatCurrency(moneyFlow.totals?.total_payments_in || 0) }}
                  </div>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                  <div class="text-caption text-grey-darken-2">Total Paid Out</div>
                  <div class="text-h5 font-weight-bold">
                    {{ formatCurrency(moneyFlow.totals?.total_payouts_out || 0) }}
                  </div>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                  <div class="text-caption text-grey-darken-2">Pending Payouts</div>
                  <div class="text-h5 font-weight-bold warning--text">
                    {{ formatCurrency(moneyFlow.totals?.pending_payouts || 0) }}
                  </div>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                  <div class="text-caption text-grey-darken-2">Expected Balance</div>
                  <div class="text-h5 font-weight-bold primary--text">
                    {{ formatCurrency(moneyFlow.totals?.expected_balance || 0) }}
                  </div>
                </v-col>
              </v-row>
            </div>

            <!-- Stripe Balance Verification -->
            <div class="mb-6" v-if="moneyFlow.totals?.stripe_balance !== null && moneyFlow.totals?.stripe_balance !== undefined">
              <v-alert 
                :color="moneyFlow.totals.balance_difference === 0 ? 'success' : 'warning'"
                variant="tonal"
                border="start"
                border-color="primary"
              >
                <div class="d-flex align-center justify-space-between flex-wrap" style="gap: 12px;">
                  <div>
                    <div class="text-subtitle-2 font-weight-bold mb-1">
                      <v-icon start>mdi-shield-check</v-icon>
                      Stripe Balance Verification
                    </div>
                    <div>
                      Database Expected: <strong>{{ formatCurrency(moneyFlow.totals.expected_balance) }}</strong> 
                      | Stripe Actual: <strong>{{ formatCurrency(moneyFlow.totals.stripe_balance) }}</strong>
                      | Difference: <strong :class="moneyFlow.totals.balance_difference === 0 ? 'success--text' : 'error--text'">
                        {{ formatCurrency(Math.abs(moneyFlow.totals.balance_difference || 0)) }}
                      </strong>
                    </div>
                  </div>
                  <v-chip 
                    :color="moneyFlow.totals.balance_difference === 0 ? 'success' : 'warning'"
                    variant="flat"
                  >
                    {{ moneyFlow.totals.balance_difference === 0 ? '✓ Matched' : '⚠ Review' }}
                  </v-chip>
                </div>
              </v-alert>
            </div>

            <!-- Failed Payouts Warning -->
            <div class="mb-4" v-if="moneyFlow.failed_payouts && moneyFlow.failed_payouts.length > 0">
              <v-alert color="error" variant="tonal" border="start">
                <div class="text-subtitle-2 font-weight-bold mb-2">
                  <v-icon start>mdi-alert-circle</v-icon>
                  {{ moneyFlow.failed_payouts.length }} Failed Payout{{ moneyFlow.failed_payouts.length > 1 ? 's' : '' }}
                </div>
                <div v-for="failed in moneyFlow.failed_payouts" :key="failed.id" class="mb-1">
                  • {{ failed.caregiver_name }}: ${{ failed.amount }} - {{ failed.failure_reason }}
                </div>
              </v-alert>
            </div>

            <!-- Quick Actions -->
            <div class="d-flex gap-2 flex-wrap">
              <v-btn 
                color="primary" 
                variant="flat" 
                prepend-icon="mdi-refresh" 
                @click="$emit('refresh-data')"
                :loading="loading"
              >
                Refresh Data
              </v-btn>
              <v-btn 
                color="primary" 
                variant="outlined" 
                prepend-icon="mdi-file-pdf-box" 
                @click="$emit('export-pdf')"
              >
                Export to PDF
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Transactions and Payment Methods -->
    <v-row>
      <v-col cols="12" md="6">
        <v-card elevation="0" class="mb-3 h-100">
          <v-card-title class="card-header pa-4">
            <span class="section-title-compact error--text">Recent Transactions</span>
          </v-card-title>
          <v-card-text class="pa-4">
            <div v-if="recentTransactions.length === 0" class="text-center py-6 text-grey">
              No recent transactions
            </div>
            <div v-for="transaction in recentTransactions" :key="transaction.id" class="transaction-item mb-3">
              <div class="d-flex justify-space-between align-center mb-1">
                <div class="d-flex align-center">
                  <v-icon 
                    :color="transaction.type === 'payment' ? 'success' : 'warning'" 
                    size="16" 
                    class="mr-2"
                  >
                    {{ transaction.type === 'payment' ? 'mdi-arrow-down' : 'mdi-arrow-up' }}
                  </v-icon>
                  <span class="transaction-desc text-body-2">{{ transaction.description }}</span>
                </div>
                <span 
                  class="transaction-amount font-weight-bold" 
                  :class="transaction.type === 'payment' ? 'success--text' : 'warning--text'"
                >
                  {{ transaction.type === 'payment' ? '+' : '-' }}${{ transaction.amount }}
                </span>
              </div>
              <div class="transaction-time text-caption text-grey">{{ transaction.time }}</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card elevation="0" class="mb-3 h-100">
          <v-card-title class="card-header pa-4">
            <span class="section-title-compact error--text">Payment Methods</span>
          </v-card-title>
          <v-card-text class="pa-4">
            <div v-if="paymentMethods.length === 0" class="text-center py-6 text-grey">
              No payment methods configured
            </div>
            <div v-for="method in paymentMethods" :key="method.type" class="payment-method-item mb-3">
              <div class="d-flex justify-space-between align-center mb-1">
                <div class="d-flex align-center">
                  <v-icon :color="method.color" size="20" class="mr-2">{{ method.icon }}</v-icon>
                  <span class="method-name font-weight-medium">{{ method.name }}</span>
                </div>
                <v-chip :color="method.status === 'Active' ? 'success' : 'warning'" size="small">
                  {{ method.status }}
                </v-chip>
              </div>
              <div class="method-details text-caption text-grey">{{ method.details }}</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
/**
 * PaymentsOverviewTab - Overview tab for the payments section
 * Shows payment stats, money flow monitoring, and recent transactions
 */

defineProps({
  paymentStats: {
    type: Array,
    default: () => []
  },
  moneyFlow: {
    type: Object,
    default: () => ({
      today: { payments_in: 0, payouts_out: 0, net_change: 0 },
      totals: { total_payments_in: 0, total_payouts_out: 0, pending_payouts: 0, expected_balance: 0, stripe_balance: null, balance_difference: 0 },
      failed_payouts: []
    })
  },
  recentTransactions: {
    type: Array,
    default: () => []
  },
  paymentMethods: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
});

defineEmits(['refresh-data', 'export-pdf']);

/**
 * Format currency value
 */
const formatCurrency = (value) => {
  if (value === null || value === undefined) return '$0.00';
  return '$' + Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<style scoped>
.compact-stat-card {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.money-flow-card {
  border-radius: 12px;
}

.transaction-item {
  padding-bottom: 8px;
  border-bottom: 1px solid #f3f4f6;
}

.transaction-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.payment-method-item {
  padding-bottom: 8px;
  border-bottom: 1px solid #f3f4f6;
}

.payment-method-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
  * {
    transition: none !important;
    animation: none !important;
  }
}
</style>
