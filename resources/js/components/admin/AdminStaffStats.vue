<template>
  <div class="admin-staff-stats">
    <!-- Stats Cards Row -->
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

    <!-- Overview Cards Row -->
    <v-row class="mt-1">
      <!-- System Overview -->
      <v-col cols="12" md="4">
        <v-card class="mb-3 compact-card d-flex flex-column" elevation="0">
          <v-card-title class="card-header pa-4">
            <span class="section-title-compact error--text">System Overview</span>
          </v-card-title>
          <v-card-text class="pa-4 flex-grow-1 d-flex flex-column justify-space-between">
            <div>
              <div class="mb-3">
                <div class="d-flex justify-space-between mb-1">
                  <span class="summary-label-compact">Active Users</span>
                  <span class="summary-value-compact error--text">{{ stats[0]?.value || 0 }}</span>
                </div>
                <v-progress-linear :model-value="userProgress" color="error" height="6" rounded />
                <div class="text-caption text-muted mt-1">{{ userGrowth }}</div>
              </div>
              <div class="mb-3">
                <div class="d-flex justify-space-between mb-1">
                  <span class="summary-label-compact">Server Load</span>
                  <span class="summary-value-compact">{{ serverLoad }}%</span>
                </div>
                <v-progress-linear :model-value="serverLoad" :color="serverLoadColor" height="6" rounded />
                <div class="text-caption text-muted mt-1">{{ serverLoadStatus }}</div>
              </div>
              <div class="mb-3">
                <div class="d-flex justify-space-between mb-1">
                  <span class="summary-label-compact">Revenue Goal</span>
                  <span class="summary-value-compact">{{ stats[2]?.value || '$0' }}</span>
                </div>
                <v-progress-linear :model-value="revenueProgress" color="success" height="6" rounded />
                <div class="text-caption text-muted mt-1">Target: ${{ revenueTarget }}/month</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Platform Metrics -->
      <v-col cols="12" md="4">
        <v-card elevation="0" class="mb-3 compact-card d-flex flex-column">
          <v-card-title class="card-header pa-4">
            <span class="section-title-compact error--text">Platform Metrics</span>
          </v-card-title>
          <v-card-text class="pa-4 flex-grow-1">
            <v-row class="metric-grid">
              <v-col v-for="metric in platformMetrics" :key="metric.label" cols="6" class="pa-2">
                <div class="metric-item">
                  <v-icon :color="metric.color" size="20" class="mb-1">{{ metric.icon }}</v-icon>
                  <div class="metric-value">{{ metric.value }}</div>
                  <div class="metric-label">{{ metric.label }}</div>
                </div>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Quick Actions -->
      <v-col cols="12" md="4">
        <v-card elevation="0" class="mb-3 compact-card d-flex flex-column">
          <v-card-title class="card-header pa-4">
            <span class="section-title-compact error--text">Quick Actions</span>
          </v-card-title>
          <v-card-text class="pa-4 flex-grow-1">
            <div class="quick-actions-grid">
              <v-btn 
                v-for="action in quickActions" 
                :key="action.label"
                :color="action.color"
                variant="tonal"
                class="quick-action-btn"
                @click="$emit('action', action.action)"
              >
                <v-icon start>{{ action.icon }}</v-icon>
                {{ action.label }}
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Activity -->
    <v-row v-if="showRecentActivity">
      <v-col cols="12">
        <v-card elevation="0" class="compact-card">
          <v-card-title class="card-header pa-4 d-flex justify-space-between align-center">
            <span class="section-title-compact error--text">Recent Activity</span>
            <v-btn 
              variant="text" 
              color="error" 
              size="small"
              @click="$emit('view-all-activity')"
            >
              View All
            </v-btn>
          </v-card-title>
          <v-card-text class="pa-4">
            <v-list v-if="recentActivity.length > 0" density="compact" class="activity-list">
              <v-list-item 
                v-for="activity in recentActivity.slice(0, 5)" 
                :key="activity.id"
                class="activity-item"
              >
                <template #prepend>
                  <v-avatar :color="getActivityColor(activity.type)" size="32">
                    <v-icon size="16" color="white">{{ getActivityIcon(activity.type) }}</v-icon>
                  </v-avatar>
                </template>
                <v-list-item-title class="activity-title">{{ activity.message }}</v-list-item-title>
                <v-list-item-subtitle class="activity-time">{{ formatTimeAgo(activity.created_at) }}</v-list-item-subtitle>
              </v-list-item>
            </v-list>
            <div v-else class="text-center py-4 text-muted">
              No recent activity
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import StatCard from './shared/StatCard.vue';

