<template>
  <div class="admin-bookings-management">
    <!-- Filters & Actions -->
    <div class="mb-6">
      <v-row class="align-center">
        <v-col cols="12" md="4">
          <v-text-field 
            v-model="searchQuery" 
            placeholder="Search bookings..." 
            prepend-inner-icon="mdi-magnify" 
            variant="outlined" 
            density="compact" 
            hide-details
            @update:model-value="$emit('search', searchQuery)"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-select 
            v-model="statusFilter" 
            :items="statusOptions" 
            variant="outlined" 
            density="compact" 
            hide-details
            @update:model-value="$emit('filter-status', statusFilter)"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-select 
            v-model="dateFilter" 
            :items="dateOptions" 
            variant="outlined" 
            density="compact" 
            hide-details
            @update:model-value="$emit('filter-date', dateFilter)"
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-btn color="error" prepend-icon="mdi-plus" @click="$emit('add')">
            Add Booking
          </v-btn>
        </v-col>
      </v-row>
    </div>

    <v-card elevation="0">
      <v-card-title class="card-header pa-6 pa-md-8 d-flex justify-space-between align-center">
        <span class="section-title error--text">Client Bookings</span>
        <v-chip color="primary" size="small">
          {{ bookings.length }} Total
        </v-chip>
      </v-card-title>

      <!-- Desktop Table View -->
      <v-data-table 
        v-if="!isMobile"
        :headers="headers" 
        :items="bookings" 
        :items-per-page="10" 
        :items-per-page-options="[10, 25, 50, -1]" 
        class="elevation-0 admin-bookings-table" 
        density="compact"
      >
        <template v-slot:item.formattedPrice="{ item }">
          <div class="price-cell">
            <span 
              v-if="item.referralDiscountApplied && item.referralDiscountApplied > 0" 
              class="original-price-strike"
            >
              ${{ ((item.hoursPerDay * item.durationDays * (parseFloat(item.hourlyRate) + parseFloat(item.referralDiscountApplied)))).toLocaleString() }}
            </span>
            <span class="current-price">{{ item.formattedPrice }}</span>
          </div>
        </template>
        
        <template v-slot:item.status="{ item }">
          <v-chip 
            :color="getStatusColor(item.status)" 
            size="small" 
            class="font-weight-bold" 
            :prepend-icon="getStatusIcon(item.status)"
          >
            {{ item.status }}
          </v-chip>
        </template>
        
        <template v-slot:item.paymentStatus="{ item }">
          <v-chip 
            :color="item.paymentStatus === 'paid' ? 'success' : 'warning'" 
            size="small" 
            class="font-weight-bold" 
            :prepend-icon="item.paymentStatus === 'paid' ? 'mdi-check-circle' : 'mdi-clock-outline'"
          >
            {{ item.paymentStatus === 'paid' ? 'Paid' : 'Unpaid' }}
          </v-chip>
        </template>
        
        <template v-slot:item.assignmentStatus="{ item }">
          <v-chip 
            :color="getAssignmentStatusColor(item.assignmentStatus)" 
            size="small" 
            class="font-weight-bold" 
            :prepend-icon="getStatusIcon(item.assignmentStatus)"
          >
            {{ item.assignmentStatus }}
          </v-chip>
        </template>
        
        <template v-slot:item.coverageEnd="{ item }">
          <div class="d-flex align-center">
            <span :class="item.isActive ? 'success--text font-weight-bold' : ''">
              {{ item.coverageEnd }}
            </span>
            <span 
              v-if="item.isActive" 
              class="ml-2 active-indicator"
            ></span>
          </div>
        </template>
        
        <template v-slot:item.assignedCount="{ item }">
          <div class="assignment-progress">
            <div class="progress-text">{{ item.assignedCount }} / {{ item.caregiversNeeded }}</div>
            <v-progress-linear 
              :model-value="(item.assignedCount / item.caregiversNeeded) * 100" 
              :color="getProgressColor(item.assignedCount, item.caregiversNeeded)" 
              height="6" 
              rounded
              class="mt-1"
            />
          </div>
        </template>
        
        <template v-slot:item.actions="{ item }">
          <div class="action-buttons">
            <v-btn 
              v-if="item.status === 'pending'" 
              class="action-btn-approve" 
              icon="mdi-check" 
              size="small" 
              @click="$emit('approve', item)"
            ></v-btn>
            <v-btn 
              v-if="item.status === 'pending'" 
              class="action-btn-reject" 
              icon="mdi-close" 
              size="small" 
              @click="$emit('reject', item)"
            ></v-btn>
            <v-btn 
              class="action-btn-view" 
              icon="mdi-eye" 
              size="small" 
              title="View Booking Details" 
              @click="$emit('view', item)"
            ></v-btn>
            <v-btn
              v-if="isApproved(item) && !isHousekeeping(item)"
              class="action-btn-caregivers"
              icon="mdi-account-group"
              size="small"
              title="View Assigned Caregivers"
              @click="$emit('view-assigned', item)"
            ></v-btn>
            <v-btn
              v-if="isApproved(item) && isHousekeeping(item)"
              class="action-btn-view-assigned"
              icon="mdi-account-eye"
              size="small"
              title="View Assigned Housekeepers"
              @click="$emit('view-housekeepers', item)"
            ></v-btn>
            <v-btn
              v-if="isApproved(item)"
              class="action-btn-assign"
              icon="mdi-account-plus"
              size="small"
              :title="isHousekeeping(item) ? 'Assign Housekeepers' : 'Assign Caregivers'"
              @click="$emit('assign', item)"
            ></v-btn>
          </div>
        </template>
      </v-data-table>

      <!-- Mobile Card View -->
      <div v-else class="mobile-cards-container pa-3">
        <div v-if="bookings.length === 0" class="text-center py-8 text-grey">
          No bookings found
        </div>
        <v-card 
          v-for="item in bookings" 
          :key="item.id" 
          class="mobile-data-card mb-3" 
          elevation="2" 
          rounded="lg"
        >
          <v-card-text class="pa-0">
            <div class="mobile-card-header d-flex align-center justify-space-between pa-3" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);">
              <span class="text-white font-weight-bold text-body-1">{{ item.client }}</span>
              <v-chip :color="getStatusColor(item.status)" size="small" class="font-weight-bold">
                {{ item.status }}
              </v-chip>
            </div>
            <div class="mobile-card-body pa-3">
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Service:</span>
                <span class="mobile-card-value">{{ item.service }}</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Date:</span>
                <span class="mobile-card-value">{{ item.date }}</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Price:</span>
                <span class="mobile-card-value font-weight-bold">{{ item.formattedPrice }}</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between align-center py-2">
                <span class="mobile-card-label text-grey-darken-1">Payment:</span>
                <v-chip 
                  :color="item.paymentStatus === 'paid' ? 'success' : 'warning'" 
                  size="x-small"
                >
                  {{ item.paymentStatus === 'paid' ? 'Paid' : 'Unpaid' }}
                </v-chip>
              </div>
            </div>
            <div class="mobile-card-actions d-flex justify-center gap-2 pa-3" style="background: #f9fafb; border-top: 1px solid #e5e7eb;">
              <v-btn 
                v-if="item.status === 'pending'"
                color="success" 
                variant="tonal" 
                size="small" 
                icon="mdi-check"
                @click="$emit('approve', item)"
              ></v-btn>
              <v-btn 
                v-if="item.status === 'pending'"
                color="error" 
                variant="tonal" 
                size="small" 
                icon="mdi-close"
                @click="$emit('reject', item)"
              ></v-btn>
              <v-btn 
                color="primary" 
                variant="tonal" 
                size="small" 
                prepend-icon="mdi-eye" 
                @click="$emit('view', item)"
              >
                View
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </div>
    </v-card>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useDisplay } from 'vuetify';

