<template>
  <div class="marketing-commissions-tab">
    <!-- Friday Payout Notice Banner -->
    <v-alert 
      :color="isFriday ? 'success' : 'info'"
      :variant="isFriday ? 'tonal' : 'outlined'"
      border="start"
      border-color="primary"
      class="mb-4"
      prominent
    >
      <template v-slot:prepend>
        <v-icon :color="isFriday ? 'success' : 'primary'" size="x-large">
          {{ isFriday ? 'mdi-check-circle' : 'mdi-information' }}
        </v-icon>
      </template>
      <div class="d-flex flex-column">
        <div class="text-h6 font-weight-bold mb-2">
          {{ isFriday ? 'Today is Payout Day!' : 'Payout Schedule Information' }}
        </div>
        <div class="text-body-1 mb-2">
          {{ isFriday 
            ? 'All payment buttons are now active. You can process marketing commission payments today.' 
            : 'Commission payment processing is only available on Fridays to maintain consistency with all staff payments.' 
          }}
        </div>
        <div class="text-body-2 text-medium-emphasis">
          <strong>Why Fridays Only?</strong> 
          Unified payment schedule across all departments, streamlined accounting processes, 
          and predictable cash flow management for both the agency and commission earners.
        </div>
        <v-chip 
          v-if="!isFriday"
          color="primary" 
          variant="flat" 
          size="small" 
          class="mt-2 align-self-start"
        >
          <v-icon start size="small">mdi-calendar</v-icon>
          Next Payout: This Friday
        </v-chip>
      </div>
    </v-alert>
    
    <!-- Filters -->
    <div class="mb-4">
      <v-row class="align-center">
        <v-col cols="12" md="3">
          <v-text-field 
            v-model="search" 
            placeholder="Search marketing staff..." 
            prepend-inner-icon="mdi-magnify" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Search marketing staff"
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select 
            v-model="statusFilter" 
            :items="['All', 'Paid', 'Pending']" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Filter by status"
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select 
            v-model="periodFilter" 
            :items="['Current Month', 'Last Month', 'Last 3 Months']" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Filter by period"
          />
        </v-col>
        <v-col cols="12" md="3" class="d-flex gap-2">
          <v-btn 
            color="success" 
            @click="$emit('export-pdf')" 
            variant="elevated"
            size="large"
            class="font-weight-bold text-none"
            elevation="4"
          >
            <v-icon size="large" class="mr-2">mdi-file-pdf-box</v-icon>
            Export PDF
          </v-btn>
          <v-tooltip :text="isFriday ? 'Process all marketing commissions' : 'Payments are processed only on Fridays'">
            <template v-slot:activator="{ props }">
              <v-btn 
                v-bind="props"
                color="error" 
                prepend-icon="mdi-cash-multiple" 
                @click="$emit('pay-all')"
                :disabled="!isFriday"
              >
                Pay All
              </v-btn>
            </template>
          </v-tooltip>
        </v-col>
      </v-row>
    </div>

    <v-card elevation="0">
      <v-card-title class="card-header pa-4">
        <span class="section-title-compact error--text">Marketing Staff Commissions</span>
      </v-card-title>
      
      <!-- Desktop Table View -->
      <v-data-table 
        v-if="!isMobile"
        :headers="headers" 
        :items="filteredCommissions" 
        :items-per-page="10" 
        class="elevation-0 table-no-checkbox"
        :loading="loading"
      >
        <template v-slot:item.bank_status="{ item }">
          <v-chip :color="item.bank_connected ? 'success' : 'warning'" size="small" variant="flat">
            <v-icon start size="small">{{ item.bank_connected ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
            {{ item.bank_status }}
          </v-chip>
        </template>
        <template v-slot:item.tier="{ item }">
          <v-chip :color="getTierChipColor(item.tier)" size="small" variant="flat" class="font-weight-medium">
            {{ item.tier_label || item.tier || 'Silver' }} · ${{ (item.commission_per_hour ?? 1).toFixed(2) }}/hr
          </v-chip>
        </template>
        <template v-slot:item.payment_status="{ item }">
          <v-chip :color="item.payment_status === 'Paid' ? 'success' : 'warning'" size="small" class="font-weight-bold">
            {{ item.payment_status }}
          </v-chip>
        </template>
        <template v-slot:item.actions="{ item }">
          <div class="action-buttons d-flex gap-1">
            <v-btn 
              class="action-btn-view" 
              icon="mdi-eye" 
              size="small" 
              @click="$emit('view-details', item)"
              aria-label="View details"
            />
            <v-tooltip v-if="item.pending_commission > 0 && item.bank_connected && isFriday" text="Process Payment">
              <template v-slot:activator="{ props }">
                <v-btn 
                  v-bind="props"
                  class="action-btn-pay-now" 
                  icon="mdi-cash-fast" 
                  size="small" 
                  color="success" 
                  @click="$emit('pay', item)"
                  :loading="item.paying"
                  aria-label="Process payment"
                />
              </template>
            </v-tooltip>
            <v-tooltip v-else-if="item.pending_commission > 0 && item.bank_connected && !isFriday" text="Payments are processed only on Fridays">
              <template v-slot:activator="{ props }">
                <v-btn 
                  v-bind="props"
                  icon="mdi-cash-clock" 
                  size="small" 
                  color="grey" 
                  disabled
                  aria-label="Available Fridays only"
                />
              </template>
            </v-tooltip>
            <v-tooltip v-else-if="!item.bank_connected" text="Bank account not connected">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-bank-off" size="small" color="grey" disabled aria-label="Bank not connected" />
              </template>
            </v-tooltip>
            <v-tooltip v-else text="No pending commission">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-cash-off" size="small" color="grey" disabled aria-label="No pending commission" />
              </template>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
      
      <!-- Mobile Card View -->
      <div v-else class="mobile-cards-container pa-3">
        <div v-if="filteredCommissions.length === 0" class="text-center py-8 text-grey">
          No marketing commissions found
        </div>
        <v-card 
          v-for="item in paginatedCommissions" 
          :key="item.id" 
          class="mobile-data-card mb-3" 
          elevation="2" 
          rounded="lg"
        >
          <v-card-text class="pa-0">
            <div 
              class="mobile-card-header d-flex align-center justify-space-between pa-3" 
              style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);"
            >
              <span class="text-white font-weight-bold text-body-1">{{ item.name }}</span>
              <v-chip :color="item.payment_status === 'Paid' ? 'success' : 'warning'" size="small" class="font-weight-bold">
                {{ item.payment_status }}
              </v-chip>
            </div>
            <div class="mobile-card-body pa-3">
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Bank Status:</span>
                <v-chip :color="item.bank_connected ? 'success' : 'warning'" size="x-small" variant="flat">
                  <v-icon start size="x-small">{{ item.bank_connected ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
                  {{ item.bank_status }}
                </v-chip>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Tier:</span>
                <v-chip :color="getTierChipColor(item.tier)" size="x-small" variant="flat">{{ item.tier_label || item.tier || 'Silver' }} · ${{ (item.commission_per_hour ?? 1).toFixed(2) }}/hr</v-chip>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Referrals:</span>
                <span class="mobile-card-value">{{ item.clients_referred ?? item.referral_count ?? 0 }}</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Total Earned:</span>
                <span class="mobile-card-value font-weight-bold">${{ item.total_commission ?? item.total_earned ?? 0 }}</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2">
                <span class="mobile-card-label text-grey-darken-1">Pending:</span>
                <span class="mobile-card-value font-weight-bold text-warning">${{ item.pending_commission || 0 }}</span>
              </div>
            </div>
            <div class="mobile-card-actions d-flex justify-center ga-2 pa-3" style="background: #f9fafb; border-top: 1px solid #e5e7eb;">
              <v-btn color="primary" variant="tonal" size="small" prepend-icon="mdi-eye" @click="$emit('view-details', item)">View</v-btn>
              <v-btn 
                v-if="item.pending_commission > 0 && item.bank_connected && isFriday" 
                color="success" 
                variant="tonal" 
                size="small" 
                prepend-icon="mdi-cash-fast" 
                @click="$emit('pay', item)" 
                :loading="item.paying"
              >
                Pay
              </v-btn>
              <v-chip v-else-if="item.pending_commission > 0 && !isFriday" color="grey" size="small">Friday Only</v-chip>
              <v-chip v-else-if="!item.bank_connected" color="warning" size="small">No Bank</v-chip>
            </div>
          </v-card-text>
        </v-card>
        
        <!-- Mobile Pagination -->
        <v-pagination
          v-if="totalPages > 1"
          v-model="currentPage"
          :length="totalPages"
          :total-visible="5"
          density="compact"
          class="mt-4"
        />
      </div>
    </v-card>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

/**
 * MarketingCommissionsTab - Marketing staff commissions tab
 */

const props = defineProps({
  commissions: {
    type: Array,
    default: () => []
  },
  isMobile: {
    type: Boolean,
    default: false
  },
  isFriday: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  }
});

defineEmits(['view-details', 'pay', 'pay-all', 'export-pdf']);

// Local state
const search = ref('');
const statusFilter = ref('All');
const periodFilter = ref('Current Month');
const currentPage = ref(1);
const itemsPerPage = 10;

const headers = ref([
  { title: 'Name', key: 'name' },
  { title: 'Bank Status', key: 'bank_status' },
  { title: 'Tier', key: 'tier', align: 'center' },
  { title: 'Referrals', key: 'referral_count' },
  { title: 'Total Earned', key: 'total_earned' },
  { title: 'Pending', key: 'pending_commission' },
  { title: 'Status', key: 'payment_status' },
  { title: 'Actions', key: 'actions', sortable: false }
]);

const filteredCommissions = computed(() => {
  let result = [...props.commissions];
  
  if (search.value) {
    const searchLower = search.value.toLowerCase();
    result = result.filter(c => 
      (c.name || '').toLowerCase().includes(searchLower) ||
      (c.email || '').toLowerCase().includes(searchLower)
    );
  }
  
  if (statusFilter.value !== 'All') {
    result = result.filter(c => c.payment_status === statusFilter.value);
  }
  
  return result;
});

const paginatedCommissions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredCommissions.value.slice(start, start + itemsPerPage);
});

const totalPages = computed(() => Math.ceil(filteredCommissions.value.length / itemsPerPage));

function getTierChipColor(tier) {
  const colors = { Silver: 'grey', Gold: 'amber', Platinum: 'indigo' };
  return colors[tier] || 'grey';
}
</script>

<style scoped>
.mobile-data-card {
  overflow: hidden;
}

.table-no-checkbox :deep(.v-data-table__checkbox) {
  display: none;
}

@media (prefers-reduced-motion: reduce) {
  * {
    transition: none !important;
  }
}
</style>
