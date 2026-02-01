<template>
  <div class="admin-staff-caregivers">
    <!-- Header with filters -->
    <v-card elevation="0" class="mb-4 filter-card">
      <v-card-text class="pa-4">
        <v-row align="center">
          <v-col cols="12" sm="6" md="3">
            <v-text-field
              v-model="searchQuery"
              label="Search caregivers..."
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              hide-details
              clearable
            />
          </v-col>
          <v-col cols="6" sm="3" md="2">
            <v-select
              v-model="typeFilter"
              :items="typeOptions"
              label="Type"
              variant="outlined"
              density="compact"
              hide-details
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
            />
          </v-col>
          <v-col cols="6" sm="3" md="2">
            <v-select
              v-model="availabilityFilter"
              :items="availabilityOptions"
              label="Availability"
              variant="outlined"
              density="compact"
              hide-details
            />
          </v-col>
          <v-col cols="6" sm="3" md="3" class="d-flex gap-2">
            <v-btn color="primary" variant="elevated" @click="$emit('add')">
              <v-icon start>mdi-plus</v-icon>
              Add
            </v-btn>
            <v-btn color="secondary" variant="tonal" @click="$emit('refresh')">
              <v-icon>mdi-refresh</v-icon>
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Stats Summary -->
    <v-row class="mb-4">
      <v-col v-for="stat in caregiverStats" :key="stat.label" cols="6" sm="3">
        <v-card elevation="0" class="stat-card" :class="stat.colorClass">
          <v-card-text class="pa-3 text-center">
            <v-icon :color="stat.iconColor" size="28" class="mb-1">{{ stat.icon }}</v-icon>
            <div class="stat-value">{{ stat.value }}</div>
            <div class="stat-label">{{ stat.label }}</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Caregivers Grid -->
    <v-row>
      <v-col 
        v-for="caregiver in filteredCaregivers" 
        :key="caregiver.id" 
        cols="12" 
        sm="6" 
        md="4" 
        lg="3"
      >
        <v-card elevation="0" class="caregiver-card" @click="$emit('view', caregiver)">
          <!-- Status Badge -->
          <div class="status-badge" :class="getStatusClass(caregiver)">
            {{ getStatusLabel(caregiver) }}
          </div>

          <v-card-text class="pa-4">
            <!-- Avatar & Name -->
            <div class="text-center mb-3">
              <v-avatar size="80" :color="getTypeColor(caregiver.type)" class="mb-2">
                <v-img v-if="caregiver.avatar" :src="caregiver.avatar" />
                <span v-else class="text-white text-h5">{{ getInitials(caregiver.name) }}</span>
              </v-avatar>
              <h3 class="caregiver-name">{{ caregiver.name }}</h3>
              <v-chip 
                :color="getTypeColor(caregiver.type)" 
                size="x-small" 
                class="mt-1"
              >
                {{ formatType(caregiver.type) }}
              </v-chip>
            </div>

            <!-- Info -->
            <div class="caregiver-info">
              <div class="info-row">
                <v-icon size="16" color="grey">mdi-map-marker</v-icon>
                <span>{{ caregiver.location || 'Not specified' }}</span>
              </div>
              <div class="info-row">
                <v-icon size="16" color="grey">mdi-star</v-icon>
                <span>{{ caregiver.rating?.toFixed(1) || 'N/A' }} ({{ caregiver.reviews_count || 0 }} reviews)</span>
              </div>
              <div class="info-row">
                <v-icon size="16" color="grey">mdi-calendar-check</v-icon>
                <span>{{ caregiver.completed_jobs || 0 }} jobs completed</span>
              </div>
              <div class="info-row">
                <v-icon size="16" color="grey">mdi-currency-usd</v-icon>
                <span>${{ caregiver.hourly_rate || 0 }}/hr</span>
              </div>
            </div>

            <!-- Availability Indicator -->
            <div class="availability-section mt-3">
              <div class="d-flex align-center justify-center">
                <v-icon 
                  size="12" 
                  :color="caregiver.is_available ? 'success' : 'grey'"
                  class="mr-1"
                >
                  mdi-circle
                </v-icon>
                <span class="availability-text">
                  {{ caregiver.is_available ? 'Available Now' : 'Not Available' }}
                </span>
              </div>
            </div>

            <!-- Actions -->
            <div class="mt-3 d-flex gap-2 justify-center">
              <v-btn 
                size="small" 
                variant="tonal" 
                color="primary"
                @click.stop="$emit('view', caregiver)"
              >
                <v-icon start size="16">mdi-eye</v-icon>
                View
              </v-btn>
              <v-btn 
                size="small" 
                variant="outlined" 
                color="success"
                :disabled="!caregiver.is_available"
                @click.stop="$emit('assign', caregiver)"
              >
                <v-icon start size="16">mdi-calendar-plus</v-icon>
                Assign
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Empty State -->
    <v-card v-if="filteredCaregivers.length === 0" elevation="0" class="empty-card">
      <v-card-text class="text-center py-12">
        <v-icon size="64" color="grey-lighten-1">mdi-account-heart</v-icon>
        <h3 class="mt-4 text-h6">No Caregivers Found</h3>
        <p class="text-body-2 text-muted">
          {{ searchQuery || typeFilter !== 'all' ? 'Try adjusting your filters' : 'No caregivers registered yet' }}
        </p>
        <v-btn color="primary" class="mt-4" @click="$emit('add')">
          <v-icon start>mdi-plus</v-icon>
          Add Caregiver
        </v-btn>
      </v-card-text>
    </v-card>

    <!-- Caregiver Details Dialog -->
    <v-dialog v-model="detailsDialog" max-width="600">
      <v-card v-if="selectedCaregiver">
        <v-card-title class="d-flex align-center pa-4">
          <v-avatar :color="getTypeColor(selectedCaregiver.type)" size="48" class="mr-3">
            <span class="text-white">{{ getInitials(selectedCaregiver.name) }}</span>
          </v-avatar>
          <div>
            <h3>{{ selectedCaregiver.name }}</h3>
            <v-chip :color="getTypeColor(selectedCaregiver.type)" size="x-small">
              {{ formatType(selectedCaregiver.type) }}
            </v-chip>
          </div>
          <v-spacer />
          <v-btn icon variant="text" @click="detailsDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-divider />

        <v-card-text class="pa-4">
          <v-row>
            <v-col cols="6">
              <div class="detail-label">Email</div>
              <div class="detail-value">{{ selectedCaregiver.email }}</div>
            </v-col>
            <v-col cols="6">
              <div class="detail-label">Phone</div>
              <div class="detail-value">{{ selectedCaregiver.phone || 'N/A' }}</div>
            </v-col>
            <v-col cols="6">
              <div class="detail-label">Location</div>
              <div class="detail-value">{{ selectedCaregiver.location || 'Not specified' }}</div>
            </v-col>
            <v-col cols="6">
              <div class="detail-label">Hourly Rate</div>
              <div class="detail-value">${{ selectedCaregiver.hourly_rate || 0 }}/hr</div>
            </v-col>
            <v-col cols="6">
              <div class="detail-label">Rating</div>
              <div class="detail-value">
                <v-rating 
                  :model-value="selectedCaregiver.rating || 0" 
                  readonly 
                  density="compact"
                  size="small"
                  color="warning"
                />
                ({{ selectedCaregiver.reviews_count || 0 }} reviews)
              </div>
            </v-col>
            <v-col cols="6">
              <div class="detail-label">Jobs Completed</div>
              <div class="detail-value">{{ selectedCaregiver.completed_jobs || 0 }}</div>
            </v-col>
            <v-col cols="12">
              <div class="detail-label">Status</div>
              <v-chip 
                :color="selectedCaregiver.status === 'approved' ? 'success' : 'warning'"
                size="small"
              >
                {{ selectedCaregiver.status || 'Pending' }}
              </v-chip>
            </v-col>
          </v-row>
        </v-card-text>

        <v-divider />

        <v-card-actions class="pa-4">
          <v-btn 
            variant="text" 
            color="error"
            @click="$emit('delete', selectedCaregiver)"
          >
            Delete
          </v-btn>
          <v-spacer />
          <v-btn variant="outlined" @click="detailsDialog = false">Close</v-btn>
          <v-btn 
            color="primary"
            @click="$emit('edit', selectedCaregiver)"
          >
            Edit Profile
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

