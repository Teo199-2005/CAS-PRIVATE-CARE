<template>
  <div class="recurring-bookings-section">
  <!-- Section Header (only show once if component is mounted multiple times) -->
  <div class="section-header recurring-main-header" v-if="showHeader">
      <div class="header-left">
        <v-icon color="success" size="28" class="mr-3">mdi-autorenew</v-icon>
        <div>
          <h3 class="section-title">Recurring Contracts</h3>
          <p class="section-subtitle">Manage your automatic booking renewals</p>
        </div>
      </div>
      <v-chip v-if="activeRecurringCount > 0" color="success" variant="outlined">
        <v-icon start size="16">mdi-check-circle</v-icon>
        {{ activeRecurringCount }} Active
      </v-chip>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
      <p class="mt-3">Loading recurring bookings...</p>
    </div>

    <!-- No Recurring Bookings -->
    <v-card v-else-if="recurringBookings.length === 0" class="empty-state-card" elevation="0" outlined>
      <v-card-text class="text-center pa-8">
        <v-icon size="80" color="grey-lighten-1" class="mb-4">mdi-calendar-refresh-outline</v-icon>
        <h4 class="text-h6 font-weight-bold mb-2">No Recurring Contracts</h4>
        <p class="text-body-2 text-grey mb-4">
          Enable auto-renewal on your bookings to have them automatically continue
          with the same schedule and payment method.
        </p>
        <v-btn color="primary" variant="outlined" @click="$emit('bookService')">
          <v-icon start>mdi-plus</v-icon>
          Book a Service
        </v-btn>
      </v-card-text>
    </v-card>

    <!-- Recurring Bookings List -->
    <div v-else class="recurring-list">
      <v-card 
        v-for="booking in recurringBookings" 
        :key="booking.id" 
        class="recurring-card mb-4" 
        elevation="2"
        :class="getStatusClass(booking)"
      >
        <v-card-text class="pa-0">
          <!-- Card Header -->
          <div class="card-header" :class="getHeaderClass(booking)">
            <div class="header-info">
              <div class="booking-badge">
                <v-icon size="20" class="mr-2">mdi-receipt</v-icon>
                Booking #{{ booking.id }}
              </div>
              <v-chip 
                :color="getStatusColor(booking)" 
                size="small" 
                variant="flat"
                class="ml-2"
              >
                <v-icon start size="14">{{ getStatusIcon(booking) }}</v-icon>
                {{ formatStatus(booking) }}
              </v-chip>
            </div>
            <div class="header-amount">
              ${{ booking.amount?.toLocaleString() || '0' }}
              <span class="amount-interval">/ {{ booking.duration_days }} days</span>
            </div>
          </div>

          <!-- Card Body -->
          <div class="card-body pa-4">
            <v-row>
              <!-- Service Details -->
              <v-col cols="12" md="6">
                <div class="detail-group">
                  <div class="detail-item">
                    <v-icon size="18" color="primary" class="mr-2">mdi-medical-bag</v-icon>
                    <span class="detail-label">Service:</span>
                    <span class="detail-value">{{ booking.service_type }}</span>
                  </div>
                  <div class="detail-item">
                    <v-icon size="18" color="primary" class="mr-2">mdi-clock-outline</v-icon>
                    <span class="detail-label">Schedule:</span>
                    <span class="detail-value">{{ booking.duty_type }}</span>
                  </div>
                  <div class="detail-item">
                    <v-icon size="18" color="primary" class="mr-2">mdi-calendar-range</v-icon>
                    <span class="detail-label">Duration:</span>
                    <span class="detail-value">{{ booking.duration_days }} days</span>
                  </div>
                </div>
              </v-col>

              <!-- Recurring Info -->
              <v-col cols="12" md="6">
                <div class="detail-group">
                  <div class="detail-item">
                    <v-icon size="18" color="success" class="mr-2">mdi-calendar-check</v-icon>
                    <span class="detail-label">Current Period:</span>
                    <span class="detail-value">{{ formatDate(booking.service_date) }} - {{ formatDate(booking.end_date) }}</span>
                  </div>
                  <div class="detail-item" v-if="booking.recurring_status === 'active'">
                    <v-icon size="18" color="warning" class="mr-2">mdi-calendar-clock</v-icon>
                    <span class="detail-label">Next Charge:</span>
                    <span class="detail-value highlight">{{ getNextChargeDate(booking) }}</span>
                  </div>
                  <div class="detail-item">
                    <v-icon size="18" color="info" class="mr-2">mdi-counter</v-icon>
                    <span class="detail-label">Renewals:</span>
                    <span class="detail-value">{{ booking.recurring_count || 0 }} times</span>
                  </div>
                </div>
              </v-col>
            </v-row>

            <!-- Progress Bar (days remaining) -->
            <div class="progress-section mt-4" v-if="booking.recurring_status === 'active'">
              <div class="progress-header">
                <span class="progress-label">Service Progress</span>
                <span class="progress-value">{{ getDaysRemaining(booking) }} days remaining</span>
              </div>
              <v-progress-linear
                :model-value="getProgressPercent(booking)"
                color="primary"
                height="8"
                rounded
                class="mt-2"
              ></v-progress-linear>
            </div>

            <!-- Actions -->
            <div class="card-actions mt-4">
                        <!-- Pause Auto-Renewal button removed by request -->
              
              <v-btn 
                v-if="booking.recurring_status === 'paused'"
                color="success" 
                variant="outlined" 
                size="small"
                @click="resumeRecurring(booking)"
                :loading="actionLoading === booking.id"
              >
                <v-icon start size="18">mdi-play-circle</v-icon>
                Resume Auto-Renewal
              </v-btn>

              <v-btn 
                v-if="booking.recurring_status !== 'cancelled'"
                color="error" 
                variant="text" 
                size="small"
                @click="showCancelConfirm(booking)"
              >
                <v-icon start size="18">mdi-close-circle</v-icon>
                Cancel Recurring
              </v-btn>

              <v-spacer></v-spacer>

              <v-btn 
                color="primary" 
                variant="text" 
                size="small"
                @click="viewDetails(booking)"
              >
                <v-icon start size="18">mdi-eye</v-icon>
                View History
              </v-btn>
            </div>
          </div>
        </v-card-text>
      </v-card>
    </div>

    <!-- Cancel Confirmation Modal -->
    <v-dialog v-model="cancelDialog" max-width="500" persistent>
      <v-card class="cancel-modal">
        <v-card-title class="cancel-header pa-6">
          <v-icon color="error" size="28" class="mr-3">mdi-alert-circle</v-icon>
          <span>Cancel Recurring Payments?</span>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text class="pa-6">
          <p class="text-body-1 mb-4">
            Are you sure you want to cancel automatic renewals for this booking?
          </p>
          <v-alert type="info" variant="tonal" class="mb-0">
            <template #text>
              Your current service period will complete as scheduled. 
              No new bookings will be created automatically after this period ends.
            </template>
          </v-alert>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="pa-6">
          <v-spacer></v-spacer>
          <v-btn variant="outlined" @click="cancelDialog = false">
            Keep Active
          </v-btn>
          <v-btn 
            color="error" 
            variant="flat" 
            @click="confirmCancel"
            :loading="cancelLoading"
          >
            <v-icon start>mdi-close-circle</v-icon>
            Cancel Recurring
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- History Modal -->
    <v-dialog v-model="historyDialog" max-width="700">
      <v-card class="history-modal">
        <v-card-title class="history-header pa-6">
          <v-icon color="primary" size="28" class="mr-3">mdi-history</v-icon>
          <span>Booking History - Chain #{{ selectedBooking?.id }}</span>
          <v-spacer></v-spacer>
          <v-btn icon="mdi-close" variant="text" @click="historyDialog = false"></v-btn>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text class="pa-6">
          <div v-if="loadingHistory" class="text-center py-8">
            <v-progress-circular indeterminate color="primary"></v-progress-circular>
          </div>
          <div v-else-if="bookingChain.length > 0">
            <div class="history-summary mb-6">
              <v-row>
                <v-col cols="4">
                  <div class="summary-item">
                    <div class="summary-value">${{ totalPaid?.toLocaleString() || '0' }}</div>
                    <div class="summary-label">Total Paid</div>
                  </div>
                </v-col>
                <v-col cols="4">
                  <div class="summary-item">
                    <div class="summary-value">{{ totalRenewals }}</div>
                    <div class="summary-label">Renewals</div>
                  </div>
                </v-col>
                <v-col cols="4">
                  <div class="summary-item">
                    <div class="summary-value">{{ bookingChain.length }}</div>
                    <div class="summary-label">Total Bookings</div>
                  </div>
                </v-col>
              </v-row>
            </div>
            
            <v-timeline density="compact" side="end">
              <v-timeline-item
                v-for="(item, index) in bookingChain"
                :key="item.id"
                :dot-color="index === 0 ? 'primary' : 'success'"
                size="small"
              >
                <div class="timeline-item">
                  <div class="timeline-header">
                    <span class="timeline-title">Booking #{{ item.id }}</span>
                    <v-chip size="x-small" :color="item.payment_status === 'paid' ? 'success' : 'warning'">
                      {{ item.payment_status }}
                    </v-chip>
                  </div>
                  <div class="timeline-content">
                    {{ formatDate(item.service_date) }} - {{ formatDate(item.end_date) }}
                  </div>
                  <div class="timeline-amount">
                    ${{ item.amount?.toLocaleString() || '0' }}
                  </div>
                </div>
              </v-timeline-item>
            </v-timeline>
          </div>
          <div v-else class="text-center py-8 text-grey">
            No booking history found
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Notification Toast -->
    <notification-toast
      v-model="notification.show"
      :type="notification.type"
      :title="notification.title"
      :message="notification.message"
      :timeout="notification.timeout"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import NotificationToast from './shared/NotificationToast.vue';
