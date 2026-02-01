<template>
  <v-container fluid class="admin-bookings-tab pa-0">
    <v-row>
      <v-col cols="12">
        <v-card elevation="2" rounded="lg">
          <v-card-title class="d-flex align-center flex-wrap py-4 px-6">
            <v-icon color="primary" class="mr-3">mdi-calendar-check</v-icon>
            <span class="text-h6 font-weight-bold">Booking Management</span>
            <v-spacer></v-spacer>
            
            <!-- Status Filter -->
            <v-select
              v-model="statusFilter"
              :items="statusOptions"
              label="Filter by Status"
              density="compact"
              variant="outlined"
              hide-details
              class="max-width-200 mr-4"
              clearable
              aria-label="Filter bookings by status"
            ></v-select>
            
            <v-text-field
              v-model="bookingSearch"
              prepend-inner-icon="mdi-magnify"
              label="Search bookings..."
              single-line
              hide-details
              density="compact"
              variant="outlined"
              class="max-width-300"
              clearable
              autocomplete="off"
              aria-label="Search bookings"
            ></v-text-field>
          </v-card-title>
          
          <v-divider></v-divider>
          
          <v-card-text class="pa-0">
            <v-data-table
              :headers="bookingHeaders"
              :items="filteredBookings"
              :loading="loading"
              :search="bookingSearch"
              :items-per-page="10"
              class="elevation-0"
              hover
            >
              <!-- Client Column -->
              <template v-slot:item.client="{ item }">
                <div class="d-flex align-center py-2">
                  <v-avatar size="32" class="mr-2" color="teal">
                    <span class="text-white text-caption">{{ getInitials(item.client?.name) }}</span>
                  </v-avatar>
                  <div>
                    <div class="font-weight-medium">{{ item.client?.name || 'N/A' }}</div>
                    <div class="text-caption text-grey">{{ item.client?.email }}</div>
                  </div>
                </div>
              </template>
              
              <!-- Caregiver Column -->
              <template v-slot:item.caregiver="{ item }">
                <div v-if="item.caregiver" class="d-flex align-center">
                  <v-avatar size="32" class="mr-2" color="green">
                    <span class="text-white text-caption">{{ getInitials(item.caregiver.name) }}</span>
                  </v-avatar>
                  <div>
                    <div class="font-weight-medium">{{ item.caregiver.name }}</div>
                    <div class="text-caption text-grey">{{ item.caregiver.email }}</div>
                  </div>
                </div>
                <v-chip v-else size="small" color="warning" variant="outlined">
                  <v-icon start size="14">mdi-account-question</v-icon>
                  Unassigned
                </v-chip>
              </template>
              
              <!-- Status Column -->
              <template v-slot:item.status="{ item }">
                <v-chip :color="getBookingStatusColor(item.status)" size="small" variant="flat">
                  <v-icon start size="14">{{ getStatusIcon(item.status) }}</v-icon>
                  {{ formatStatus(item.status) }}
                </v-chip>
              </template>
              
              <!-- Date Column -->
              <template v-slot:item.scheduled_date="{ item }">
                <div>
                  <div class="font-weight-medium">{{ formatDate(item.scheduled_date) }}</div>
                  <div class="text-caption text-grey">
                    {{ formatTime(item.start_time) }} - {{ formatTime(item.end_time) }}
                  </div>
                </div>
              </template>
              
              <!-- Amount Column -->
              <template v-slot:item.total_amount="{ item }">
                <span class="font-weight-bold text-success">${{ formatMoney(item.total_amount) }}</span>
              </template>
              
              <!-- Actions Column -->
              <template v-slot:item.actions="{ item }">
                <v-menu>
                  <template v-slot:activator="{ props }">
                    <v-btn 
                      icon="mdi-dots-vertical" 
                      size="small" 
                      variant="text" 
                      v-bind="props" 
                      :aria-label="`Actions for booking ${item.id}`"
                    ></v-btn>
                  </template>
                  <v-list density="compact">
                    <v-list-item @click="viewBooking(item)" prepend-icon="mdi-eye">
                      <v-list-item-title>View Details</v-list-item-title>
                    </v-list-item>
                    <v-list-item 
                      v-if="item.status === 'pending'" 
                      @click="approveBooking(item)" 
                      prepend-icon="mdi-check"
                    >
                      <v-list-item-title>Approve</v-list-item-title>
                    </v-list-item>
                    <v-list-item 
                      v-if="!item.caregiver_id" 
                      @click="openAssignDialog(item)" 
                      prepend-icon="mdi-account-plus"
                    >
                      <v-list-item-title>Assign Caregiver</v-list-item-title>
                    </v-list-item>
                    <v-list-item 
                      v-if="item.status !== 'cancelled' && item.status !== 'completed'"
                      @click="cancelBooking(item)" 
                      prepend-icon="mdi-cancel" 
                      class="text-error"
                    >
                      <v-list-item-title>Cancel Booking</v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </template>
              
              <!-- Loading State -->
              <template v-slot:loading>
                <v-skeleton-loader type="table-row@5"></v-skeleton-loader>
              </template>
              
              <!-- Empty State -->
              <template v-slot:no-data>
                <div class="text-center py-8">
                  <v-icon size="64" color="grey-lighten-1">mdi-calendar-blank</v-icon>
                  <p class="text-grey mt-4">No bookings found</p>
                </div>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    
    <!-- Booking Detail Dialog -->
    <v-dialog 
      v-model="bookingDialog" 
      max-width="800" 
      role="dialog" 
      aria-modal="true" 
      aria-labelledby="booking-dialog-title"
    >
      <v-card>
        <v-card-title id="booking-dialog-title" class="d-flex align-center">
          <v-icon class="mr-2">mdi-calendar</v-icon>
          Booking #{{ selectedBooking?.id }}
          <v-spacer></v-spacer>
          <v-chip 
            v-if="selectedBooking" 
            :color="getBookingStatusColor(selectedBooking.status)" 
            size="small"
          >
            {{ formatStatus(selectedBooking.status) }}
          </v-chip>
          <v-btn 
            icon="mdi-close" 
            variant="text" 
            @click="bookingDialog = false" 
            aria-label="Close dialog"
            class="ml-2"
          ></v-btn>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text v-if="selectedBooking" class="pa-6">
          <v-row>
            <!-- Client Info -->
            <v-col cols="12" md="6">
              <h4 class="text-subtitle-2 text-grey mb-2">Client</h4>
              <div class="d-flex align-center">
                <v-avatar color="teal" size="48" class="mr-3">
                  <span class="text-white">{{ getInitials(selectedBooking.client?.name) }}</span>
                </v-avatar>
                <div>
                  <p class="font-weight-bold mb-0">{{ selectedBooking.client?.name }}</p>
                  <p class="text-caption text-grey mb-0">{{ selectedBooking.client?.email }}</p>
                  <p class="text-caption text-grey mb-0">{{ selectedBooking.client?.phone }}</p>
                </div>
              </div>
            </v-col>
            
            <!-- Caregiver Info -->
            <v-col cols="12" md="6">
              <h4 class="text-subtitle-2 text-grey mb-2">Caregiver</h4>
              <div v-if="selectedBooking.caregiver" class="d-flex align-center">
                <v-avatar color="green" size="48" class="mr-3">
                  <span class="text-white">{{ getInitials(selectedBooking.caregiver?.name) }}</span>
                </v-avatar>
                <div>
                  <p class="font-weight-bold mb-0">{{ selectedBooking.caregiver?.name }}</p>
                  <p class="text-caption text-grey mb-0">{{ selectedBooking.caregiver?.email }}</p>
                </div>
              </div>
              <v-chip v-else color="warning" variant="outlined">Not Assigned</v-chip>
            </v-col>
            
            <!-- Booking Details -->
            <v-col cols="12">
              <v-divider class="my-4"></v-divider>
              <h4 class="text-subtitle-2 text-grey mb-3">Booking Details</h4>
            </v-col>
            <v-col cols="6" sm="3">
              <p class="text-caption text-grey mb-1">Date</p>
              <p class="font-weight-medium">{{ formatDate(selectedBooking.scheduled_date) }}</p>
            </v-col>
            <v-col cols="6" sm="3">
              <p class="text-caption text-grey mb-1">Time</p>
              <p class="font-weight-medium">
                {{ formatTime(selectedBooking.start_time) }} - {{ formatTime(selectedBooking.end_time) }}
              </p>
            </v-col>
            <v-col cols="6" sm="3">
              <p class="text-caption text-grey mb-1">Service Type</p>
              <p class="font-weight-medium">{{ selectedBooking.service_type || 'Standard Care' }}</p>
            </v-col>
            <v-col cols="6" sm="3">
              <p class="text-caption text-grey mb-1">Total Amount</p>
              <p class="font-weight-bold text-success text-h6">${{ formatMoney(selectedBooking.total_amount) }}</p>
            </v-col>
            
            <!-- Address -->
            <v-col cols="12">
              <p class="text-caption text-grey mb-1">Service Address</p>
              <p class="font-weight-medium">{{ selectedBooking.address || 'N/A' }}</p>
            </v-col>
            
            <!-- Notes -->
            <v-col cols="12" v-if="selectedBooking.notes">
              <p class="text-caption text-grey mb-1">Notes</p>
              <p class="font-weight-medium">{{ selectedBooking.notes }}</p>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="bookingDialog = false">Close</v-btn>
          <v-btn 
            v-if="selectedBooking?.status === 'pending'" 
            color="success" 
            variant="flat"
            @click="approveBooking(selectedBooking); bookingDialog = false;"
          >
            Approve
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Assign Caregiver Dialog -->
    <v-dialog 
      v-model="assignDialog" 
      max-width="500" 
      role="dialog" 
      aria-modal="true" 
      aria-labelledby="assign-dialog-title"
    >
      <v-card>
        <v-card-title id="assign-dialog-title">
          <v-icon class="mr-2">mdi-account-plus</v-icon>
          Assign Caregiver
        </v-card-title>
        <v-card-text>
          <p class="mb-4">Select a caregiver for booking #{{ bookingToAssign?.id }}</p>
          <v-autocomplete
            v-model="selectedCaregiver"
            :items="availableCaregivers"
            item-title="name"
            item-value="id"
            label="Select Caregiver"
            variant="outlined"
            :loading="loadingCaregivers"
            prepend-inner-icon="mdi-account-search"
            autocomplete="off"
            aria-label="Select caregiver to assign"
          >
            <template v-slot:item="{ item, props }">
              <v-list-item v-bind="props">
                <template v-slot:prepend>
                  <v-avatar size="36" color="green">
                    <span class="text-white text-caption">{{ getInitials(item.raw.name) }}</span>
                  </v-avatar>
                </template>
                <v-list-item-subtitle>{{ item.raw.email }}</v-list-item-subtitle>
              </v-list-item>
            </template>
          </v-autocomplete>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="assignDialog = false">Cancel</v-btn>
          <v-btn 
            color="primary" 
            variant="flat" 
            @click="confirmAssign" 
            :loading="assigning" 
            :disabled="!selectedCaregiver"
          >
            Assign
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
/**
 * AdminBookingsTab Component
 * Manages booking listing, viewing, approval, and caregiver assignment for admin dashboard
 */