// Props
const props = defineProps({
  caregivers: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
});

// Emits
defineEmits(['view', 'edit', 'add', 'delete', 'assign', 'refresh']);

// State
const searchQuery = ref('');
const typeFilter = ref('all');
const statusFilter = ref('all');
const availabilityFilter = ref('all');
const detailsDialog = ref(false);
const selectedCaregiver = ref(null);

// Options
const typeOptions = [
  { title: 'All Types', value: 'all' },
  { title: 'Caregivers', value: 'caregiver' },
  { title: 'Housekeepers', value: 'housekeeper' },
];

const statusOptions = [
  { title: 'All', value: 'all' },
  { title: 'Approved', value: 'approved' },
  { title: 'Pending', value: 'pending' },
  { title: 'Suspended', value: 'suspended' },
];

const availabilityOptions = [
  { title: 'All', value: 'all' },
  { title: 'Available', value: 'available' },
  { title: 'Unavailable', value: 'unavailable' },
];

// Computed
const filteredCaregivers = computed(() => {
  let result = [...props.caregivers];
  
  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(c => 
      c.name?.toLowerCase().includes(query) ||
      c.email?.toLowerCase().includes(query) ||
      c.location?.toLowerCase().includes(query)
    );
  }
  
  // Type filter
  if (typeFilter.value !== 'all') {
    result = result.filter(c => c.type === typeFilter.value);
  }
  
  // Status filter
  if (statusFilter.value !== 'all') {
    result = result.filter(c => c.status === statusFilter.value);
  }
  
  // Availability filter
  if (availabilityFilter.value !== 'all') {
    const isAvailable = availabilityFilter.value === 'available';
    result = result.filter(c => c.is_available === isAvailable);
  }
  
  return result;
});

