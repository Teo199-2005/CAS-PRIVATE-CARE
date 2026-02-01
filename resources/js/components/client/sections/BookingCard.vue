<template>
  <v-card class="booking-card" elevation="2">
    <v-card-text class="pa-6">
      <!-- Header -->
      <div class="d-flex justify-space-between align-center mb-4 flex-wrap gap-2">
        <div class="booking-info">
          <h3 class="booking-service-title">{{ booking.service || booking.serviceType }}</h3>
          <p class="booking-id text-grey mb-0">
            {{ status === 'completed' ? 'Completed on' : (status === 'pending' ? 'Request ID:' : 'Booking ID:') }} 
            {{ status === 'completed' ? booking.date : `#${booking.id}` }}
          </p>
        </div>
        <v-chip :color="statusColor" size="large">
          <v-icon start>{{ statusIcon }}</v-icon>
          {{ statusLabel }}
        </v-chip>
      </div>
      
      <v-row>
        <!-- Booking Details -->
        <v-col cols="12" :md="status === 'approved' ? 4 : 5">
          <div class="booking-details">
            <div v-if="status !== 'pending'" class="detail-row">
              <v-icon size="18" color="primary">mdi-account</v-icon>
              <span class="detail-text">{{ booking.caregiver || 'Pending Assignment' }}</span>
            </div>
            <div class="detail-row">
              <v-icon size="18" color="primary">mdi-calendar</v-icon>
              <span class="detail-text">{{ booking.date }}</span>
            </div>
            <div v-if="booking.time && booking.time !== 'N/A'" class="detail-row">
              <v-icon size="18" color="primary">mdi-clock-start</v-icon>
              <span class="detail-text">Starts at {{ booking.time }}</span>
            </div>
            <div class="detail-row">
              <v-icon size="18" color="primary">mdi-map-marker</v-icon>
              <span class="detail-text">{{ booking.location }}</span>
            </div>
            <div class="detail-row">
              <v-icon size="18" color="primary">mdi-account-clock</v-icon>
              <span class="detail-text">{{ booking.dutyType || booking.duty_type }}</span>
            </div>
            <div class="detail-row mt-3">
              <v-chip :color="status === 'pending' ? 'warning' : 'primary'" size="small" variant="flat">
                <span v-if="originalPrice" class="original-price-chip mr-1">${{ originalPrice }}</span>
                ${{ displayPrice }}
              </v-chip>
            </div>
          </div>
        </v-col>

        <!-- Pricing Summary (for approved bookings) -->
        <v-col v-if="status === 'approved'" cols="12" md="3">
          <div class="pricing-summary-card pa-3 rounded-lg">
            <div class="text-caption mb-2 opacity-90">Total Amount</div>
            <div class="text-h5 font-weight-bold mb-2">${{ displayPrice }}</div>
            <v-divider class="my-2" style="border-color: rgba(255,255,255,0.3);"></v-divider>
            <div class="d-flex justify-space-between text-caption opacity-90">
              <span>Rate/Hour:</span>
              <span>${{ booking.hourlyRate || booking.hourly_rate || 45 }}</span>
            </div>
            <div class="d-flex justify-space-between text-caption opacity-90">
              <span>Hours/Day:</span>
              <span>{{ extractHoursFromDuty(booking.dutyType || booking.duty_type) }}</span>
            </div>
            <div v-if="booking.hasDiscount" class="mt-2">
              <v-chip color="white" size="x-small" class="mt-1" variant="flat">
                <v-icon start size="x-small" color="success">mdi-tag-check</v-icon>
                Discount Applied
              </v-chip>
            </div>
          </div>
        </v-col>

        <!-- Assignment Progress (for approved bookings) -->
        <v-col v-if="status === 'approved'" cols="12" md="2">
          <div class="assignment-progress-card pa-3 rounded-lg">
            <div class="d-flex align-center mb-2">
              <v-icon size="18" color="info" class="mr-2">mdi-account-group</v-icon>
              <span class="text-caption font-weight-medium">Assignment</span>
            </div>
            <div class="d-flex align-center justify-space-between mb-2">
              <span class="text-body-2">{{ booking.assignedCount || 0 }} / {{ booking.requiredCount || 1 }}</span>
              <v-chip 
                :color="isAssignmentComplete ? 'success' : 'warning'" 
                size="x-small"
                variant="flat"
              >
                {{ isAssignmentComplete ? 'Complete' : 'In Progress' }}
              </v-chip>
            </div>
            <v-progress-linear
              :model-value="assignmentProgress"
              :color="isAssignmentComplete ? 'success' : 'info'"
              height="8"
              rounded
            ></v-progress-linear>
          </div>
        </v-col>

        <!-- Rating (for completed bookings) -->
        <v-col v-if="status === 'completed'" cols="12" md="3">
          <div class="rating-section pa-3 rounded-lg">
            <div class="text-caption mb-2">Your Rating</div>
            <v-rating
              v-if="booking.rating"
              :model-value="booking.rating"
              color="warning"
              readonly
              density="compact"
              size="small"
            ></v-rating>
            <v-btn 
              v-else 
              color="warning" 
              size="small" 
              variant="outlined"
              @click="$emit('rate', booking)"
            >
              <v-icon start size="small">mdi-star</v-icon>
              Rate Service
            </v-btn>
          </div>
        </v-col>
        
        <!-- Actions -->
        <v-col cols="12" :md="status === 'approved' ? 3 : (status === 'completed' ? 4 : 7)">
          <div class="booking-actions" :class="{ 'flex-column': status === 'approved' }">
            <!-- Pending Actions -->
            <template v-if="status === 'pending'">
              <v-btn color="info" variant="outlined" size="small" class="mr-2 mb-2" @click="$emit('view', booking)">
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
              
              <v-alert type="info" density="compact" class="mt-3 full-width" variant="tonal">
                <div class="d-flex align-center">
                  <v-icon start size="small">mdi-information</v-icon>
                  <span class="text-caption">Your booking is under review by our admin team. We'll notify you once it's approved.</span>
                </div>
              </v-alert>
            </template>

            <!-- Approved Actions -->
            <template v-else-if="status === 'approved'">
              <v-btn color="info" variant="outlined" size="small" @click="$emit('view', booking)" block class="mb-2">
                <v-icon start>mdi-eye</v-icon>
                View Details
              </v-btn>
              <v-btn color="primary" variant="outlined" size="small" @click="$emit('contact-caregiver', booking)" block class="mb-2">
                <v-icon start>mdi-phone</v-icon>
                Contact Caregiver
              </v-btn>
              <v-btn color="success" variant="outlined" size="small" @click="$emit('contact-admin')" block>
                <v-icon start>mdi-account-tie</v-icon>
                Contact Admin
              </v-btn>
            </template>

            <!-- Completed Actions -->
            <template v-else-if="status === 'completed'">
              <v-btn color="info" variant="outlined" size="small" class="mr-2 mb-2" @click="$emit('view', booking)">
                <v-icon start>mdi-eye</v-icon>
                View Details
              </v-btn>
              <v-btn color="primary" variant="flat" size="small" class="mb-2" @click="$emit('rebook', booking)">
                <v-icon start>mdi-refresh</v-icon>
                Book Again
              </v-btn>
            </template>
          </div>
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  booking: {
    type: Object,
    required: true
  },
  status: {
    type: String,
    default: 'pending',
    validator: (value) => ['pending', 'approved', 'completed'].includes(value)
  }
});