import { ref, computed } from 'vue';

// Props
const props = defineProps({
  bookings: { type: Array, default: () => [] },
  caregivers: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false }
});

// Emits
const emit = defineEmits(['approve', 'cancel', 'assign', 'view']);

// State
const bookingSearch = ref('');
const statusFilter = ref(null);
const bookingDialog = ref(false);
const assignDialog = ref(false);
const selectedBooking = ref(null);
const bookingToAssign = ref(null);
const selectedCaregiver = ref(null);
const loadingCaregivers = ref(false);
const assigning = ref(false);

const statusOptions = [
  { title: 'Pending', value: 'pending' },
  { title: 'Approved', value: 'approved' },
  { title: 'In Progress', value: 'in_progress' },
  { title: 'Completed', value: 'completed' },
  { title: 'Cancelled', value: 'cancelled' }
];

const bookingHeaders = [
  { title: 'ID', key: 'id', sortable: true, width: '80px' },
  { title: 'Client', key: 'client', sortable: true },
  { title: 'Caregiver', key: 'caregiver', sortable: true },
  { title: 'Date & Time', key: 'scheduled_date', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Amount', key: 'total_amount', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center', width: '100px' }
];

// Computed
const filteredBookings = computed(() => {
  if (!statusFilter.value) return props.bookings;
  return props.bookings.filter(b => b.status === statusFilter.value);
});

