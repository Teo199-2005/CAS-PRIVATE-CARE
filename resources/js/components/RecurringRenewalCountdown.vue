<template>
  <div v-if="upcomingRenewals.length > 0" class="renewal-countdown-banner">
    <v-alert
      v-for="renewal in upcomingRenewals"
      :key="renewal.booking_id"
      :color="getAlertColor(renewal.days_remaining)"
      :icon="getAlertIcon(renewal.days_remaining)"
      prominent
      border="start"
      class="mb-4"
      closable
      @click:close="dismissRenewal(renewal.booking_id)"
    >
      <template v-slot:prepend>
        <v-icon size="40" class="pulse-icon"></v-icon>
      </template>
      
      <v-alert-title class="text-h6 font-weight-bold mb-2">
        <span v-if="renewal.days_remaining === 1">
          Your Contract Renews Tomorrow!
        </span>
        <span v-else-if="renewal.days_remaining === 0">
          Your Contract Renews Today!
        </span>
        <span v-else>
          Contract Renewal in {{ renewal.days_remaining }} Days
        </span>
      </v-alert-title>

      <div class="renewal-details">
        <p class="text-body-1 mb-3">
          <span v-if="renewal.days_remaining === 0">
            Your <strong>{{ renewal.service_type }}</strong> contract renews today!
          </span>
          <span v-else-if="renewal.days_remaining === 1">
            Your <strong>{{ renewal.service_type }}</strong> contract will automatically renew tomorrow on <strong>{{ renewal.renewal_date }}</strong>.
          </span>
          <span v-else>
            Your <strong>{{ renewal.service_type }}</strong> contract will automatically renew on <strong>{{ renewal.renewal_date }}</strong>.
          </span>
        </p>

        <div class="renewal-info-grid">
          <div class="info-item">
            <v-icon size="20" class="mr-2">mdi-receipt</v-icon>
            <span class="text-caption">Booking #{{ renewal.booking_id }}</span>
          </div>
          <div class="info-item">
            <v-icon size="20" class="mr-2">mdi-calendar-range</v-icon>
            <span class="text-caption">{{ renewal.duration_days }} days</span>
          </div>
          <div class="info-item">
            <v-icon size="20" class="mr-2">mdi-clock-outline</v-icon>
            <span class="text-caption">{{ renewal.hours_per_day }} hrs/day</span>
          </div>
          <div class="info-item amount-highlight">
            <v-icon size="20" class="mr-2">mdi-credit-card</v-icon>
            <span class="text-body-1 font-weight-bold">${{ renewal.amount.toLocaleString() }}</span>
          </div>
        </div>

        <v-divider class="my-3"></v-divider>

        <div class="d-flex align-center justify-space-between flex-wrap">
          <div class="renewal-message">
            <v-icon size="18" color="white" class="mr-2">mdi-information</v-icon>
            <span class="text-caption">
              Your saved card will be charged automatically. The same caregiver and schedule will continue.
            </span>
          </div>
          <div class="renewal-actions mt-2 mt-sm-0">
            <v-btn
              color="white"
              variant="outlined"
              size="small"
              @click="goToPaymentSettings"
              class="mr-2"
            >
              <v-icon start size="18">mdi-cog</v-icon>
              Manage
            </v-btn>
            <v-btn
              color="white"
              variant="text"
              size="small"
              @click="viewDetails(renewal)"
            >
              <v-icon start size="18">mdi-information-outline</v-icon>
              Details
            </v-btn>
          </div>
        </div>
      </div>
    </v-alert>

    <!-- Details Dialog -->
    <v-dialog v-model="detailsDialog" max-width="600">
      <v-card v-if="selectedRenewal">
        <v-card-title class="text-h5 font-weight-bold bg-primary text-white pa-4">
          <v-icon start color="white">mdi-calendar-refresh</v-icon>
          Contract Renewal Details
        </v-card-title>

        <v-card-text class="pa-6">
          <div class="countdown-display text-center mb-4">
            <div class="countdown-number">{{ selectedRenewal.days_remaining }}</div>
            <div class="countdown-label">Day{{ selectedRenewal.days_remaining !== 1 ? 's' : '' }} Until Renewal</div>
          </div>

          <v-divider class="my-4"></v-divider>

          <div class="detail-row">
            <span class="detail-label">Renewal Date:</span>
            <span class="detail-value">{{ selectedRenewal.renewal_date }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Booking ID:</span>
            <span class="detail-value">#{{ selectedRenewal.booking_id }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Service Type:</span>
            <span class="detail-value">{{ selectedRenewal.service_type }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Duration:</span>
            <span class="detail-value">{{ selectedRenewal.duration_days }} days</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Hours per Day:</span>
            <span class="detail-value">{{ selectedRenewal.hours_per_day }} hours</span>
          </div>
          <div class="detail-row total-row">
            <span class="detail-label">Amount to be Charged:</span>
            <span class="detail-value text-h6 primary--text">${{ selectedRenewal.amount.toLocaleString() }}</span>
          </div>

          <v-alert color="info" variant="tonal" class="mt-4">
            <v-icon start>mdi-information</v-icon>
            Your saved payment method will be automatically charged on {{ selectedRenewal.renewal_date }}. 
            You'll receive a confirmation email after the payment is processed.
          </v-alert>
        </v-card-text>

        <v-card-actions class="pa-4">
          <v-btn color="primary" variant="outlined" @click="goToPaymentSettings">
            Manage Auto-Renewal
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn color="grey" variant="text" @click="detailsDialog = false">
            Close
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const upcomingRenewals = ref([]);
const detailsDialog = ref(false);
const selectedRenewal = ref(null);
const dismissedRenewals = ref(new Set());

const emit = defineEmits(['navigateToSection']);

const loadUpcomingRenewals = async () => {
  try {
  const response = await axios.get('/api/client/recurring/upcoming-renewals');
    if (response.data.success) {
      upcomingRenewals.value = response.data.renewals
        .filter(r => !dismissedRenewals.value.has(r.booking_id))
        .sort((a, b) => a.days_remaining - b.days_remaining);
    }
  } catch (e) {
    console.error('Error loading upcoming renewals:', e);
    try {
      const status = e.response?.status;
      const url = e.config?.url;
      console.error('Upcoming renewals request failed', { status, url });
      if (status === 401) {
        // Optionally notify user to login
        // (Don't spam; this banner is informational)
        console.warn('User appears not authenticated. Renewals will not display until login.');
      }
    } catch (logErr) {
      console.error('Failed to read error response', logErr);
    }
  }
};

const getAlertColor = (daysRemaining) => {
  if (daysRemaining <= 1) return 'error';
  if (daysRemaining <= 2) return 'warning';
  return 'info';
};

const getAlertIcon = (daysRemaining) => {
  if (daysRemaining <= 1) return 'mdi-alert-circle';
  if (daysRemaining <= 2) return 'mdi-clock-alert';
  return 'mdi-calendar-clock';
};

const dismissRenewal = (bookingId) => {
  dismissedRenewals.value.add(bookingId);
  upcomingRenewals.value = upcomingRenewals.value.filter(r => r.booking_id !== bookingId);
  
  // Store in session storage
  const dismissed = Array.from(dismissedRenewals.value);
  sessionStorage.setItem('dismissedRenewals', JSON.stringify(dismissed));
};

const goToPaymentSettings = () => {
  detailsDialog.value = false;
  emit('navigateToSection', 'payment');
};

const viewDetails = (renewal) => {
  selectedRenewal.value = renewal;
  detailsDialog.value = true;
};

onMounted(() => {
  // Load dismissed renewals from session storage
  const dismissed = sessionStorage.getItem('dismissedRenewals');
  if (dismissed) {
    dismissedRenewals.value = new Set(JSON.parse(dismissed));
  }
  
  loadUpcomingRenewals();
  
  // Refresh every 5 minutes
  setInterval(loadUpcomingRenewals, 5 * 60 * 1000);
});
</script>

<style scoped>
.renewal-countdown-banner {
  margin-bottom: 20px;
}

/* Force all text in the renewal banner to be white */
.renewal-countdown-banner :deep(.v-alert) {
  color: white !important;
}

.renewal-countdown-banner :deep(.v-alert-title) {
  color: white !important;
}

.renewal-countdown-banner :deep(.v-alert-title span) {
  color: white !important;
}

.renewal-countdown-banner :deep(.text-caption) {
  color: white !important;
}

.renewal-countdown-banner :deep(.text-body-1) {
  color: white !important;
}

.renewal-countdown-banner :deep(p) {
  color: white !important;
}

.renewal-countdown-banner :deep(strong) {
  color: white !important;
}

.renewal-details {
  margin-top: 8px;
}

.renewal-info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 12px;
  margin-bottom: 12px;
}

.info-item {
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.1);
  padding: 8px 12px;
  border-radius: 6px;
  color: white !important;
}

