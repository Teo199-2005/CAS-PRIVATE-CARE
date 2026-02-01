<template>
  <div class="client-my-bookings">
    <div class="bookings-header mb-6">
      <h1 class="bookings-title">My Bookings</h1>
      <p class="bookings-subtitle">Review and manage your care service requests</p>
    </div>

    <v-tabs v-model="bookingTab" color="primary" class="mb-6 booking-tabs" bg-color="transparent" show-arrows density="comfortable">
      <v-tab value="pending" class="booking-tab">
        <v-icon start size="small">mdi-clock-outline</v-icon>
        <span class="tab-text">Pending Review</span>
        <v-chip v-if="pendingBookings.length > 0" size="x-small" color="warning" class="ml-2">
          {{ pendingBookings.length }}
        </v-chip>
      </v-tab>
      <v-tab value="approved" class="booking-tab">
        <v-icon start size="small">mdi-check-circle</v-icon>
        <span class="tab-text">Approved</span>
        <v-chip v-if="confirmedBookings.length > 0" size="x-small" color="success" class="ml-2">
          {{ confirmedBookings.length }}
        </v-chip>
      </v-tab>
      <v-tab value="completed" class="booking-tab">
        <v-icon start size="small">mdi-calendar-check</v-icon>
        <span class="tab-text">Completed</span>
      </v-tab>
    </v-tabs>

    <v-window v-model="bookingTab">
      <!-- Pending Bookings Tab -->
      <v-window-item value="pending">
        <v-row>
          <v-col v-for="booking in pendingBookings" :key="booking.id" cols="12">
            <v-card class="booking-request-card" elevation="2">
              <v-card-text class="pa-6">
                <div class="d-flex justify-between align-center mb-4 flex-wrap gap-2">
                  <div>
                    <h3 class="booking-service-title">{{ booking.service }}</h3>
                    <p class="booking-id text-grey">Request ID: #{{ booking.id }}</p>
                  </div>
                  <v-chip color="warning" size="large">
                    <v-icon start>mdi-clock-outline</v-icon>
                    Pending Review
                  </v-chip>
                </div>
                
                <v-row>
                  <v-col cols="12" md="5">
                    <div class="booking-details">
                      <div class="detail-row">
                        <v-icon size="18" color="primary">mdi-calendar</v-icon>
                        <span class="detail-text">{{ booking.date }}</span>
                      </div>
                      <div class="detail-row" v-if="booking.time && booking.time !== 'N/A'">
                        <v-icon size="18" color="primary">mdi-clock-start</v-icon>
                        <span class="detail-text">Starts at {{ booking.time }}</span>
                      </div>
                      <div class="detail-row">
                        <v-icon size="18" color="primary">mdi-map-marker</v-icon>
                        <span class="detail-text">{{ booking.location }}</span>
                      </div>
                      <div class="detail-row">
                        <v-icon size="18" color="primary">mdi-account-clock</v-icon>
                        <span class="detail-text">{{ booking.dutyType }}</span>
                      </div>
                      <div class="detail-row mt-3">
                        <v-chip color="warning" size="small" variant="flat">
                          <span v-if="getOriginalPrice(booking)" class="original-price">${{ getOriginalPrice(booking) }}</span>
                          ${{ getBookingPrice(booking) }}
                        </v-chip>
                      </div>
                    </div>
                  </v-col>
                  <v-col cols="12" md="7">
                    <div class="booking-actions">
                      <v-btn color="info" variant="outlined" size="small" class="mr-2 mb-2" @click="$emit('view-details', booking)">
                        <v-icon start>mdi-eye</v-icon>
                        View Details
                      </v-btn>
                      <v-btn color="primary" variant="outlined" size="small" class="mr-2 mb-2" @click="$emit('edit', booking.id)">
                        <v-icon start>mdi-pencil</v-icon>
                        Edit Request
                      </v-btn>
                      <v-btn color="error" variant="outlined" size="small" class="mb-2" @click="$emit('cancel', booking.id)">
                        <v-icon start>mdi-cancel</v-icon>
                        Cancel
                      </v-btn>
                    </div>
                    
                    <v-alert type="info" density="compact" class="mt-3" variant="tonal">
                      <div class="d-flex align-center">
                        <v-icon start>mdi-information</v-icon>
                        <span class="text-caption">Your booking is under review by our admin team. We'll notify you once it's approved.</span>
                      </div>
                    </v-alert>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
        <v-alert v-if="pendingBookings.length === 0 && !loading" type="info" class="mt-4">
          <v-icon start>mdi-information</v-icon>
          No pending requests. <a href="#" @click.prevent="$emit('new-booking')" class="text-primary">Submit a new request</a>
        </v-alert>
        <div v-if="loading" class="text-center pa-8">
          <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
          <p class="mt-4 text-grey">Loading bookings...</p>
        </div>
      </v-window-item>

      <!-- Approved Bookings Tab -->
      <v-window-item value="approved">
        <v-row>
          <v-col v-for="booking in confirmedBookings" :key="booking.id" cols="12">
            <v-card class="booking-request-card" elevation="2">
              <v-card-text class="pa-6">
                <div class="d-flex justify-between align-center mb-4 flex-wrap gap-2">
                  <div>
                    <h3 class="booking-service-title">{{ booking.service }}</h3>
                    <p class="booking-id text-grey">Booking ID: #{{ booking.id }}</p>
                  </div>
                  <v-chip color="success" size="large">
                    <v-icon start>mdi-check</v-icon>
                    Approved
                  </v-chip>
                </div>
                
                <v-row>
                  <v-col cols="12" md="4">
                    <div class="booking-details">
                      <div class="detail-row">
                        <v-icon size="18" color="primary">mdi-account</v-icon>
                        <span class="detail-text">{{ booking.caregiver }}</span>
                      </div>
                      <div class="detail-row">
                        <v-icon size="18" color="primary">mdi-calendar</v-icon>
                        <span class="detail-text">{{ booking.date }}</span>
                      </div>
                      <div class="detail-row" v-if="booking.time && booking.time !== 'N/A'">
                        <v-icon size="18" color="primary">mdi-clock-start</v-icon>
                        <span class="detail-text">Starts at {{ booking.time }}</span>
                      </div>
                      <div class="detail-row">
                        <v-icon size="18" color="primary">mdi-map-marker</v-icon>
                        <span class="detail-text">{{ booking.location }}</span>
                      </div>
                      <div class="detail-row">
                        <v-icon size="18" color="primary">mdi-clock-outline</v-icon>
                        <span class="detail-text">{{ booking.durationDays || 15 }} days</span>
                      </div>
                    </div>
                  </v-col>
                  
                  <!-- Pricing Summary Card -->
                  <v-col cols="12" md="3">
                    <div class="pricing-summary-card pa-3 rounded-lg">
                      <div class="text-caption mb-2 opacity-90">Total Amount</div>
                      <div class="text-h5 font-weight-bold mb-2">${{ getBookingPrice(booking) }}</div>
                      <v-divider class="my-2" style="border-color: rgba(255,255,255,0.3);"></v-divider>
                      <div class="d-flex justify-space-between text-caption opacity-90">
                        <span>Rate/Hour:</span>
                        <span>${{ booking.hourlyRate || 45 }}</span>
                      </div>
                      <div class="d-flex justify-space-between text-caption opacity-90">
                        <span>Hours/Day:</span>
                        <span>{{ extractHoursFromDuty(booking.dutyType || booking.duty_type) }}</span>
                      </div>
                      <div v-if="booking.hasDiscount" class="mt-2">
                        <v-chip color="success" size="x-small" class="mt-1">
                          <v-icon start size="x-small">mdi-tag-check</v-icon>
                          Discount Applied
                        </v-chip>
                      </div>
                    </div>
                  </v-col>
                  
                  <!-- Assignment Progress -->
                  <v-col cols="12" md="2">
                    <div class="assignment-progress-card pa-3 rounded-lg">
                      <div class="d-flex align-center mb-2">
                        <v-icon size="18" color="info" class="mr-2">mdi-account-group</v-icon>
                        <span class="text-caption font-weight-medium">Assignment</span>
                      </div>
                      <div class="d-flex align-center justify-between mb-2">
                        <span class="text-body-2">{{ booking.assignedCount || 0 }} / {{ booking.requiredCount || 1 }}</span>
                        <v-chip 
                          :color="(booking.assignedCount || 0) >= (booking.requiredCount || 1) ? 'success' : 'warning'" 
                          size="x-small"
                          variant="flat"
                        >
                          {{ (booking.assignedCount || 0) >= (booking.requiredCount || 1) ? 'Complete' : 'In Progress' }}
                        </v-chip>
                      </div>
                      <v-progress-linear
                        :model-value="((booking.assignedCount || 0) / (booking.requiredCount || 1)) * 100"
                        :color="(booking.assignedCount || 0) >= (booking.requiredCount || 1) ? 'success' : 'info'"
                        height="8"
                        rounded
                      ></v-progress-linear>
                    </div>
                  </v-col>
                  
                  <!-- Actions -->
                  <v-col cols="12" md="3">
                    <div class="d-flex flex-column gap-2">
                      <v-btn color="info" variant="outlined" size="small" @click="$emit('view-details', booking)" block>
                        <v-icon start>mdi-eye</v-icon>
                        View Details
                      </v-btn>
                      <v-btn color="primary" variant="outlined" size="small" @click="$emit('contact-caregiver', booking)" block>
                        <v-icon start>mdi-phone</v-icon>
                        Contact Caregiver
                      </v-btn>
                      <v-btn color="success" variant="outlined" size="small" @click="$emit('contact-admin')" block>
                        <v-icon start>mdi-account-tie</v-icon>
                        Contact Admin
                      </v-btn>
                    </div>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
        <v-alert v-if="confirmedBookings.length === 0 && !loading" type="info" class="mt-4">
          <v-icon start>mdi-information</v-icon>
          No approved bookings
        </v-alert>
        <div v-if="loading" class="text-center pa-8">
          <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
          <p class="mt-4 text-grey">Loading bookings...</p>
        </div>
      </v-window-item>

      <!-- Completed Bookings Tab -->
      <v-window-item value="completed">
        <v-row>
          <v-col v-for="booking in completedBookings" :key="booking.id" cols="12">
            <v-card class="booking-request-card" elevation="2">
              <v-card-text class="pa-6">
                <div class="d-flex justify-between align-center mb-4 flex-wrap gap-2">
                  <div>
                    <h3 class="booking-service-title">{{ booking.service }}</h3>
                    <p class="booking-id text-grey">Completed on {{ booking.date }}</p>
                  </div>
                  <v-chip color="success" size="large">
                    <v-icon start>mdi-check-circle</v-icon>
                    Completed
                  </v-chip>
                </div>
                
                <div class="booking-details mb-4">
                  <div class="detail-row">
                    <v-icon size="18" color="primary">mdi-account</v-icon>
                    <span class="detail-text">{{ booking.caregiver }}</span>
                  </div>
                </div>
                
                <div class="d-flex gap-2 flex-wrap">
                  <v-btn color="info" variant="outlined" size="small" @click="$emit('view-details', booking)">
                    <v-icon start>mdi-eye</v-icon>
                    View Details
                  </v-btn>
                  <v-btn color="success" variant="flat" size="small" @click="$emit('download-receipt', booking.id)">
                    <v-icon start>mdi-receipt</v-icon>
                    Download Receipt
                  </v-btn>
                  <v-btn color="primary" variant="outlined" size="small" @click="$emit('rate', booking.id)">
                    <v-icon start>mdi-star</v-icon>
                    Rate Service
                  </v-btn>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
        <v-alert v-if="completedBookings.length === 0 && !loading" type="info" class="mt-4">
          <v-icon start>mdi-information</v-icon>
          No completed bookings
        </v-alert>
        <div v-if="loading" class="text-center pa-8">
          <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
          <p class="mt-4 text-grey">Loading bookings...</p>
        </div>
      </v-window-item>
    </v-window>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  pendingBookings: {
    type: Array,
    default: () => []
  },
  confirmedBookings: {
    type: Array,
    default: () => []
  },
  completedBookings: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits([
  'view-details',
  'edit',
  'cancel',
  'new-booking',
  'contact-caregiver',
  'contact-admin',
  'download-receipt',
  'rate'
]);

