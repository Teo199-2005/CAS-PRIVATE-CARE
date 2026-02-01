<template>
  <div class="caregiver-payments-tab">
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
            ? 'All payment buttons are now active. You can process caregiver payments today.' 
            : 'Payment processing is only available on Fridays to ensure proper fund allocation and processing time.' 
          }}
        </div>
        <div class="text-body-2 text-medium-emphasis">
          <strong>Why Fridays Only?</strong> 
          This ensures: (1) Consistent weekly payment schedule for all staff, 
          (2) Adequate time for bank processing before the weekend, 
          (3) Proper reconciliation of weekly hours and earnings, 
          (4) Compliance with payment processing protocols.
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
            placeholder="Search caregivers..." 
            prepend-inner-icon="mdi-magnify" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Search caregivers"
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select 
            v-model="statusFilter" 
            :items="['All', 'Paid', 'Pending', 'Partial', 'No Hours']" 
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
          <v-tooltip :text="isFriday ? 'Process all payments' : 'Payments are processed only on Fridays'">
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
        <span class="section-title-compact error--text">Caregiver Payments</span>
      </v-card-title>
      
      <!-- Desktop Table View -->
      <v-data-table 
        v-if="!isMobile" 
        :headers="headers" 
        :items="filteredPayments" 
        :items-per-page="10" 
        :loading="loading"
        class="elevation-0 table-no-checkbox"
      >
        <template v-slot:item.bank_status="{ item }">
          <v-chip :color="item.bank_connected ? 'success' : 'warning'" size="small" variant="flat">
            <v-icon start size="small">{{ item.bank_connected ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
            {{ item.bank_status }}
          </v-chip>
        </template>
        <template v-slot:item.status="{ item }">
          <v-chip :color="getPaymentStatusColor(item.status)" size="small" class="font-weight-bold">
            {{ item.status }}
          </v-chip>
        </template>
        <template v-slot:item.actions="{ item }">
          <div class="action-buttons d-flex gap-1">
            <v-btn 
              class="action-btn-view" 
              icon="mdi-eye" 
              size="small" 
              @click="$emit('view-details', item)"
              aria-label="View payment details"
            />
            <v-tooltip v-if="item.can_pay && isFriday" text="Process Payment">
              <template v-slot:activator="{ props }">
                <v-btn 
                  v-bind="props" 
                  class="action-btn-pay-now" 
                  icon="mdi-cash-fast" 
                  size="small" 
                  color="success" 
                  @click="$emit('pay', item)"
                  aria-label="Process payment for this caregiver"
                />
              </template>
            </v-tooltip>
            <v-tooltip v-else-if="item.can_pay && !isFriday" text="Payments are processed only on Fridays">
              <template v-slot:activator="{ props }">
                <v-btn 
                  v-bind="props" 
                  icon="mdi-cash-clock" 
                  size="small" 
                  color="grey" 
                  disabled
                  aria-label="Payment available on Fridays only"
                />
              </template>
            </v-tooltip>
            <v-tooltip v-else-if="!item.bank_connected" text="Bank account not connected">
              <template v-slot:activator="{ props }">
                <v-btn 
                  v-bind="props" 
                  icon="mdi-bank-off" 
                  size="small" 
                  color="grey" 
                  disabled
                  aria-label="Bank account not connected"
                />
              </template>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
      
      <!-- Mobile Card View -->
      <div v-else class="mobile-cards-container pa-3">
        <div v-if="filteredPayments.length === 0" class="text-center py-8 text-grey">
          No caregiver payments found
        </div>
        <v-card 
          v-for="item in paginatedPayments" 
          :key="item.id" 
          class="mobile-data-card mb-3" 
          elevation="2" 
          rounded="lg"
        >
          <v-card-text class="pa-0">
            <div 
              class="mobile-card-header d-flex align-center justify-space-between pa-3" 
              style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);"
            >
              <span class="text-white font-weight-bold text-body-1">{{ item.name }}</span>
              <v-chip :color="getPaymentStatusColor(item.status)" size="small" class="font-weight-bold">
                {{ item.status }}
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
                <span class="mobile-card-label text-grey-darken-1">Hours Worked:</span>
                <span class="mobile-card-value">{{ item.hours_worked || item.hours }} hrs</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Amount Due:</span>
                <span class="mobile-card-value font-weight-bold text-success">${{ item.amount || item.total }}</span>
              </div>
            </div>
            <div class="mobile-card-actions d-flex justify-center ga-2 pa-3" style="background: #f9fafb; border-top: 1px solid #e5e7eb;">
              <v-btn color="primary" variant="tonal" size="small" prepend-icon="mdi-eye" @click="$emit('view-details', item)">View</v-btn>
              <v-btn 
                v-if="item.can_pay && isFriday" 
                color="success" 
                variant="tonal" 
                size="small" 
                prepend-icon="mdi-cash-fast" 
                @click="$emit('pay', item)"
              >
                Pay
              </v-btn>
              <v-chip v-else-if="item.can_pay && !isFriday" color="grey" size="small">Friday Only</v-chip>
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
 * CaregiverPaymentsTab - Caregiver payments management tab
 * Includes Friday-only payout restriction notice
 */

const props = defineProps({
  payments: {
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
  { title: 'Hours', key: 'hours_worked' },
  { title: 'Rate', key: 'rate' },
  { title: 'Amount', key: 'amount' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false }
]);

/**
 * Filter payments based on search and filters
 */
const filteredPayments = computed(() => {
  let result = [...props.payments];
  
  // Search filter
  if (search.value) {
    const searchLower = search.value.toLowerCase();
    result = result.filter(p => 
      (p.name || '').toLowerCase().includes(searchLower) ||
      (p.email || '').toLowerCase().includes(searchLower)
    );
  }
  
  // Status filter
  if (statusFilter.value !== 'All') {
    result = result.filter(p => p.status === statusFilter.value);
  }
  
  return result;
});

/**
 * Paginated payments for mobile view
 */
const paginatedPayments = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredPayments.value.slice(start, start + itemsPerPage);
});

/**
 * Total pages for pagination
 */
const totalPages = computed(() => Math.ceil(filteredPayments.value.length / itemsPerPage));

/**
 * Get status chip color
 */
const getPaymentStatusColor = (status) => {
  const colors = {
    'Paid': 'success',
    'Pending': 'warning',
    'Partial': 'info',
    'No Hours': 'grey'
  };
  return colors[status] || 'grey';
};
</script>

<style scoped>
.mobile-data-card {
  overflow: hidden;
}

.table-no-checkbox :deep(.v-data-table__checkbox) {
  display: none;
}

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
  * {
    transition: none !important;
  }
}
</style>
