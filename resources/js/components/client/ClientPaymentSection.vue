<template>
  <div class="client-payment-section">
    <v-row>
      <!-- Saved Payment Methods - Full Width -->
      <v-col cols="12">
        <v-card elevation="0" class="mb-6">
          <v-card-title class="card-header pa-8">
            <span class="section-title primary--text">Saved Payment Methods</span>
          </v-card-title>
          <v-card-text class="pa-8">
            <!-- Slot for payment methods component -->
            <slot name="payment-methods">
              <div class="text-center py-8 text-grey">
                <v-icon size="48" color="grey-lighten-1">mdi-credit-card-outline</v-icon>
                <div class="mt-2">No payment methods configured</div>
              </div>
            </slot>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Payment History -->
      <v-col cols="12" md="8">
        <v-card elevation="0" class="mb-6">
          <v-card-title class="card-header pa-8">
            <span class="section-title primary--text">Payment History</span>
          </v-card-title>
          <v-card-text class="pa-8">
            <!-- Desktop Table View -->
            <v-data-table
              v-if="!isMobile"
              :headers="headers"
              :items="paymentHistory"
              :items-per-page="10"
              class="elevation-0"
            >
              <template v-slot:item.amount="{ item }">
                <span class="font-weight-bold">${{ formatAmount(item.amount) }}</span>
              </template>

              <template v-slot:item.status="{ item }">
                <v-chip 
                  v-if="item.paymentStatus === 'paid'" 
                  color="success" 
                  size="small" 
                  prepend-icon="mdi-check-circle"
                >
                  Paid
                </v-chip>
                <v-chip 
                  v-else 
                  color="warning" 
                  size="small" 
                  prepend-icon="mdi-clock-outline"
                >
                  Pending
                </v-chip>
              </template>

              <template v-slot:item.receipt="{ item }">
                <v-btn
                  v-if="item.paymentStatus === 'paid'"
                  color="primary"
                  size="small"
                  variant="text"
                  icon="mdi-download"
                  @click="$emit('download-receipt', item.id)"
                  aria-label="Download receipt"
                />
                <span v-else class="text-grey">—</span>
              </template>

              <template v-slot:no-data>
                <div class="text-center py-8">
                  <v-icon size="48" color="grey" class="mb-4">mdi-receipt-text-outline</v-icon>
                  <div class="text-h6 text-grey">No payment history yet</div>
                  <div class="text-body-2 text-grey mt-2">Your completed payments will appear here</div>
                </div>
              </template>
            </v-data-table>

            <!-- Mobile Card View -->
            <div v-else class="mobile-cards-container">
              <div v-if="paymentHistory.length === 0" class="text-center py-8">
                <v-icon size="48" color="grey">mdi-receipt-text-outline</v-icon>
                <div class="text-h6 text-grey mt-2">No payment history</div>
              </div>
              <v-card 
                v-for="payment in paginatedHistory" 
                :key="payment.id" 
                class="mobile-payment-card mb-3" 
                elevation="1"
              >
                <v-card-text class="pa-4">
                  <div class="d-flex justify-space-between align-center mb-2">
                    <span class="font-weight-bold">Booking #{{ payment.id }}</span>
                    <v-chip 
                      :color="payment.paymentStatus === 'paid' ? 'success' : 'warning'" 
                      size="x-small"
                    >
                      {{ payment.paymentStatus === 'paid' ? 'Paid' : 'Pending' }}
                    </v-chip>
                  </div>
                  <div class="d-flex justify-space-between text-caption text-grey">
                    <span>{{ payment.date }}</span>
                    <span>{{ payment.service }}</span>
                  </div>
                  <div class="d-flex justify-space-between align-center mt-2">
                    <span class="text-h6 font-weight-bold primary--text">${{ formatAmount(payment.amount) }}</span>
                    <v-btn
                      v-if="payment.paymentStatus === 'paid'"
                      color="primary"
                      size="small"
                      variant="text"
                      @click="$emit('download-receipt', payment.id)"
                    >
                      <v-icon start size="small">mdi-download</v-icon>
                      Receipt
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
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Payment Summary -->
      <v-col cols="12" md="4">
        <v-card elevation="0" class="mb-6">
          <v-card-title class="card-header pa-8">
            <span class="section-title primary--text">Payment Summary</span>
          </v-card-title>
          <v-card-text class="pa-8">
            <div class="summary-item d-flex justify-space-between py-2">
              <span class="summary-label text-grey">Total Spent</span>
              <span class="summary-value font-weight-bold primary--text">${{ formatAmount(summary.totalSpent) }}</span>
            </div>
            <div class="summary-item d-flex justify-space-between py-2">
              <span class="summary-label text-grey">This Month</span>
              <span class="summary-value font-weight-bold">${{ formatAmount(summary.thisMonth) }}</span>
            </div>
            <div class="summary-item d-flex justify-space-between py-2">
              <span class="summary-label text-grey">Amount Due</span>
              <span class="summary-value font-weight-bold" :class="summary.amountDue > 0 ? 'warning--text' : 'success--text'">
                ${{ formatAmount(summary.amountDue) }}
              </span>
            </div>
            <v-divider class="my-4" />
            <div class="summary-item d-flex justify-space-between py-2">
              <span class="summary-label text-grey">Paid Bookings</span>
              <span class="summary-value font-weight-bold">{{ summary.paidBookings }}</span>
            </div>
            <div class="summary-item d-flex justify-space-between py-2">
              <span class="summary-label text-grey">Pending Payments</span>
              <span class="summary-value font-weight-bold">{{ summary.pendingPayments }}</span>
            </div>
          </v-card-text>
        </v-card>

        <!-- Quick Actions -->
        <v-card elevation="0">
          <v-card-title class="card-header pa-8">
            <span class="section-title primary--text">Quick Actions</span>
          </v-card-title>
          <v-card-text class="pa-8">
            <v-btn
              color="primary"
              block
              size="large"
              prepend-icon="mdi-home"
              @click="$emit('navigate', 'dashboard')"
              class="mb-3"
            >
              Back to Dashboard
            </v-btn>
            <v-btn
              color="success"
              variant="outlined"
              block
              size="large"
              prepend-icon="mdi-calendar-plus"
              @click="$emit('navigate', 'book-form')"
            >
              Book New Service
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

