<template>
  <div class="client-bookings-section">
    <div class="bookings-header mb-6">
      <h1 class="bookings-title">My Bookings</h1>
      <p class="bookings-subtitle">Review and manage your care service requests</p>
    </div>

    <!-- Booking Tabs -->
    <v-tabs 
      v-model="bookingTab" 
      color="primary" 
      class="mb-6 booking-tabs" 
      bg-color="transparent" 
      show-arrows 
      density="comfortable"
    >
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
        <v-chip v-if="completedBookings.length > 0" size="x-small" color="grey" class="ml-2">
          {{ completedBookings.length }}
        </v-chip>
      </v-tab>
    </v-tabs>

    <v-window v-model="bookingTab">
      <!-- Pending Bookings Tab -->
      <v-window-item value="pending">
        <div v-if="loading" class="text-center pa-8">
          <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
          <p class="mt-4 text-grey">Loading bookings...</p>
        </div>
        
        <v-alert v-else-if="pendingBookings.length === 0" type="info" class="mt-4">
          <v-icon start>mdi-information</v-icon>
          No pending requests. 
          <a href="#" @click.prevent="$emit('book-now')" class="text-primary font-weight-medium">
            Submit a new request
          </a>
        </v-alert>

        <v-row v-else>
          <v-col v-for="booking in pendingBookings" :key="booking.id" cols="12">
            <BookingCard 
              :booking="booking" 
              status="pending"
              @view="$emit('view-booking', booking)"
              @edit="$emit('edit-booking', booking.id)"
              @cancel="$emit('cancel-booking', booking.id)"
            />
          </v-col>
        </v-row>
      </v-window-item>

      <!-- Approved Bookings Tab -->
      <v-window-item value="approved">
        <div v-if="loading" class="text-center pa-8">
          <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
          <p class="mt-4 text-grey">Loading bookings...</p>
        </div>
        
        <v-alert v-else-if="confirmedBookings.length === 0" type="info" class="mt-4">
          <v-icon start>mdi-information</v-icon>
          No approved bookings
        </v-alert>

        <v-row v-else>
          <v-col v-for="booking in confirmedBookings" :key="booking.id" cols="12">
            <BookingCard 
              :booking="booking" 
              status="approved"
              @view="$emit('view-booking', booking)"
              @contact-caregiver="$emit('contact-caregiver', booking)"
              @contact-admin="$emit('contact-admin')"
            />
          </v-col>
        </v-row>
      </v-window-item>

      <!-- Completed Bookings Tab -->
      <v-window-item value="completed">
        <div v-if="loading" class="text-center pa-8">
          <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
          <p class="mt-4 text-grey">Loading bookings...</p>
        </div>
        
        <v-alert v-else-if="completedBookings.length === 0" type="info" class="mt-4">
          <v-icon start>mdi-information</v-icon>
          No completed bookings yet
        </v-alert>

        <v-row v-else>
          <v-col v-for="booking in completedBookings" :key="booking.id" cols="12">
            <BookingCard 
              :booking="booking" 
              status="completed"
              @view="$emit('view-booking', booking)"
              @rate="$emit('rate-booking', booking)"
              @rebook="$emit('rebook', booking)"
            />
          </v-col>
        </v-row>
      </v-window-item>
    </v-window>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import BookingCard from './BookingCard.vue';

const props = defineProps({
  bookings: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  initialTab: {
    type: String,
    default: 'pending'
  }
});

const emit = defineEmits([
  'book-now',
  'view-booking',
  'edit-booking',
  'cancel-booking',
  'contact-caregiver',
  'contact-admin',
  'rate-booking',
  'rebook',
  'tab-change'
]);

// Local state
const bookingTab = ref(props.initialTab);

// Computed properties for filtered bookings
const pendingBookings = computed(() => {
  return props.bookings.filter(b => 
    b.status === 'pending' || 
    b.status === 'pending_review' ||
    b.status === 'Pending'
  );
});

const confirmedBookings = computed(() => {
  return props.bookings.filter(b => 
    b.status === 'approved' || 
    b.status === 'confirmed' ||
    b.status === 'Approved' ||
    b.status === 'Confirmed'
  );
});

const completedBookings = computed(() => {
  return props.bookings.filter(b => 
    b.status === 'completed' || 
    b.status === 'Completed'
  );
});

// Watch tab changes
watch(bookingTab, (newTab) => {
  emit('tab-change', newTab);
});

// Sync with initialTab prop
watch(() => props.initialTab, (newTab) => {
  bookingTab.value = newTab;
});
</script>

<style scoped>
.client-bookings-section {
  animation: fadeInUp 0.3s ease-out;
}

.bookings-header {
  margin-bottom: 1.5rem;
}

.bookings-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text-primary, #1a1a1a);
  margin-bottom: 0.25rem;
  letter-spacing: -0.02em;
}

.bookings-subtitle {
  font-size: 0.9375rem;
  color: var(--text-secondary, #666);
  margin: 0;
}

.booking-tabs {
  border-radius: var(--card-radius-sm, 12px);
  overflow: hidden;
}

.booking-tabs :deep(.v-tab) {
  min-width: 120px;
  text-transform: none;
  font-weight: 500;
  letter-spacing: 0;
}

.tab-text {
  font-size: 0.875rem;
}

/* Responsive Styles */
@media (max-width: 768px) {
  .bookings-title {
    font-size: 1.5rem;
  }
  
  .bookings-subtitle {
    font-size: 0.875rem;
  }
  
  .booking-tabs :deep(.v-tab) {
    min-width: 100px;
    padding: 0 12px;
  }
  
  .tab-text {
    font-size: 0.75rem;
  }
}

@media (max-width: 480px) {
  .bookings-title {
    font-size: 1.25rem;
  }
  
  .tab-text {
    display: none;
  }
  
  .booking-tabs :deep(.v-tab) {
    min-width: 60px;
  }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