import { useNotification } from '../composables/useNotification.js';

const { notification, success, error } = useNotification();

const loading = ref(false);
const recurringBookings = ref([]);
const actionLoading = ref(null);
const cancelDialog = ref(false);
const cancelLoading = ref(false);
const selectedBooking = ref(null);
const historyDialog = ref(false);
const loadingHistory = ref(false);
const bookingChain = ref([]);
const totalPaid = ref(0);
const totalRenewals = ref(0);

const activeRecurringCount = computed(() => {
  return recurringBookings.value.filter(b => b.recurring_status === 'active').length;
});

// Show header only if no other instance has already rendered it (DOM check)
const props = defineProps({
  showInternalHeader: {
    type: Boolean,
    default: true
  }
});

const showHeader = computed(() => props.showInternalHeader);

const loadRecurringBookings = async () => {
  loading.value = true;
  try {
  const response = await axios.get('/api/client/recurring');
    if (response.data && response.data.success) {
      recurringBookings.value = response.data.recurring_bookings || [];
    } else {
      // API returned but with success: false - just show empty state
      recurringBookings.value = [];
    }
  } catch (e) {
    // Log detailed info to help debug missing routes / auth redirects
    console.error('Error loading recurring bookings:', e);
    try {
      const status = e.response?.status;
      const url = e.config?.url;
      console.error('Recurring bookings request failed', { status, url });
      if (status === 401) {
        // Inform the user to login (visible feedback)
        error('Please log in to view your recurring contracts', 'Unauthorized');
      }
    } catch (logErr) {
      console.error('Failed to read error response', logErr);
    }
    // Don't show error toast for other issues - show empty state as before
    recurringBookings.value = [];
  } finally {
    loading.value = false;
  }
};

