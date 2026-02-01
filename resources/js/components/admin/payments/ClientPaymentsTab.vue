<template>
  <div class="client-payments-tab">
    <!-- Filters -->
    <div class="mb-4">
      <v-row class="align-center">
        <v-col cols="12" md="4">
          <v-text-field 
            v-model="search" 
            placeholder="Search payments..." 
            prepend-inner-icon="mdi-magnify" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Search payments"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-select 
            v-model="statusFilter" 
            :items="['All', 'Paid', 'Pending', 'Overdue']" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Filter by status"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-select 
            v-model="periodFilter" 
            :items="['All Time', 'This Month', 'Last Month', 'Last 3 Months']" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Filter by period"
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-btn color="error" prepend-icon="mdi-plus" @click="$emit('add-payment')">Add Payment</v-btn>
        </v-col>
      </v-row>
    </div>

    <v-card elevation="0">
      <v-card-title class="card-header pa-4">
        <span class="section-title-compact error--text">Client Payments</span>
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
        <template v-slot:item.status="{ item }">
          <v-chip :color="getPaymentStatusColor(item.status)" size="small" class="font-weight-bold">
            {{ item.status }}
          </v-chip>
        </template>
        <template v-slot:item.actions="{ item }">
          <div class="action-buttons d-flex gap-2">
            <v-btn 
              color="primary" 
              variant="flat" 
              icon 
              size="small" 
              @click="$emit('view-payment', item)"
              aria-label="View payment details"
            >
              <v-icon>mdi-eye</v-icon>
            </v-btn>
            <v-btn 
              v-if="item.status === 'Pending' || item.status === 'Overdue'" 
              color="success" 
              variant="flat" 
              icon 
              size="small" 
              @click="$emit('mark-paid', item)"
              aria-label="Mark as paid"
            >
              <v-icon>mdi-check</v-icon>
            </v-btn>
          </div>
        </template>
      </v-data-table>
      
      <!-- Mobile Card View -->
      <div v-else class="mobile-cards-container pa-3">
        <div v-if="filteredPayments.length === 0" class="text-center py-8 text-grey">
          No client payments found
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
              style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);"
            >
              <span class="text-white font-weight-bold text-body-1">{{ item.client_name || item.client }}</span>
              <v-chip :color="getPaymentStatusColor(item.status)" size="small" class="font-weight-bold">
                {{ item.status }}
              </v-chip>
            </div>
            <div class="mobile-card-body pa-3">
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Amount:</span>
                <span class="mobile-card-value font-weight-bold text-success">{{ formatAmount(item.amount) }}</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Date:</span>
                <span class="mobile-card-value">{{ item.date }}</span>
              </div>
              <div v-if="item.description" class="mobile-card-row d-flex justify-space-between py-2">
                <span class="mobile-card-label text-grey-darken-1">Description:</span>
                <span class="mobile-card-value text-caption">{{ item.description }}</span>
              </div>
            </div>
            <div class="mobile-card-actions d-flex justify-center ga-2 pa-3" style="background: #f9fafb; border-top: 1px solid #e5e7eb;">
              <v-btn color="primary" variant="tonal" size="small" prepend-icon="mdi-eye" @click="$emit('view-payment', item)">
                View
              </v-btn>
              <v-btn 
                v-if="item.status === 'Pending' || item.status === 'Overdue'" 
                color="success" 
                variant="tonal" 
                size="small" 
                prepend-icon="mdi-check" 
                @click="$emit('mark-paid', item)"
              >
                Mark Paid
              </v-btn>
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
 * ClientPaymentsTab - Client payments management tab
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
  loading: {
    type: Boolean,
    default: false
  }
});

defineEmits(['view-payment', 'mark-paid', 'add-payment']);

// Local state
const search = ref('');
const statusFilter = ref('All');
const periodFilter = ref('All Time');
const currentPage = ref(1);
const itemsPerPage = 10;

const headers = ref([
  { title: 'Client', key: 'client_name' },
  { title: 'Amount', key: 'amount' },
  { title: 'Date', key: 'date' },
  { title: 'Description', key: 'description' },
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
      (p.client_name || p.client || '').toLowerCase().includes(searchLower) ||
      (p.description || '').toLowerCase().includes(searchLower)
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
    'Overdue': 'error'
  };
  return colors[status] || 'grey';
};

/**
 * Format payment amount
 */
const formatAmount = (amount) => {
  if (!amount) return '$0.00';
  return '$' + Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