const bookingTab = ref('pending');

// Helper methods
const getBookingPrice = (booking) => {
  if (booking.totalPrice) return booking.totalPrice;
  if (booking.total_price) return booking.total_price;
  
  const hourlyRate = booking.hourlyRate || booking.hourly_rate || 45;
  const hours = extractHoursFromDuty(booking.dutyType || booking.duty_type);
  const days = booking.durationDays || booking.duration_days || 15;
  
  return (hourlyRate * hours * days).toFixed(2);
};

const getOriginalPrice = (booking) => {
  if (booking.hasDiscount && booking.originalPrice) {
    return booking.originalPrice;
  }
  return null;
};

const extractHoursFromDuty = (dutyType) => {
  if (!dutyType) return 8;
  const match = dutyType.match(/(\d+)/);
  return match ? parseInt(match[1]) : 8;
};
</script>

<style scoped>
.client-my-bookings {
  max-width: 1200px;
  margin: 0 auto;
}

.bookings-header {
  text-align: center;
}

.bookings-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.bookings-subtitle {
  font-size: 1rem;
  color: #64748b;
  margin: 0;
}

.booking-tabs {
  border-radius: 12px;
  background: #f8fafc;
  padding: 0.5rem;
}

.booking-tab {
  text-transform: none;
  font-weight: 500;
}

