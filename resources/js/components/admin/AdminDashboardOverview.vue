<template>
  <div class="admin-dashboard-overview">
    <!-- Stats Row -->
    <v-row class="mb-4">
      <v-col v-for="(stat, index) in stats" :key="stat.title" cols="6" sm="6" md="3">
        <stat-card 
          :icon="stat.icon" 
          :value="stat.value" 
          :label="stat.title" 
          :change="stat.change" 
          :change-color="stat.changeColor" 
          :change-icon="stat.changeIcon" 
          icon-class="error"
          :stagger-index="index"
        />
      </v-col>
    </v-row>

    <v-row class="mt-1">
      <v-col cols="12">
        <v-row>
          <!-- Staff Overview Card -->
          <v-col cols="12" md="4">
            <v-card class="mb-3 compact-card d-flex flex-column" elevation="0">
              <v-card-title class="card-header pa-4">
                <span class="section-title-compact error--text">Staff Overview</span>
              </v-card-title>
              <v-card-text class="pa-4 flex-grow-1 d-flex flex-column justify-space-between">
                <div>
                  <div class="mb-3">
                    <div class="d-flex justify-space-between align-center mb-1">
                      <div class="d-flex align-center">
                        <v-icon color="success" size="18" class="mr-2">mdi-account-heart</v-icon>
                        <span class="summary-label-compact">Caregivers</span>
                      </div>
                      <span class="summary-value-compact success--text">{{ caregiversCount }}</span>
                    </div>
                  </div>
                  <div class="mb-3">
                    <div class="d-flex justify-space-between align-center mb-1">
                      <div class="d-flex align-center">
                        <v-icon color="purple" size="18" class="mr-2">mdi-broom</v-icon>
                        <span class="summary-label-compact">Housekeepers</span>
                      </div>
                      <span class="summary-value-compact" style="color: #6A1B9A;">{{ housekeepersCount }}</span>
                    </div>
                  </div>
                  <div class="mb-3">
                    <div class="d-flex justify-space-between align-center mb-1">
                      <div class="d-flex align-center">
                        <v-icon color="info" size="18" class="mr-2">mdi-account-group</v-icon>
                        <span class="summary-label-compact">Clients</span>
                      </div>
                      <span class="summary-value-compact info--text">{{ clientsCount }}</span>
                    </div>
                  </div>
                  <div class="mb-3">
                    <div class="d-flex justify-space-between align-center mb-1">
                      <div class="d-flex align-center">
                        <v-icon color="warning" size="18" class="mr-2">mdi-file-document-outline</v-icon>
                        <span class="summary-label-compact">Pending Applications</span>
                      </div>
                      <span class="summary-value-compact warning--text">{{ pendingApplicationsCount }}</span>
                    </div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Booking Status Card -->
          <v-col cols="12" md="4">
            <v-card elevation="0" class="mb-3 compact-card d-flex flex-column">
              <v-card-title class="card-header pa-4">
                <span class="section-title-compact error--text">Booking Status</span>
              </v-card-title>
              <v-card-text class="pa-4 flex-grow-1">
                <v-row class="metric-grid">
                  <v-col cols="6" class="pa-2">
                    <div class="metric-card">
                      <div class="d-flex align-center mb-2">
                        <v-icon color="warning" size="20" class="mr-2">mdi-clock-outline</v-icon>
                        <span class="metric-label-grid">Pending</span>
                      </div>
                      <div class="metric-value-grid warning--text">{{ bookingStats.pending }}</div>
                      <div class="metric-change-grid text-grey">Awaiting</div>
                    </div>
                  </v-col>
                  <v-col cols="6" class="pa-2">
                    <div class="metric-card">
                      <div class="d-flex align-center mb-2">
                        <v-icon color="info" size="20" class="mr-2">mdi-calendar-clock</v-icon>
                        <span class="metric-label-grid">Active</span>
                      </div>
                      <div class="metric-value-grid info--text">{{ bookingStats.active }}</div>
                      <div class="metric-change-grid text-grey">In Progress</div>
                    </div>
                  </v-col>
                  <v-col cols="6" class="pa-2">
                    <div class="metric-card">
                      <div class="d-flex align-center mb-2">
                        <v-icon color="success" size="20" class="mr-2">mdi-check-circle</v-icon>
                        <span class="metric-label-grid">Completed</span>
                      </div>
                      <div class="metric-value-grid success--text">{{ bookingStats.completed }}</div>
                      <div class="metric-change-grid text-grey">Done</div>
                    </div>
                  </v-col>
                  <v-col cols="6" class="pa-2">
                    <div class="metric-card">
                      <div class="d-flex align-center mb-2">
                        <v-icon color="error" size="20" class="mr-2">mdi-close-circle</v-icon>
                        <span class="metric-label-grid">Cancelled</span>
                      </div>
                      <div class="metric-value-grid error--text">{{ bookingStats.cancelled }}</div>
                      <div class="metric-change-grid text-grey">Closed</div>
                    </div>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Caregiver Contacts Card -->
          <v-col cols="12" md="4">
            <v-card elevation="0" class="mb-3 compact-card d-flex flex-column">
              <v-card-title class="card-header pa-4">
                <div class="d-flex justify-space-between align-center">
                  <span class="section-title-compact error--text">Caregiver Contacts</span>
                  <v-btn size="small" color="error" variant="flat" prepend-icon="mdi-eye" @click="$emit('view-all-caregivers')">View All</v-btn>
                </div>
              </v-card-title>
              <v-card-text class="pa-4 flex-grow-1">
                <div class="caregiver-contacts">
                  <div v-for="caregiver in quickCaregivers" :key="caregiver.id" class="caregiver-contact-item">
                    <div class="d-flex align-center mb-2">
                      <v-avatar size="32" :color="caregiver.available ? 'success' : 'grey'" class="mr-3">
                        <span class="text-white font-weight-bold">{{ caregiver.initials }}</span>
                      </v-avatar>
                      <div class="flex-grow-1">
                        <div class="caregiver-name">{{ caregiver.name }}</div>
                        <div class="caregiver-status" :class="caregiver.available ? 'success--text' : 'grey--text'">
                          {{ caregiver.available ? 'Available' : 'Busy' }}
                        </div>
                      </div>
                      <div class="caregiver-phone">{{ caregiver.phone }}</div>
                    </div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <!-- Quick Actions Card -->
        <v-card elevation="0" class="modern-activity-card">
          <v-card-title class="modern-card-header pa-6">
            <div class="d-flex align-center justify-space-between flex-wrap ga-2">
              <div class="d-flex align-center">
                <v-icon color="error" size="20" class="mr-3">mdi-lightning-bolt</v-icon>
                <span class="modern-title error--text">Quick Actions</span>
              </div>
            </div>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-4">
            <v-row>
              <v-col cols="6" sm="4" md="2">
                <v-btn 
                  variant="tonal" 
                  color="success" 
                  block 
                  class="quick-action-btn py-6"
                  @click="$emit('navigate', 'caregivers')"
                >
                  <div class="d-flex flex-column align-center">
                    <v-icon size="28" class="mb-2">mdi-account-heart</v-icon>
                    <span class="text-caption font-weight-medium">Caregivers</span>
                    <span class="text-h6 font-weight-bold">{{ caregiversCount }}</span>
                  </div>
                </v-btn>
              </v-col>
              <v-col cols="6" sm="4" md="2">
                <v-btn 
                  variant="tonal" 
                  color="purple" 
                  block 
                  class="quick-action-btn py-6"
                  @click="$emit('navigate', 'housekeepers')"
                >
                  <div class="d-flex flex-column align-center">
                    <v-icon size="28" class="mb-2">mdi-broom</v-icon>
                    <span class="text-caption font-weight-medium">Housekeepers</span>
                    <span class="text-h6 font-weight-bold">{{ housekeepersCount }}</span>
                  </div>
                </v-btn>
              </v-col>
              <v-col cols="6" sm="4" md="2">
                <v-btn 
                  variant="tonal" 
                  color="info" 
                  block 
                  class="quick-action-btn py-6"
                  @click="$emit('navigate', 'clients')"
                >
                  <div class="d-flex flex-column align-center">
                    <v-icon size="28" class="mb-2">mdi-account-group</v-icon>
                    <span class="text-caption font-weight-medium">Clients</span>
                    <span class="text-h6 font-weight-bold">{{ clientsCount }}</span>
                  </div>
                </v-btn>
              </v-col>
              <v-col cols="6" sm="4" md="2">
                <v-btn 
                  variant="tonal" 
                  color="warning" 
                  block 
                  class="quick-action-btn py-6"
                  @click="$emit('navigate', 'pending')"
                >
                  <div class="d-flex flex-column align-center">
                    <v-icon size="28" class="mb-2">mdi-file-document-outline</v-icon>
                    <span class="text-caption font-weight-medium">Applications</span>
                    <span class="text-h6 font-weight-bold">{{ pendingApplicationsCount }}</span>
                  </div>
                </v-btn>
              </v-col>
              <v-col cols="6" sm="4" md="2">
                <v-btn 
                  variant="tonal" 
                  color="error" 
                  block 
                  class="quick-action-btn py-6"
                  @click="$emit('navigate', 'client-bookings')"
                >
                  <div class="d-flex flex-column align-center">
                    <v-icon size="28" class="mb-2">mdi-calendar-check</v-icon>
                    <span class="text-caption font-weight-medium">Bookings</span>
                    <span class="text-h6 font-weight-bold">{{ bookingStats.active }}</span>
                  </div>
                </v-btn>
              </v-col>
              <v-col cols="6" sm="4" md="2">
                <v-btn 
                  variant="tonal" 
                  color="teal" 
                  block 
                  class="quick-action-btn py-6"
                  @click="$emit('navigate', 'reviews')"
                >
                  <div class="d-flex flex-column align-center">
                    <v-icon size="28" class="mb-2">mdi-star</v-icon>
                    <span class="text-caption font-weight-medium">Reviews</span>
                    <span class="text-h6 font-weight-bold">{{ reviewsCount }}</span>
                  </div>
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <!-- Booking Maintenance Mode Widget -->
        <v-card elevation="0" class="modern-activity-card mt-4">
          <v-card-title class="modern-card-header pa-6">
            <div class="d-flex align-center justify-space-between flex-wrap ga-2">
              <div class="d-flex align-center">
                <v-icon :color="bookingMaintenanceEnabled ? 'warning' : 'success'" size="20" class="mr-3">
                  {{ bookingMaintenanceEnabled ? 'mdi-wrench' : 'mdi-check-circle' }}
                </v-icon>
                <span class="modern-title" :class="bookingMaintenanceEnabled ? 'warning--text' : 'success--text'">
                  Booking System Status
                </span>
              </div>
              <v-chip 
                :color="bookingMaintenanceEnabled ? 'warning' : 'success'" 
                size="small"
                class="font-weight-bold"
              >
                {{ bookingMaintenanceEnabled ? 'Maintenance Mode' : 'Active' }}
              </v-chip>
            </div>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-6 booking-maintenance-card-body">
            <v-row align="center">
              <v-col cols="12" md="8" class="booking-maintenance-text-col">
                <div class="d-flex align-center mb-3">
                  <v-icon :color="bookingMaintenanceEnabled ? 'warning' : 'success'" size="24" class="mr-3 flex-shrink-0">
                    {{ bookingMaintenanceEnabled ? 'mdi-alert-circle' : 'mdi-calendar-check' }}
                  </v-icon>
                  <div class="min-width-0">
                    <div class="text-subtitle-1 font-weight-bold">
                      {{ bookingMaintenanceEnabled ? 'Booking System Disabled' : 'Booking System Active' }}
                    </div>
                    <div class="text-body-2 text-grey">
                      {{ bookingMaintenanceEnabled 
                        ? 'New bookings are currently blocked. Existing bookings are not affected.' 
                        : 'Clients can book services normally.' 
                      }}
                    </div>
                  </div>
                </div>
                <v-text-field
                  v-if="bookingMaintenanceEnabled || showMaintenanceMessageField"
                  v-model="localMaintenanceMessage"
                  label="Maintenance Message (shown to clients)"
                  placeholder="Our booking system is currently under maintenance. Please try again later."
                  variant="outlined"
                  density="compact"
                  class="mt-3"
                  :disabled="togglingMaintenance"
                  @update:model-value="$emit('update:bookingMaintenanceMessage', $event)"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="4" class="booking-maintenance-actions-col">
                <div class="booking-maintenance-actions">
                  <v-btn
                    :color="bookingMaintenanceEnabled ? 'success' : 'warning'"
                    size="large"
                    :prepend-icon="bookingMaintenanceEnabled ? 'mdi-play-circle' : 'mdi-pause-circle'"
                    @click="$emit('toggle-maintenance')"
                    :loading="togglingMaintenance"
                    class="booking-maintenance-btn"
                    block
                  >
                    {{ bookingMaintenanceEnabled ? 'Enable Booking' : 'Disable Booking' }}
                  </v-btn>
                  <div class="text-caption text-grey mt-2 text-center">
                    {{ bookingMaintenanceEnabled 
                      ? 'Click to allow new bookings' 
                      : 'Click to enable maintenance mode' 
                    }}
                  </div>
                </div>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import StatCard from '@/components/shared/StatCard.vue';