// Props
const props = defineProps({
  stats: {
    type: Array,
    default: () => []
  },
  recentActivity: {
    type: Array,
    default: () => []
  },
  showRecentActivity: {
    type: Boolean,
    default: true
  },
  serverLoad: {
    type: Number,
    default: 68
  },
  revenueTarget: {
    type: Number,
    default: 50000
  }
});

// Emits
defineEmits(['action', 'view-all-activity']);

// Computed
const userProgress = computed(() => {
  const total = props.stats[0]?.value || 0;
  const target = 1000; // Target users
  return Math.min((parseInt(total) / target) * 100, 100);
});

const userGrowth = computed(() => {
  const change = props.stats[0]?.change || '+0%';
  return `${change} from last month`;
});

const revenueProgress = computed(() => {
  const revenue = props.stats[2]?.value || '$0';
  const numericRevenue = parseInt(revenue.replace(/[$,]/g, '')) || 0;
  return Math.min((numericRevenue / props.revenueTarget) * 100, 100);
});

const serverLoadColor = computed(() => {
  if (props.serverLoad < 50) return 'success';
  if (props.serverLoad < 80) return 'warning';
  return 'error';
});

const serverLoadStatus = computed(() => {
  if (props.serverLoad < 50) return 'Optimal performance';
  if (props.serverLoad < 80) return 'Normal range';
  return 'High load - monitor closely';
});

const platformMetrics = computed(() => [
  { icon: 'mdi-account-group', value: props.stats[0]?.value || '0', label: 'Total Users', color: 'primary' },
  { icon: 'mdi-calendar-check', value: props.stats[1]?.value || '0', label: 'Bookings', color: 'success' },
  { icon: 'mdi-currency-usd', value: props.stats[2]?.value || '$0', label: 'Revenue', color: 'warning' },
  { icon: 'mdi-star', value: props.stats[3]?.value || '0', label: 'Reviews', color: 'info' },
]);

const quickActions = [
  { icon: 'mdi-account-plus', label: 'Add User', action: 'add-user', color: 'primary' },
  { icon: 'mdi-calendar-plus', label: 'New Booking', action: 'new-booking', color: 'success' },
  { icon: 'mdi-bullhorn', label: 'Announce', action: 'announce', color: 'warning' },
  { icon: 'mdi-cog', label: 'Settings', action: 'settings', color: 'secondary' },
];

// Helper functions
const getActivityColor = (type) => {
  const colors = {
    booking: 'success',
    user: 'primary',
    payment: 'warning',
    application: 'info',
    system: 'secondary',
  };
  return colors[type] || 'grey';
};

const getActivityIcon = (type) => {
  const icons = {
    booking: 'mdi-calendar',
    user: 'mdi-account',
    payment: 'mdi-credit-card',
    application: 'mdi-file-document',
    system: 'mdi-cog',
  };
  return icons[type] || 'mdi-information';
};

const formatTimeAgo = (dateString) => {
  if (!dateString) return 'Just now';
  
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);
  
  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  
  return date.toLocaleDateString();
};
</script>

<style scoped>
.admin-staff-stats {
  width: 100%;
}

.compact-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  height: 100%;
}

.card-header {
  border-bottom: 1px solid #f3f4f6;
}

.section-title-compact {
  font-size: 1rem;
  font-weight: 600;
}

.summary-label-compact {
  font-size: 0.875rem;
  color: #4b5563;
}

.summary-value-compact {
  font-size: 0.875rem;
  font-weight: 600;
}

.metric-grid {
  margin: -8px;
}

.metric-item {
  text-align: center;
  padding: 12px;
  background: #f9fafb;
  border-radius: 8px;
}

.metric-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
}

.metric-label {
  font-size: 0.75rem;
  color: #4b5563;
}

.quick-actions-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}

.quick-action-btn {
  width: 100%;
  min-height: 44px;
}

.activity-list {
  background: transparent;
}

.activity-item {
  border-bottom: 1px solid #f3f4f6;
  padding: 12px 0;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-title {
  font-size: 0.875rem;
  color: #1f2937;
}

.activity-time {
  font-size: 0.75rem;
  color: #4b5563;
}

.text-muted {
  color: #4b5563;
}

@media (max-width: 768px) {
  .quick-actions-grid {
    grid-template-columns: 1fr;
  }
}
</style>