.booking-request-card {
  border-radius: 16px;
  margin-bottom: 1rem;
  transition: box-shadow 0.2s ease;
}

.booking-request-card:hover {
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.booking-service-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.booking-id {
  font-size: 0.875rem;
  margin: 0.25rem 0 0 0;
}

.booking-details {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.detail-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.detail-text {
  color: #475569;
  font-size: 0.9375rem;
}

.booking-actions {
  display: flex;
  flex-wrap: wrap;
}

.pricing-summary-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.assignment-progress-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}

.original-price {
  text-decoration: line-through;
  opacity: 0.7;
  margin-right: 0.5rem;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .bookings-title {
    font-size: 1.5rem;
  }

  .booking-request-card {
    margin-bottom: 0.75rem;
  }

  .booking-request-card .v-card-text {
    padding: 1rem !important;
  }

  .pricing-summary-card,
  .assignment-progress-card {
    margin-top: 1rem;
  }

  .booking-actions {
    margin-top: 1rem;
  }

  .booking-actions .v-btn {
    flex: 1 1 auto;
    min-width: 120px;
  }
}

@media (max-width: 480px) {
  .bookings-title {
    font-size: 1.25rem;
  }

  .booking-tabs {
    padding: 0.25rem;
  }

  .tab-text {
    font-size: 0.75rem;
  }
}
</style>