const availableCaregivers = computed(() => {
  return props.caregivers.filter(c => c.status === 'active' || c.status === 'approved');
});

// Methods
const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const getBookingStatusColor = (status) => {
  const colors = {
    pending: 'warning',
    approved: 'info',
    in_progress: 'primary',
    completed: 'success',
    cancelled: 'error'
  };
  return colors[status] || 'grey';
};

const getStatusIcon = (status) => {
  const icons = {
    pending: 'mdi-clock-outline',
    approved: 'mdi-check-circle-outline',
    in_progress: 'mdi-progress-clock',
    completed: 'mdi-check-all',
    cancelled: 'mdi-cancel'
  };
  return icons[status] || 'mdi-help-circle-outline';
};

const formatStatus = (status) => {
  return status?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) || 'Unknown';
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric' 
  });
};

const formatTime = (time) => {
  if (!time) return '';
  return time.substring(0, 5);
};

const formatMoney = (amount) => {
  return (parseFloat(amount) || 0).toFixed(2);
};

const viewBooking = (booking) => {
  selectedBooking.value = booking;
  bookingDialog.value = true;
  emit('view', booking);
};

const approveBooking = (booking) => {
  emit('approve', booking);
};

const cancelBooking = (booking) => {
  emit('cancel', booking);
};

const openAssignDialog = (booking) => {
  bookingToAssign.value = booking;
  selectedCaregiver.value = null;
  assignDialog.value = true;
};

const confirmAssign = () => {
  assigning.value = true;
  emit('assign', { 
    booking: bookingToAssign.value, 
    caregiverId: selectedCaregiver.value 
  });
  assignDialog.value = false;
  assigning.value = false;
};
</script>

<style scoped>
.max-width-200 {
  max-width: 200px;
}
.max-width-300 {
  max-width: 300px;
}

/* Focus visible for accessibility */
.admin-bookings-tab :deep(.v-btn:focus-visible) {
  outline: 2px solid var(--v-theme-primary);
  outline-offset: 2px;
}
</style>