.info-item span {
  color: white !important;
}

.info-item.amount-highlight {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.renewal-message {
  display: flex;
  align-items: center;
  flex: 1;
  color: white !important;
}

.renewal-message span {
  color: white !important;
}

.renewal-details {
  margin-top: 8px;
  color: white !important;
}

.renewal-details p,
.renewal-details span {
  color: white !important;
}

.renewal-actions {
  display: flex;
  gap: 8px;
}

.countdown-display {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 30px;
  border-radius: 12px;
}

.countdown-number {
  font-size: 64px;
  font-weight: bold;
  line-height: 1;
}

.countdown-label {
  font-size: 18px;
  margin-top: 10px;
  opacity: 0.9;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #e0e0e0;
}

.detail-row.total-row {
  border-bottom: none;
  border-top: 2px solid #667eea;
  margin-top: 8px;
  padding-top: 16px;
}

.detail-label {
  font-weight: 600;
  color: #666;
}

.detail-value {
  font-weight: 500;
  color: #333;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.1);
    opacity: 0.8;
  }
}

.pulse-icon {
  animation: pulse 2s infinite;
}

@media (max-width: 600px) {
  .renewal-info-grid {
    grid-template-columns: 1fr 1fr;
  }
  
  .renewal-actions {
    width: 100%;
    justify-content: flex-end;
  }
}
</style>