const pauseRecurring = async (booking) => {
  actionLoading.value = booking.id;
  try {
  await axios.post(`/api/client/recurring/${booking.id}/pause`);
    await loadRecurringBookings();
    success('Auto-renewal paused successfully', 'Paused');
  } catch (e) {
    error('Failed to pause auto-renewal', 'Error');
  } finally {
    actionLoading.value = null;
  }
};

const resumeRecurring = async (booking) => {
  actionLoading.value = booking.id;
  try {
  await axios.post(`/api/client/recurring/${booking.id}/resume`);
    await loadRecurringBookings();
    success('Auto-renewal resumed successfully', 'Resumed');
  } catch (e) {
    error('Failed to resume auto-renewal', 'Error');
  } finally {
    actionLoading.value = null;
  }
};

const showCancelConfirm = (booking) => {
  selectedBooking.value = booking;
  cancelDialog.value = true;
};

const confirmCancel = async () => {
  if (!selectedBooking.value) return;
  
  cancelLoading.value = true;
  try {
  await axios.post(`/api/client/recurring/${selectedBooking.value.id}/cancel`);
    cancelDialog.value = false;
    await loadRecurringBookings();
    success('Recurring payments cancelled', 'Cancelled');
  } catch (e) {
    error('Failed to cancel recurring payments', 'Error');
  } finally {
    cancelLoading.value = false;
  }
};

const viewDetails = async (booking) => {
  selectedBooking.value = booking;
  historyDialog.value = true;
  loadingHistory.value = true;
  
  try {
  const response = await axios.get(`/api/client/recurring/${booking.id}`);
    bookingChain.value = response.data.chain || [];
    totalPaid.value = response.data.total_paid || 0;
    totalRenewals.value = response.data.total_renewals || 0;
  } catch (e) {
    console.error('Error loading booking history:', e);
  } finally {
    loadingHistory.value = false;
  }
};

const getStatusClass = (booking) => {
  const classes = {
    'active': 'status-active',
    'paused': 'status-paused',
    'cancelled': 'status-cancelled',
    'failed': 'status-failed'
  };
  return classes[booking.recurring_status] || 'status-active';
};