defineEmits([
  'view',
  'edit', 
  'cancel',
  'contact-caregiver',
  'contact-admin',
  'rate',
  'rebook'
]);

// Computed status styling
const statusColor = computed(() => {
  switch (props.status) {
    case 'pending': return 'warning';
    case 'approved': return 'success';
    case 'completed': return 'success';
    default: return 'grey';
  }
});

const statusIcon = computed(() => {
  switch (props.status) {
    case 'pending': return 'mdi-clock-outline';
    case 'approved': return 'mdi-check';
    case 'completed': return 'mdi-check-all';
    default: return 'mdi-help';
  }
});

const statusLabel = computed(() => {
  switch (props.status) {
    case 'pending': return 'Pending Review';
    case 'approved': return 'Approved';
    case 'completed': return 'Completed';
    default: return 'Unknown';
  }
});

// Pricing calculations
const displayPrice = computed(() => {
  const booking = props.booking;
  
  // Check for discounted price first
  if (booking.discounted_price !== undefined && booking.discounted_price !== null) {
    return Number(booking.discounted_price).toFixed(2);
  }
  
  // Use final price or total_amount
  if (booking.final_price) return Number(booking.final_price).toFixed(2);
  if (booking.total_amount) return Number(booking.total_amount).toFixed(2);
  if (booking.price) return Number(booking.price).toFixed(2);
  
  // Calculate from hourly rate
  const hourlyRate = booking.hourly_rate || booking.hourlyRate || 45;
  const hours = extractHoursFromDuty(booking.dutyType || booking.duty_type);
  const days = booking.duration_days || booking.durationDays || 1;
  
  return (hourlyRate * hours * days).toFixed(2);
});

