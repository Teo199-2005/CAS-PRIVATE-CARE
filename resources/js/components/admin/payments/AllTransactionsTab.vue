<template>
  <div class="all-transactions-tab">
    <!-- Filters -->
    <div class="mb-4">
      <v-row class="align-center">
        <v-col cols="12" md="4">
          <v-text-field 
            v-model="search" 
            placeholder="Search transactions..." 
            prepend-inner-icon="mdi-magnify" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Search transactions"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-select 
            v-model="typeFilter" 
            :items="['All', 'Payment', 'Salary', 'Refund', 'Fee']" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Filter by type"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-select 
            v-model="periodFilter" 
            :items="['All Time', 'Today', 'This Week', 'This Month']" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Filter by period"
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-btn color="error" prepend-icon="mdi-file-pdf-box" @click="$emit('export')">Export PDF</v-btn>
        </v-col>
      </v-row>
    </div>

    <v-card elevation="0">
      <v-card-title class="card-header pa-4">
        <span class="section-title-compact error--text">All Transactions</span>
      </v-card-title>
      
      <!-- Desktop Table View -->
      <v-data-table 
        v-if="!isMobile" 
        :headers="headers" 
        :items="filteredTransactions" 
        :items-per-page="15" 
        :loading="loading"
        class="elevation-0 table-no-checkbox"
      >
        <template v-slot:item.type="{ item }">
          <v-chip :color="getTransactionTypeColor(item.type)" size="small" class="font-weight-bold">
            {{ item.type }}
          </v-chip>
        </template>
        <template v-slot:item.amount="{ item }">
          <span :class="item.type === 'Payment' ? 'success--text' : 'warning--text'" class="font-weight-bold">
            {{ item.type === 'Payment' ? '+' : '-' }}${{ item.amount }}
          </span>
        </template>
        <template v-slot:item.status="{ item }">
          <v-chip v-if="item.status" :color="item.status === 'Completed' ? 'success' : 'warning'" size="small">
            {{ item.status }}
          </v-chip>
        </template>
      </v-data-table>
      
      <!-- Mobile Card View -->
      <div v-else class="mobile-cards-container pa-3">
        <div v-if="filteredTransactions.length === 0" class="text-center py-8 text-grey">
          No transactions found
        </div>
        <v-card 
          v-for="(item, index) in paginatedTransactions" 
          :key="index" 
          class="mobile-data-card mb-3" 
          elevation="2" 
          rounded="lg"
        >
          <v-card-text class="pa-0">
            <div 
              class="mobile-card-header d-flex align-center justify-space-between pa-3" 
              :style="item.type === 'Payment' ? 'background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);' : 'background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);'"
            >
              <span class="text-white font-weight-bold text-body-1">{{ item.description || item.reference }}</span>
              <v-chip :color="getTransactionTypeColor(item.type)" size="small" class="font-weight-bold">
                {{ item.type }}
              </v-chip>
            </div>
            <div class="mobile-card-body pa-3">
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Amount:</span>
                <span class="mobile-card-value font-weight-bold" :class="item.type === 'Payment' ? 'text-success' : 'text-error'">
                  {{ item.type === 'Payment' ? '+' : '-' }}${{ item.amount }}
                </span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Date:</span>
                <span class="mobile-card-value">{{ item.date || item.created_at }}</span>
              </div>
              <div v-if="item.status" class="mobile-card-row d-flex justify-space-between py-2">
                <span class="mobile-card-label text-grey-darken-1">Status:</span>
                <v-chip :color="item.status === 'Completed' ? 'success' : 'warning'" size="x-small">
                  {{ item.status }}
                </v-chip>
              </div>
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
 * AllTransactionsTab - All transactions listing tab
 */

const props = defineProps({
  transactions: {
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

defineEmits(['export']);

// Local state
const search = ref('');
const typeFilter = ref('All');
const periodFilter = ref('All Time');
const currentPage = ref(1);
const itemsPerPage = 15;

const headers = ref([
  { title: 'Description', key: 'description' },
  { title: 'Type', key: 'type' },
  { title: 'Amount', key: 'amount' },
  { title: 'Date', key: 'date' },
  { title: 'Status', key: 'status' }
]);

/**
 * Filter transactions based on search and filters
 */
const filteredTransactions = computed(() => {
  let result = [...props.transactions];
  
  if (search.value) {
    const searchLower = search.value.toLowerCase();
    result = result.filter(t => 
      (t.description || '').toLowerCase().includes(searchLower) ||
      (t.reference || '').toLowerCase().includes(searchLower)
    );
  }
  
  if (typeFilter.value !== 'All') {
    result = result.filter(t => t.type === typeFilter.value);
  }
  
  return result;
});

/**
 * Paginated transactions for mobile view
 */
const paginatedTransactions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredTransactions.value.slice(start, start + itemsPerPage);
});

/**
 * Total pages for pagination
 */
const totalPages = computed(() => Math.ceil(filteredTransactions.value.length / itemsPerPage));

/**
 * Get transaction type chip color
 */
const getTransactionTypeColor = (type) => {
  const colors = {
    'Payment': 'success',
    'Salary': 'warning',
    'Refund': 'error',
    'Fee': 'grey'
  };
  return colors[type] || 'grey';
};
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