const getHeaderClass = (booking) => {
  const classes = {
    'active': 'header-active',
    'paused': 'header-paused',
    'cancelled': 'header-cancelled',
    'failed': 'header-failed'
  };
  return classes[booking.recurring_status] || 'header-active';
};

const getStatusColor = (booking) => {
  const colors = {
    'active': 'success',
    'paused': 'warning',
    'cancelled': 'grey',
    'failed': 'error'
  };
  return colors[booking.recurring_status] || 'success';
};

const getStatusIcon = (booking) => {
  const icons = {
    'active': 'mdi-check-circle',
    'paused': 'mdi-pause-circle',
    'cancelled': 'mdi-close-circle',
    'failed': 'mdi-alert-circle'
  };
  return icons[booking.recurring_status] || 'mdi-autorenew';
};

const formatStatus = (booking) => {
  const statuses = {
    'active': 'Auto-Renewing',
    'paused': 'Paused',
    'cancelled': 'Cancelled',
    'failed': 'Payment Failed'
  };
  return statuses[booking.recurring_status] || 'Active';
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const getNextChargeDate = (booking) => {
  if (!booking.end_date) return 'N/A';
  const endDate = new Date(booking.end_date);
  endDate.setDate(endDate.getDate() + 1);
  return formatDate(endDate.toISOString().split('T')[0]);
};

const getDaysRemaining = (booking) => {
  if (!booking.end_date) return 0;
  const endDate = new Date(booking.end_date);
  const today = new Date();
  const diffTime = endDate.getTime() - today.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return Math.max(0, diffDays);
};

const getProgressPercent = (booking) => {
  if (!booking.service_date || !booking.duration_days) return 0;
  const startDate = new Date(booking.service_date);
  const today = new Date();
  const daysElapsed = Math.ceil((today.getTime() - startDate.getTime()) / (1000 * 60 * 60 * 24));
  const percent = (daysElapsed / booking.duration_days) * 100;
  return Math.min(100, Math.max(0, percent));
};

onMounted(() => {
  loadRecurringBookings();
});

// Expose refresh method
defineExpose({ loadRecurringBookings });
</script>

<style scoped>
.recurring-bookings-section {
  padding: 16px 0;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-left {
  display: flex;
  align-items: center;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0;
}

.section-subtitle {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
}

.empty-state-card {
  border: 2px dashed #e0e0e0 !important;
  background: #fafafa !important;
}

.recurring-card {
  border-radius: 16px !important;
  overflow: hidden;
  transition: all 0.3s ease;
}

.recurring-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
}

.card-header {
  padding: 16px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-active {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.header-paused {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
}

.header-cancelled {
  background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
  color: white;
}

.header-failed {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.header-info {
  display: flex;
  align-items: center;
}

.booking-badge {
  font-weight: 600;
  display: flex;
  align-items: center;
}

.header-amount {
  font-size: 1.5rem;
  font-weight: 700;
}

.amount-interval {
  font-size: 0.875rem;
  opacity: 0.9;
  font-weight: 400;
}

.card-body {
  background: white;
}

.detail-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.detail-item {
  display: flex;
  align-items: center;
  font-size: 0.9rem;
}

.detail-label {
  color: #6b7280;
  margin-right: 8px;
}

.detail-value {
  color: #1a1a1a;
  font-weight: 500;
}

.detail-value.highlight {
  color: #f59e0b;
  font-weight: 600;
}

.progress-section {
  padding-top: 16px;
  border-top: 1px solid #e0e0e0;
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.progress-label {
  font-size: 0.875rem;
  color: #6b7280;
}

.progress-value {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1a1a1a;
}

.card-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid #e0e0e0;
}

/* Modal Styles */
.cancel-header {
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  color: #dc2626;
  font-weight: 600;
}

.history-header {
  background: #f8fafc;
  font-weight: 600;
}

.history-summary {
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border-radius: 12px;
  padding: 20px;
}

.summary-item {
  text-align: center;
}

.summary-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e40af;
}

.summary-label {
  font-size: 0.875rem;
  color: #6b7280;
}

.timeline-item {
  background: #f8fafc;
  padding: 12px 16px;
  border-radius: 8px;
}

.timeline-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.timeline-title {
  font-weight: 600;
  color: #1a1a1a;
}

.timeline-content {
  font-size: 0.875rem;
  color: #6b7280;
}

.timeline-amount {
  font-size: 1rem;
  font-weight: 600;
  color: #10b981;
  margin-top: 4px;
}

/* Status Border Colors */
.status-active {
  border-left: 4px solid #10b981;
}

.status-paused {
  border-left: 4px solid #f59e0b;
}

.status-cancelled {
  border-left: 4px solid #6b7280;
}

.status-failed {
  border-left: 4px solid #ef4444;
}
</style>