const props = defineProps({
  bookings: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits([
  'add', 
  'view', 
  'approve', 
  'reject', 
  'assign', 
  'view-assigned', 
  'view-housekeepers',
  'search', 
  'filter-status', 
  'filter-date'
]);

const { smAndDown } = useDisplay();
const isMobile = computed(() => smAndDown.value);

const searchQuery = ref('');
const statusFilter = ref('All');
const dateFilter = ref('All Time');

const statusOptions = ['All', 'Pending', 'Approved', 'Rejected'];
const dateOptions = ['All Time', 'Today', 'This Week', 'This Month'];

const headers = [
  { title: 'Client', key: 'client' },
  { title: 'Service', key: 'service' },
  { title: 'Date', key: 'date' },
  { title: 'Price', key: 'formattedPrice' },
  { title: 'Status', key: 'status' },
  { title: 'Payment', key: 'paymentStatus' },
  { title: 'Assignment', key: 'assignedCount' },
  { title: 'Coverage End', key: 'coverageEnd' },
  { title: 'Actions', key: 'actions', sortable: false }
];

const getStatusColor = (status) => {
  const colors = {
    'pending': 'warning',
    'Pending': 'warning',
    'approved': 'success',
    'Approved': 'success',
    'confirmed': 'success',
    'Confirmed': 'success',
    'rejected': 'error',
    'Rejected': 'error',
    'cancelled': 'grey',
    'Cancelled': 'grey'
  };
  return colors[status] || 'grey';
};

const getStatusIcon = (status) => {
  const icons = {
    'pending': 'mdi-clock-outline',
    'Pending': 'mdi-clock-outline',
    'approved': 'mdi-check-circle',
    'Approved': 'mdi-check-circle',
    'confirmed': 'mdi-check-circle',
    'rejected': 'mdi-close-circle',
    'Rejected': 'mdi-close-circle'
  };
  return icons[status] || 'mdi-help-circle';
};

const getAssignmentStatusColor = (status) => {
  const colors = {
    'complete': 'success',
    'Complete': 'success',
    'partial': 'warning',
    'Partial': 'warning',
    'none': 'error',
    'None': 'error'
  };
  return colors[status] || 'grey';
};

const getProgressColor = (assigned, needed) => {
  if (assigned >= needed) return 'success';
  if (assigned > 0) return 'warning';
  return 'error';
};

const isApproved = (item) => {
  return item.status === 'approved' || item.status === 'confirmed';
};

const isHousekeeping = (item) => {
  const service = String(item.service || item.service_type || '').toLowerCase();
  return service.includes('housekeeping');
};
</script>

<style scoped>
.admin-bookings-management {
  width: 100%;
}

.card-header {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-bottom: 1px solid #e2e8f0;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
}

.action-buttons {
  display: flex;
  gap: 0.25rem;
}

.action-btn-approve {
  color: #16a34a !important;
}

.action-btn-reject {
  color: #dc2626 !important;
}

.action-btn-view {
  color: #2563eb !important;
}

.action-btn-caregivers {
  color: #7c3aed !important;
}

.action-btn-assign {
  color: #0891b2 !important;
}

.price-cell {
  display: flex;
  flex-direction: column;
}

.original-price-strike {
  text-decoration: line-through;
  color: #9ca3af;
  font-size: 0.75rem;
}

.current-price {
  font-weight: 600;
  color: #16a34a;
}

.assignment-progress {
  min-width: 80px;
}

.progress-text {
  font-size: 0.75rem;
  font-weight: 500;
  text-align: center;
}

.active-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #4caf50;
  display: inline-block;
}

.mobile-data-card {
  border-radius: 12px;
  overflow: hidden;
}

.mobile-card-label {
  font-size: 0.875rem;
  font-weight: 500;
}

.mobile-card-value {
  font-size: 0.875rem;
  color: #1e293b;
}

@media (max-width: 768px) {
  .card-header {
    padding: 1rem !important;
  }
}
</style>