// Props
const props = defineProps({
  stats: {
    type: Array,
    required: true,
    default: () => []
  },
  caregiversCount: {
    type: Number,
    default: 0
  },
  housekeepersCount: {
    type: Number,
    default: 0
  },
  clientsCount: {
    type: Number,
    default: 0
  },
  pendingApplicationsCount: {
    type: Number,
    default: 0
  },
  bookingStats: {
    type: Object,
    default: () => ({ pending: 0, active: 0, completed: 0, cancelled: 0 })
  },
  quickCaregivers: {
    type: Array,
    default: () => []
  },
  reviewsCount: {
    type: Number,
    default: 0
  },
  bookingMaintenanceEnabled: {
    type: Boolean,
    default: false
  },
  bookingMaintenanceMessage: {
    type: String,
    default: ''
  },
  togglingMaintenance: {
    type: Boolean,
    default: false
  },
  showMaintenanceMessageField: {
    type: Boolean,
    default: false
  }
});

// Emits
defineEmits([
  'navigate',
  'view-all-caregivers',
  'toggle-maintenance',
  'update:bookingMaintenanceMessage'
]);

// Local state for v-model binding
const localMaintenanceMessage = ref(props.bookingMaintenanceMessage);

// Sync with prop changes
watch(() => props.bookingMaintenanceMessage, (newVal) => {
  localMaintenanceMessage.value = newVal;
});
</script>

