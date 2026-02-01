<template>
  <div class="admin-staff-bookings">
    <!-- Header with filters -->
    <v-card elevation="0" class="mb-4 filter-card">
      <v-card-text class="pa-4">
        <v-row align="center" class="filter-row">
          <v-col cols="12" sm="6" md="3">
            <v-text-field
              v-model="searchQuery"
              label="Search bookings..."
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              hide-details
              clearable
              @update:model-value="onSearch"
            />
          </v-col>
          <v-col cols="6" sm="3" md="2">
            <v-select
              v-model="statusFilter"
              :items="statusOptions"
              label="Status"
              variant="outlined"
              density="compact"
              hide-details
              @update:model-value="onFilterChange"
            />
          </v-col>
          <v-col cols="6" sm="3" md="2">
            <v-select
              v-model="assignmentFilter"
              :items="assignmentOptions"
              label="Assignment"
              variant="outlined"
              density="compact"
              hide-details
              @update:model-value="onFilterChange"
            />
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-btn 
              color="primary" 
              variant="tonal"
              class="mr-2"
              @click="$emit('refresh')"
            >
              <v-icon start>mdi-refresh</v-icon>
              Refresh
            </v-btn>
            <v-btn 
              color="secondary" 
              variant="text"
              @click="clearFilters"
            >
              Clear Filters
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Booking Stats Summary -->
    <v-row class="mb-4">
      <v-col v-for="stat in bookingStats" :key="stat.label" cols="6" sm="3">
        <v-card elevation="0" class="stat-mini-card" :class="stat.colorClass">
          <v-card-text class="pa-3 text-center">
            <div class="stat-mini-value">{{ stat.value }}</div>
            <div class="stat-mini-label">{{ stat.label }}</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Desktop Table View -->
    <v-card v-if="!isMobile" elevation="0" class="bookings-table-card">
      <v-data-table
        :headers="tableHeaders"
        :items="filteredBookings"
        :loading="loading"
        :items-per-page="10"
        class="elevation-0"
        hover
      >
        <!-- ID Column -->
        <template #item.id="{ item }">
          <span class="booking-id">#{{ item.id }}</span>
        </template>

        <!-- Client Column -->
        <template #item.client="{ item }">
          <div class="d-flex align-center">
            <v-avatar size="32" color="primary" class="mr-2">
              <span class="text-white text-caption">{{ getInitials(item.client?.name) }}</span>
            </v-avatar>
            <div>
              <div class="client-name">{{ item.client?.name || 'Unknown' }}</div>
              <div class="client-email">{{ item.client?.email || '' }}</div>
            </div>
          </div>
        </template>

        <!-- Service Type Column -->
        <template #item.service_type="{ item }">
          <v-chip :color="getServiceColor(item.service_type)" size="small" variant="tonal">
            {{ formatServiceType(item.service_type) }}
          </v-chip>
        </template>

        <!-- Date Column -->
        <template #item.service_date="{ item }">
          <div>
            <div class="booking-date">{{ formatDate(item.service_date) }}</div>
            <div class="booking-time text-caption">{{ item.start_time || 'TBD' }}</div>
          </div>
        </template>

        <!-- Status Column -->
        <template #item.status="{ item }">
          <v-chip :color="getStatusColor(item.status)" size="small">
            {{ formatStatus(item.status) }}
          </v-chip>
        </template>

        <!-- Assignment Column -->
        <template #item.assignment_status="{ item }">
          <v-chip :color="getAssignmentColor(item.assignment_status)" size="small" variant="outlined">
            <v-icon start size="14">{{ getAssignmentIcon(item.assignment_status) }}</v-icon>
            {{ formatAssignment(item.assignment_status) }}
          </v-chip>
        </template>

        <!-- Payment Column -->
        <template #item.payment_status="{ item }">
          <v-chip :color="item.payment_status === 'paid' ? 'success' : 'warning'" size="small" variant="tonal">
            <v-icon start size="14">{{ item.payment_status === 'paid' ? 'mdi-check-circle' : 'mdi-clock' }}</v-icon>
            {{ item.payment_status === 'paid' ? 'Paid' : 'Pending' }}
          </v-chip>
        </template>

        <!-- Actions Column -->
        <template #item.actions="{ item }">
          <div class="action-buttons">
            <v-btn 
              icon 
              size="small" 
              variant="text"
              color="primary"
              @click="$emit('view', item)"
            >
              <v-icon size="18">mdi-eye</v-icon>
              <v-tooltip activator="parent" location="top">View Details</v-tooltip>
            </v-btn>
            <v-btn 
              icon 
              size="small" 
              variant="text"
              color="success"
              :disabled="!canAssign(item)"
              @click="$emit('assign', item)"
            >
              <v-icon size="18">mdi-account-plus</v-icon>
              <v-tooltip activator="parent" location="top">Assign Caregiver</v-tooltip>
            </v-btn>
            <v-btn 
              icon 
              size="small" 
              variant="text"
              color="error"
              :disabled="!canCancel(item)"
              @click="$emit('cancel', item)"
            >
              <v-icon size="18">mdi-close-circle</v-icon>
              <v-tooltip activator="parent" location="top">Cancel Booking</v-tooltip>
            </v-btn>
          </div>
        </template>

        <!-- Empty State -->
        <template #no-data>
          <div class="empty-state py-8">
            <v-icon size="64" color="grey-lighten-1">mdi-calendar-blank</v-icon>
            <h3 class="mt-4 text-h6">No Bookings Found</h3>
            <p class="text-body-2 text-muted">
              {{ searchQuery || statusFilter !== 'all' ? 'Try adjusting your filters' : 'No bookings have been created yet' }}
            </p>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Mobile Card View -->
    <div v-else class="mobile-bookings-list">
      <v-card 
        v-for="booking in filteredBookings" 
        :key="booking.id"
        elevation="0"
        class="mobile-booking-card mb-3"
        @click="$emit('view', booking)"
      >
        <v-card-text class="pa-4">
          <!-- Header -->
          <div class="d-flex justify-space-between align-center mb-3">
            <div class="d-flex align-center">
              <v-avatar size="40" color="primary" class="mr-3">
                <span class="text-white">{{ getInitials(booking.client?.name) }}</span>
              </v-avatar>
              <div>
                <div class="font-weight-medium">{{ booking.client?.name || 'Unknown' }}</div>
                <div class="text-caption text-muted">#{{ booking.id }}</div>
              </div>
            </div>
            <v-chip :color="getStatusColor(booking.status)" size="small">
              {{ formatStatus(booking.status) }}
            </v-chip>
          </div>

          <!-- Details -->
          <v-divider class="mb-3" />
          
          <div class="booking-details-grid">
            <div class="detail-item">
              <v-icon size="16" color="primary" class="mr-1">mdi-briefcase</v-icon>
              <span>{{ formatServiceType(booking.service_type) }}</span>
            </div>
            <div class="detail-item">
              <v-icon size="16" color="success" class="mr-1">mdi-calendar</v-icon>
              <span>{{ formatDate(booking.service_date) }}</span>
            </div>
            <div class="detail-item">
              <v-icon size="16" color="warning" class="mr-1">mdi-clock</v-icon>
              <span>{{ booking.start_time || 'TBD' }}</span>
            </div>
            <div class="detail-item">
              <v-icon size="16" :color="booking.payment_status === 'paid' ? 'success' : 'warning'" class="mr-1">
                {{ booking.payment_status === 'paid' ? 'mdi-check-circle' : 'mdi-clock-outline' }}
              </v-icon>
              <span>{{ booking.payment_status === 'paid' ? 'Paid' : 'Unpaid' }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-3 d-flex justify-end gap-2">
            <v-btn 
              size="small" 
              variant="tonal"
              color="success"
              :disabled="!canAssign(booking)"
              @click.stop="$emit('assign', booking)"
            >
              <v-icon start size="16">mdi-account-plus</v-icon>
              Assign
            </v-btn>
            <v-btn 
              size="small" 
              variant="outlined"
              color="primary"
              @click.stop="$emit('view', booking)"
            >
              <v-icon start size="16">mdi-eye</v-icon>
              View
            </v-btn>
          </div>
        </v-card-text>
      </v-card>

      <!-- Mobile Empty State -->
      <div v-if="filteredBookings.length === 0" class="empty-state py-8 text-center">
        <v-icon size="64" color="grey-lighten-1">mdi-calendar-blank</v-icon>
        <h3 class="mt-4 text-h6">No Bookings Found</h3>
        <p class="text-body-2 text-muted">
          {{ searchQuery || statusFilter !== 'all' ? 'Try adjusting your filters' : 'No bookings yet' }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

// Props
const props = defineProps({
  bookings: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
});

// Emits
defineEmits(['view', 'assign', 'cancel', 'refresh']);

// State
const searchQuery = ref('');
const statusFilter = ref('all');
const assignmentFilter = ref('all');
const isMobile = ref(window.innerWidth <= 768);

// Options
const statusOptions = [
  { title: 'All Statuses', value: 'all' },
  { title: 'Pending', value: 'pending' },
  { title: 'Approved', value: 'approved' },
  { title: 'Completed', value: 'completed' },
  { title: 'Cancelled', value: 'cancelled' },
];

const assignmentOptions = [
  { title: 'All', value: 'all' },
  { title: 'Assigned', value: 'assigned' },
  { title: 'Unassigned', value: 'unassigned' },
  { title: 'Partial', value: 'partial' },
];

const tableHeaders = [
  { title: 'ID', key: 'id', width: '80px' },
  { title: 'Client', key: 'client', width: '200px' },
  { title: 'Service', key: 'service_type', width: '120px' },
  { title: 'Date', key: 'service_date', width: '120px' },
  { title: 'Status', key: 'status', width: '100px' },
  { title: 'Assignment', key: 'assignment_status', width: '120px' },
  { title: 'Payment', key: 'payment_status', width: '100px' },
  { title: 'Actions', key: 'actions', width: '150px', sortable: false },
];

// Computed
const filteredBookings = computed(() => {
  let result = [...props.bookings];
  
  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(b => 
      b.client?.name?.toLowerCase().includes(query) ||
      b.service_type?.toLowerCase().includes(query) ||
      b.id?.toString().includes(query) ||
      b.borough?.toLowerCase().includes(query)
    );
  }
  
  // Status filter
  if (statusFilter.value !== 'all') {
    result = result.filter(b => b.status === statusFilter.value);
  }
  
  // Assignment filter
  if (assignmentFilter.value !== 'all') {
    result = result.filter(b => b.assignment_status === assignmentFilter.value);
  }
  
  return result;
});

const bookingStats = computed(() => {
  const total = props.bookings.length;
  const pending = props.bookings.filter(b => b.status === 'pending').length;
  const approved = props.bookings.filter(b => b.status === 'approved').length;
  const unassigned = props.bookings.filter(b => b.assignment_status === 'unassigned').length;
  
  return [
    { label: 'Total', value: total, colorClass: 'bg-primary-light' },
    { label: 'Pending', value: pending, colorClass: 'bg-warning-light' },
    { label: 'Approved', value: approved, colorClass: 'bg-success-light' },
    { label: 'Unassigned', value: unassigned, colorClass: 'bg-error-light' },
  ];
});

// Methods
const onSearch = () => {
  // Debounced search handled by Vue reactivity
};

const onFilterChange = () => {
  // Filter change handled by computed
};

const clearFilters = () => {
  searchQuery.value = '';
  statusFilter.value = 'all';
  assignmentFilter.value = 'all';
};

const canAssign = (booking) => {
  return booking.status !== 'cancelled' && booking.assignment_status !== 'assigned';
};

const canCancel = (booking) => {
  return booking.status !== 'cancelled' && booking.status !== 'completed';
};

const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatServiceType = (type) => {
  if (!type) return 'Unknown';
  return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatDate = (date) => {
  if (!date) return 'TBD';
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const formatStatus = (status) => {
  if (!status) return 'Unknown';
  return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatAssignment = (status) => {
  const labels = {
    assigned: 'Assigned',
    unassigned: 'Unassigned',
    partial: 'Partial',
  };
  return labels[status] || status;
};

const getServiceColor = (type) => {
  const colors = {
    caregiving: 'primary',
    housekeeping: 'success',
    companion: 'info',
    respite: 'warning',
  };
  return colors[type?.toLowerCase()] || 'secondary';
};

const getStatusColor = (status) => {
  const colors = {
    pending: 'warning',
    approved: 'success',
    completed: 'info',
    cancelled: 'error',
  };
  return colors[status] || 'grey';
};

const getAssignmentColor = (status) => {
  const colors = {
    assigned: 'success',
    unassigned: 'error',
    partial: 'warning',
  };
  return colors[status] || 'grey';
};

const getAssignmentIcon = (status) => {
  const icons = {
    assigned: 'mdi-check',
    unassigned: 'mdi-account-off',
    partial: 'mdi-account-clock',
  };
  return icons[status] || 'mdi-help';
};

// Resize handler
const handleResize = () => {
  isMobile.value = window.innerWidth <= 768;
};

onMounted(() => {
  window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
});
</script>

<style scoped>
.admin-staff-bookings {
  width: 100%;
}

.filter-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
}

.stat-mini-card {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.stat-mini-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.stat-mini-label {
  font-size: 0.75rem;
  color: #4b5563;
}

.bg-primary-light { background-color: rgba(59, 130, 246, 0.1) !important; }
.bg-warning-light { background-color: rgba(245, 158, 11, 0.1) !important; }
.bg-success-light { background-color: rgba(16, 185, 129, 0.1) !important; }
.bg-error-light { background-color: rgba(239, 68, 68, 0.1) !important; }

.bookings-table-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
}

.booking-id {
  font-family: monospace;
  font-weight: 600;
  color: #4b5563;
}

.client-name {
  font-weight: 500;
  font-size: 0.875rem;
}

.client-email {
  font-size: 0.75rem;
  color: #4b5563;
}

.booking-date {
  font-weight: 500;
}

.action-buttons {
  display: flex;
  gap: 4px;
}

.action-buttons .v-btn {
  min-width: 44px !important;
  min-height: 44px !important;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.mobile-booking-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: box-shadow 0.2s ease;
}

.mobile-booking-card:active {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.booking-details-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}

.detail-item {
  display: flex;
  align-items: center;
  font-size: 0.875rem;
  color: #4b5563;
}

.text-muted {
  color: #4b5563 !important;
}

.gap-2 {
  gap: 8px;
}

@media (max-width: 600px) {
  .filter-row .v-col {
    padding: 4px 8px;
  }
  
  .booking-details-grid {
    grid-template-columns: 1fr;
  }
}
</style>