/**
 * ClientPaymentSection - Payment history and summary for clients
 */

const props = defineProps({
  /** Payment history list */
  paymentHistory: {
    type: Array,
    default: () => []
  },
  /** Payment summary data */
  summary: {
    type: Object,
    default: () => ({
      totalSpent: 0,
      thisMonth: 0,
      amountDue: 0,
      paidBookings: 0,
      pendingPayments: 0
    })
  },
  /** Mobile view flag */
  isMobile: {
    type: Boolean,
    default: false
  }
});

defineEmits(['download-receipt', 'navigate']);

// Local state
const currentPage = ref(1);
const itemsPerPage = 10;

const headers = ref([
  { title: 'Booking ID', key: 'id', width: '100px' },
  { title: 'Date', key: 'date', width: '140px' },
  { title: 'Service', key: 'service', width: '150px' },
  { title: 'Amount', key: 'amount', width: '120px', align: 'end' },
  { title: 'Status', key: 'status', width: '120px', align: 'center' },
  { title: 'Receipt', key: 'receipt', width: '100px', align: 'center' }
]);

/**
 * Format amount with commas
 */
const formatAmount = (amount) => {
  return Math.round(amount || 0).toLocaleString();
};

/**
 * Paginated history for mobile
 */
const paginatedHistory = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return props.paymentHistory.slice(start, start + itemsPerPage);
});

/**
 * Total pages for pagination
 */
const totalPages = computed(() => Math.ceil(props.paymentHistory.length / itemsPerPage));
</script>

<style scoped>
.mobile-payment-card {
  border-radius: 12px;
}

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
  * {
    transition: none !important;
  }
}
</style>