<style scoped>
.admin-dashboard-overview {
  width: 100%;
}

.quick-action-btn {
  min-height: 100px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.quick-action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.caregiver-contact-item {
  padding: 8px 0;
  border-bottom: 1px solid #f3f4f6;
}

.caregiver-contact-item:last-child {
  border-bottom: none;
}

.caregiver-name {
  font-weight: 600;
  font-size: 0.875rem;
  color: #1e293b;
}

.caregiver-status {
  font-size: 0.75rem;
}

.caregiver-phone {
  font-size: 0.75rem;
  color: #64748b;
}

.metric-card {
  padding: 12px;
  border-radius: 8px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}

.metric-label-grid {
  font-size: 0.75rem;
  font-weight: 500;
  color: #64748b;
}

.metric-value-grid {
  font-size: 1.5rem;
  font-weight: 700;
}

.metric-change-grid {
  font-size: 0.7rem;
}

.summary-label-compact {
  font-size: 0.875rem;
  color: #475569;
}

.summary-value-compact {
  font-size: 1.25rem;
  font-weight: 700;
}

.section-title-compact {
  font-size: 1rem;
  font-weight: 600;
}

.modern-title {
  font-size: 1rem;
  font-weight: 600;
}

.booking-maintenance-card-body .booking-maintenance-actions-col {
  display: flex;
  align-items: center;
  justify-content: flex-end;
}

.booking-maintenance-actions {
  width: 100%;
  max-width: 280px;
}

.booking-maintenance-btn {
  min-width: 160px;
  font-weight: 600;
  letter-spacing: 0.02em;
  text-transform: none;
}

@media (max-width: 960px) {
  .booking-maintenance-card-body .booking-maintenance-actions-col {
    justify-content: stretch;
    margin-top: 8px;
  }
  .booking-maintenance-actions {
    max-width: none;
    width: 100%;
  }
  .booking-maintenance-btn {
    width: 100%;
  }
  .booking-maintenance-actions .text-caption {
    text-align: center !important;
  }
}

@media (max-width: 600px) {
  .quick-action-btn {
    min-height: 80px;
    padding: 16px 8px;
  }
}
</style>
