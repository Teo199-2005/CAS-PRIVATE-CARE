<template>
  <div class="company-account-tab">
    <!-- Stripe Account Status -->
    <v-row>
      <v-col cols="12">
        <v-card elevation="0" class="mb-4 border rounded-lg">
          <v-card-text class="pa-5">
            <div class="d-flex align-center justify-space-between mb-4">
              <div class="d-flex align-center">
                <v-icon size="24" color="grey-darken-2" class="mr-3">mdi-credit-card-outline</v-icon>
                <div>
                  <div class="text-subtitle-1 font-weight-bold">Stripe Account</div>
                  <div class="text-caption text-grey">Payment Processing</div>
                </div>
              </div>
              <v-chip 
                :color="companyAccount.account?.charges_enabled ? 'success' : 'warning'" 
                variant="tonal"
                size="small"
              >
                {{ companyAccount.account?.charges_enabled ? 'Active' : 'Setup Required' }}
              </v-chip>
            </div>

            <v-divider class="mb-4" />
            
            <div class="text-body-2 mb-4">
              <div class="font-weight-medium mb-1">{{ companyAccount.account?.business_name || 'CAS Private Care' }}</div>
              <div class="text-grey text-caption">Platform Account</div>
            </div>

            <div class="d-flex flex-column" style="gap: 12px;">
              <div class="d-flex justify-space-between">
                <span class="text-grey text-body-2">Account ID</span>
                <code class="text-body-2">{{ companyAccount.account?.id?.substring(0, 10) || 'acct_' }}••••</code>
              </div>
              <div class="d-flex justify-space-between">
                <span class="text-grey text-body-2">Type</span>
                <span class="text-body-2">Company (LLC)</span>
              </div>
              <div class="d-flex justify-space-between">
                <span class="text-grey text-body-2">Country</span>
                <span class="text-body-2">{{ companyAccount.account?.country || 'US' }}</span>
              </div>
              <div class="d-flex justify-space-between">
                <span class="text-grey text-body-2">Currency</span>
                <span class="text-body-2">{{ companyAccount.account?.default_currency || 'USD' }}</span>
              </div>
              <div class="d-flex justify-space-between">
                <span class="text-grey text-body-2">Payouts</span>
                <span class="text-body-2">Weekly</span>
              </div>
            </div>

            <v-btn 
              color="grey-darken-3" 
              variant="outlined" 
              block
              class="mt-5"
              href="https://dashboard.stripe.com" 
              target="_blank"
              size="small"
              aria-label="Open Stripe Dashboard in new tab"
            >
              Open Stripe Dashboard
              <v-icon end size="16">mdi-open-in-new</v-icon>
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Balance Cards Row -->
    <v-row>
      <!-- Available Balance -->
      <v-col cols="12" md="4">
        <v-card elevation="0" class="h-100 border rounded-lg">
          <v-card-text class="pa-5">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-caption text-grey text-uppercase font-weight-medium">Available</span>
              <v-icon size="20" color="grey-lighten-1">mdi-wallet-outline</v-icon>
            </div>
            <div class="text-h4 font-weight-bold mb-1">
              {{ formatCurrency(moneyFlow.totals?.stripe_balance || 0) }}
            </div>
            <div class="text-caption text-grey">Ready for payout</div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Pending Balance -->
      <v-col cols="12" md="4">
        <v-card elevation="0" class="h-100 border rounded-lg">
          <v-card-text class="pa-5">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-caption text-grey text-uppercase font-weight-medium">Pending</span>
              <v-icon size="20" color="grey-lighten-1">mdi-clock-outline</v-icon>
            </div>
            <div class="text-h4 font-weight-bold mb-1">
              {{ formatCurrency(moneyFlow.totals?.pending_payouts || 0) }}
            </div>
            <div class="text-caption text-grey">Processing</div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- This Month Revenue -->
      <v-col cols="12" md="4">
        <v-card elevation="0" class="h-100 border rounded-lg">
          <v-card-text class="pa-5">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-caption text-grey text-uppercase font-weight-medium">This Month</span>
              <v-chip 
                :color="(companyAccount.percent_change || 0) >= 0 ? 'success' : 'error'" 
                variant="tonal" 
                size="x-small"
              >
                {{ (companyAccount.percent_change || 0) >= 0 ? '+' : '' }}{{ companyAccount.percent_change || 0 }}%
              </v-chip>
            </div>
            <div class="text-h4 font-weight-bold mb-1">
              {{ formatCurrency(moneyFlow.totals?.total_received || 0) }}
            </div>
            <div class="text-caption text-grey">Revenue</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Payouts History -->
    <v-row class="mt-4">
      <v-col cols="12">
        <v-card elevation="0" class="border rounded-lg">
          <v-card-text class="pa-4">
            <div class="d-flex align-center justify-space-between mb-4">
              <span class="text-subtitle-1 font-weight-bold">Recent Payouts</span>
              <v-btn color="grey-darken-3" variant="text" size="small" @click="$emit('export')">
                Export
                <v-icon end size="16">mdi-download</v-icon>
              </v-btn>
            </div>
            <v-data-table
              :headers="payoutHeaders"
              :items="recentPayouts"
              :items-per-page="5"
              :loading="loading"
              class="elevation-0"
            >
              <template v-slot:item.type="{ item }">
                <v-chip :color="item.type === 'Payout' ? 'warning' : 'success'" size="small" variant="flat">
                  {{ item.type }}
                </v-chip>
              </template>
              <template v-slot:item.amount="{ item }">
                <span :class="item.type === 'Payout' ? 'warning--text' : 'success--text'" class="font-weight-bold">
                  {{ item.type === 'Payout' ? '-' : '+' }}${{ item.amount }}
                </span>
              </template>
              <template v-slot:item.txn_id="{ item }">
                <code class="text-caption">{{ item.txn_id }}</code>
              </template>
              <template v-slot:item.status="{ item }">
                <v-chip :color="item.status === 'Completed' ? 'success' : 'warning'" size="small" variant="flat">
                  {{ item.status }}
                </v-chip>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref } from 'vue';

/**
 * CompanyAccountTab - Company Stripe account tab
 * Shows account status, balances, and payout history
 */

defineProps({
  companyAccount: {
    type: Object,
    default: () => ({})
  },
  moneyFlow: {
    type: Object,
    default: () => ({
      totals: { stripe_balance: 0, pending_payouts: 0, total_received: 0 }
    })
  },
  recentPayouts: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
});

defineEmits(['export']);

const payoutHeaders = ref([
  { title: 'Date', key: 'date' },
  { title: 'Description', key: 'description' },
  { title: 'Type', key: 'type' },
  { title: 'Amount', key: 'amount' },
  { title: 'Transaction ID', key: 'txn_id' },
  { title: 'Status', key: 'status' }
]);

/**
 * Format currency value
 */
const formatCurrency = (value) => {
  if (value === null || value === undefined) return '$0.00';
  return '$' + Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<style scoped>
.border {
  border: 1px solid #e5e7eb !important;
}

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
  * {
    transition: none !important;
  }
}
</style>