const caregiverStats = computed(() => {
  const total = props.caregivers.length;
  const caregivers = props.caregivers.filter(c => c.type === 'caregiver').length;
  const housekeepers = props.caregivers.filter(c => c.type === 'housekeeper').length;
  const available = props.caregivers.filter(c => c.is_available).length;
  
  return [
    { 
      label: 'Total', 
      value: total, 
      icon: 'mdi-account-group',
      iconColor: 'primary',
      colorClass: 'bg-primary-light' 
    },
    { 
      label: 'Caregivers', 
      value: caregivers, 
      icon: 'mdi-account-heart',
      iconColor: 'success',
      colorClass: 'bg-success-light' 
    },
    { 
      label: 'Housekeepers', 
      value: housekeepers, 
      icon: 'mdi-broom',
      iconColor: 'info',
      colorClass: 'bg-info-light' 
    },
    { 
      label: 'Available', 
      value: available, 
      icon: 'mdi-check-circle',
      iconColor: 'warning',
      colorClass: 'bg-warning-light' 
    },
  ];
});

// Methods
const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatType = (type) => {
  const labels = {
    caregiver: 'Caregiver',
    housekeeper: 'Housekeeper',
  };
  return labels[type] || type;
};

const getTypeColor = (type) => {
  const colors = {
    caregiver: 'success',
    housekeeper: 'info',
  };
  return colors[type] || 'grey';
};

const getStatusLabel = (caregiver) => {
  if (caregiver.status === 'suspended') return 'Suspended';
  if (caregiver.status === 'pending') return 'Pending';
  return caregiver.is_available ? 'Available' : 'Busy';
};

const getStatusClass = (caregiver) => {
  if (caregiver.status === 'suspended') return 'status-suspended';
  if (caregiver.status === 'pending') return 'status-pending';
  return caregiver.is_available ? 'status-available' : 'status-busy';
};

// Watch for view events
watch(() => props.caregivers, () => {
  // Update selected caregiver if it exists
  if (selectedCaregiver.value) {
    const updated = props.caregivers.find(c => c.id === selectedCaregiver.value.id);
    if (updated) selectedCaregiver.value = updated;
  }
});
</script>

<style scoped>
.admin-staff-caregivers {
  width: 100%;
}

.filter-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
}

.stat-card {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.stat-label {
  font-size: 0.75rem;
  color: #4b5563;
}

.bg-primary-light { background-color: rgba(59, 130, 246, 0.1) !important; }
.bg-success-light { background-color: rgba(16, 185, 129, 0.1) !important; }
.bg-info-light { background-color: rgba(6, 182, 212, 0.1) !important; }
.bg-warning-light { background-color: rgba(245, 158, 11, 0.1) !important; }

.caregiver-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  overflow: visible;
}

.caregiver-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.status-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-available {
  background-color: #dcfce7;
  color: #166534;
}

.status-busy {
  background-color: #fef3c7;
  color: #92400e;
}

.status-pending {
  background-color: #e0e7ff;
  color: #3730a3;
}

.status-suspended {
  background-color: #fee2e2;
  color: #991b1b;
}

.caregiver-name {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
}

.caregiver-info {
  background: #f9fafb;
  border-radius: 8px;
  padding: 12px;
}

.info-row {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8125rem;
  color: #4b5563;
  margin-bottom: 6px;
}

.info-row:last-child {
  margin-bottom: 0;
}

.availability-section {
  text-align: center;
}

.availability-text {
  font-size: 0.75rem;
  color: #4b5563;
}

.empty-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
}

.detail-label {
  font-size: 0.75rem;
  color: #4b5563;
  margin-bottom: 4px;
}

.detail-value {
  font-size: 0.875rem;
  font-weight: 500;
  color: #1f2937;
}

.text-muted {
  color: #4b5563 !important;
}

.gap-2 {
  gap: 8px;
}
</style>