const originalPrice = computed(() => {
  const booking = props.booking;
  
  // Only show original if there's a discount
  if (booking.hasDiscount || (booking.original_price && booking.discounted_price)) {
    return booking.original_price ? Number(booking.original_price).toFixed(2) : null;
  }
  
  return null;
});

// Assignment progress for approved bookings
const isAssignmentComplete = computed(() => {
  return (props.booking.assignedCount || 0) >= (props.booking.requiredCount || 1);
});

const assignmentProgress = computed(() => {
  const assigned = props.booking.assignedCount || 0;
  const required = props.booking.requiredCount || 1;
  return (assigned / required) * 100;
});

// Extract hours from duty type string
function extractHoursFromDuty(dutyType) {
  if (!dutyType) return 8;
  
  const match = dutyType.match(/(\d+)\s*(?:hour|hr)/i);
  if (match) return parseInt(match[1], 10);
  
  // Map common duty types
  if (dutyType.toLowerCase().includes('24')) return 24;
  if (dutyType.toLowerCase().includes('12')) return 12;
  if (dutyType.toLowerCase().includes('8')) return 8;
  
  return 8;
}
</script>

<style scoped>
.booking-card {
  border-radius: var(--card-radius-lg, 16px) !important;
  border: 1px solid var(--border-default, #e5e7eb);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  margin-bottom: 1rem;
}

.booking-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
}

.booking-service-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary, #1a1a1a);
  margin-bottom: 0.25rem;
}

.booking-id {
  font-size: 0.8125rem;
}

.booking-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.detail-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.detail-text {
  font-size: 0.875rem;
  color: var(--text-secondary, #64748b);
}

.pricing-summary-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  height: 100%;
}

.assignment-progress-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  height: 100%;
}

.rating-section {
  background: #fffbeb;
  border: 1px solid #fde68a;
}

.booking-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
}

.booking-actions.flex-column {
  flex-direction: column;
}

.full-width {
  width: 100%;
}

.original-price-chip {
  text-decoration: line-through;
  opacity: 0.7;
}

/* Responsive Styles */
@media (max-width: 960px) {
  .booking-card .v-card-text {
    padding: 1rem !important;
  }
  
  .booking-service-title {
    font-size: 1rem;
  }
  
  .detail-text {
    font-size: 0.8125rem;
  }
  
  .pricing-summary-card,
  .assignment-progress-card,
  .rating-section {
    margin-top: 1rem;
  }
}

@media (max-width: 600px) {
  .booking-actions {
    flex-direction: column;
    width: 100%;
  }
  
  .booking-actions .v-btn {
    width: 100%;
    margin-right: 0 !important;
  }
}
</style>
