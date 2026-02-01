<template>
  <div class="dashboard-stats">
    <!-- Stats Cards Row -->
    <v-row>
      <v-col cols="12" sm="6" md="3" v-for="stat in stats" :key="stat.title">
        <v-card elevation="0" class="stat-card" :class="stat.colorClass">
          <v-card-text class="pa-4 pa-sm-6">
            <div class="d-flex align-center justify-space-between">
              <div>
                <p class="stat-label">{{ stat.title }}</p>
                <h2 class="stat-value">{{ stat.value }}</h2>
                <p class="stat-change" :class="stat.positive ? 'text-success' : 'text-error'" v-if="stat.change">
                  <v-icon size="14">{{ stat.positive ? 'mdi-arrow-up' : 'mdi-arrow-down' }}</v-icon>
                  {{ stat.change }}
                </p>
              </div>
              <v-avatar size="56" :color="stat.iconColor" class="stat-icon-wrapper">
                <v-icon size="28" color="white">{{ stat.icon }}</v-icon>
              </v-avatar>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Quick Actions -->
    <v-row class="mt-4" v-if="showActions">
      <v-col cols="12">
        <v-card elevation="0" class="quick-actions-card">
          <v-card-title class="pa-4 pa-sm-6">
            <span class="text-h6 font-weight-bold">Quick Actions</span>
          </v-card-title>
          <v-card-text class="pa-4 pa-sm-6 pt-0">
            <div class="d-flex flex-wrap gap-3">
              <slot name="actions">
                <v-btn 
                  v-for="action in defaultActions" 
                  :key="action.label"
                  :color="action.color" 
                  variant="tonal" 
                  size="large"
                  :prepend-icon="action.icon"
                  @click="$emit('action', action.key)"
                >
                  {{ action.label }}
                </v-btn>
              </slot>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Activity -->
    <v-row class="mt-4" v-if="activities.length > 0">
      <v-col cols="12" md="8">
        <v-card elevation="0" class="activity-card">
          <v-card-title class="pa-4 pa-sm-6 d-flex justify-space-between align-center">
            <span class="text-h6 font-weight-bold">Recent Activity</span>
            <v-btn variant="text" color="primary" size="small" @click="$emit('view-all-activity')">
              View All
            </v-btn>
          </v-card-title>
          <v-card-text class="pa-0">
            <v-list class="activity-list">
              <v-list-item 
                v-for="(activity, index) in activities" 
                :key="index"
                :class="index !== activities.length - 1 ? 'border-b' : ''"
              >
                <template v-slot:prepend>
                  <v-avatar :color="activity.iconColor || 'primary'" size="40" variant="tonal">
                    <v-icon size="20">{{ activity.icon }}</v-icon>
                  </v-avatar>
                </template>
                <v-list-item-title class="font-weight-medium">
                  {{ activity.title }}
                </v-list-item-title>
                <v-list-item-subtitle>
                  {{ activity.description }}
                </v-list-item-subtitle>
                <template v-slot:append>
                  <span class="text-caption text-grey">{{ activity.time }}</span>
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Upcoming Events -->
      <v-col cols="12" md="4" v-if="upcomingEvents.length > 0">
        <v-card elevation="0" class="events-card">
          <v-card-title class="pa-4 pa-sm-6">
            <span class="text-h6 font-weight-bold">Upcoming</span>
          </v-card-title>
          <v-card-text class="pa-0">
            <v-list class="events-list">
              <v-list-item 
                v-for="(event, index) in upcomingEvents" 
                :key="index"
                class="py-3"
              >
                <template v-slot:prepend>
                  <div class="event-date text-center mr-3">
                    <div class="event-day">{{ event.day }}</div>
                    <div class="event-month">{{ event.month }}</div>
                  </div>
                </template>
                <v-list-item-title class="font-weight-medium">
                  {{ event.title }}
                </v-list-item-title>
                <v-list-item-subtitle>
                  {{ event.time }}
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  stats: {
    type: Array,
    default: () => [],
    // Example: [{ title: 'Total Bookings', value: '24', icon: 'mdi-calendar', iconColor: 'primary', change: '+12%', positive: true }]
  },
  activities: {
    type: Array,
    default: () => [],
    // Example: [{ title: 'Booking Confirmed', description: 'Your booking #123 was confirmed', time: '2h ago', icon: 'mdi-check', iconColor: 'success' }]
  },
  upcomingEvents: {
    type: Array,
    default: () => [],
    // Example: [{ day: '15', month: 'Jan', title: 'Caregiver Visit', time: '9:00 AM' }]
  },
  showActions: {
    type: Boolean,
    default: true
  },
  userType: {
    type: String,
    default: 'client'
  }
});

const emit = defineEmits(['action', 'view-all-activity']);

const defaultActions = computed(() => {
  const actions = {
    client: [
      { key: 'book', label: 'Book Service', icon: 'mdi-plus', color: 'primary' },
      { key: 'bookings', label: 'My Bookings', icon: 'mdi-calendar', color: 'info' },
      { key: 'payment', label: 'Payment', icon: 'mdi-credit-card', color: 'success' },
    ],
    caregiver: [
      { key: 'schedule', label: 'My Schedule', icon: 'mdi-calendar', color: 'primary' },
      { key: 'assignments', label: 'Assignments', icon: 'mdi-account-group', color: 'info' },
      { key: 'earnings', label: 'Earnings', icon: 'mdi-cash', color: 'success' },
    ],
    admin: [
      { key: 'bookings', label: 'Manage Bookings', icon: 'mdi-calendar', color: 'primary' },
      { key: 'users', label: 'Manage Users', icon: 'mdi-account-group', color: 'info' },
      { key: 'reports', label: 'Reports', icon: 'mdi-chart-bar', color: 'success' },
    ]
  };
  return actions[props.userType] || actions.client;
});
</script>

<style scoped>
.stat-card {
  border-radius: 16px;
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border: 1px solid #e2e8f0;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}

.stat-label {
  font-size: 0.875rem;
  color: #64748b;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.stat-change {
  font-size: 0.75rem;
  margin: 0;
  margin-top: 0.25rem;
}

.stat-icon-wrapper {
  flex-shrink: 0;
}

.quick-actions-card,
.activity-card,
.events-card {
  border-radius: 16px;
  border: 1px solid #e2e8f0;
}

.activity-list,
.events-list {
  padding: 0;
}

.border-b {
  border-bottom: 1px solid #f1f5f9;
}

.event-date {
  min-width: 48px;
  padding: 0.5rem;
  background: #f1f5f9;
  border-radius: 8px;
}

.event-day {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  line-height: 1;
}

.event-month {
  font-size: 0.75rem;
  color: #64748b;
  text-transform: uppercase;
}

@media (max-width: 600px) {
  .stat-value {
    font-size: 1.5rem;
  }

  .stat-card {
    margin-bottom: 0.5rem;
  }

  .quick-actions-card .d-flex {
    flex-direction: column;
  }

  .quick-actions-card .v-btn {
    width: 100%;
    justify-content: flex-start;
  }
}
</style>
