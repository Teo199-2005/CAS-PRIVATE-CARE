<template>
  <div class="dashboard-overview">
    <!-- Stats Row -->
    <v-row class="mb-4">
      <v-col 
        v-for="(stat, index) in stats" 
        :key="stat.title" 
        cols="6" 
        sm="6" 
        md="3"
      >
        <v-card
          class="stat-card"
          :style="{ animationDelay: `${index * 50}ms` }"
          elevation="0"
          rounded="lg"
        >
          <v-card-text class="d-flex align-center pa-4">
            <v-avatar 
              :color="stat.color || 'primary'" 
              size="48" 
              class="mr-3"
            >
              <v-icon color="white" size="24">{{ stat.icon }}</v-icon>
            </v-avatar>
            <div class="flex-grow-1">
              <div class="text-h5 font-weight-bold">
                {{ formatValue(stat.value, stat.format) }}
              </div>
              <div class="text-caption text-medium-emphasis">
                {{ stat.title }}
              </div>
              <div 
                v-if="stat.change !== undefined" 
                class="text-caption"
                :class="stat.change >= 0 ? 'text-success' : 'text-error'"
              >
                <v-icon size="12">
                  {{ stat.change >= 0 ? 'mdi-trending-up' : 'mdi-trending-down' }}
                </v-icon>
                {{ Math.abs(stat.change) }}% from last month
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Quick Actions -->
    <v-row class="mb-4">
      <v-col cols="12">
        <v-card elevation="0" rounded="lg">
          <v-card-title class="text-subtitle-1 font-weight-medium">
            <v-icon start size="20">mdi-lightning-bolt</v-icon>
            Quick Actions
          </v-card-title>
          <v-card-text>
            <div class="d-flex flex-wrap gap-2">
              <v-btn
                v-for="action in quickActions"
                :key="action.label"
                :color="action.color || 'primary'"
                :variant="action.variant || 'outlined'"
                size="small"
                :prepend-icon="action.icon"
                @click="$emit('action', action.action)"
              >
                {{ action.label }}
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Info Cards Row -->
    <v-row>
      <v-col cols="12" md="4">
        <slot name="staff-card">
          <v-card elevation="0" rounded="lg" class="h-100">
            <v-card-title class="text-subtitle-1">
              <v-icon start>mdi-account-group</v-icon>
              Staff Overview
            </v-card-title>
            <v-card-text>
              <v-list density="compact">
                <v-list-item 
                  v-for="item in staffData" 
                  :key="item.label"
                >
                  <template #prepend>
                    <v-icon :color="item.color" size="20">{{ item.icon }}</v-icon>
                  </template>
                  <v-list-item-title>{{ item.label }}</v-list-item-title>
                  <template #append>
                    <v-chip size="x-small" :color="item.color">
                      {{ item.value }}
                    </v-chip>
                  </template>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </slot>
      </v-col>

      <v-col cols="12" md="4">
        <slot name="booking-card">
          <v-card elevation="0" rounded="lg" class="h-100">
            <v-card-title class="text-subtitle-1">
              <v-icon start>mdi-calendar-check</v-icon>
              Booking Status
            </v-card-title>
            <v-card-text>
              <v-list density="compact">
                <v-list-item 
                  v-for="item in bookingStats" 
                  :key="item.label"
                >
                  <template #prepend>
                    <v-icon :color="item.color" size="20">{{ item.icon }}</v-icon>
                  </template>
                  <v-list-item-title>{{ item.label }}</v-list-item-title>
                  <template #append>
                    <v-chip size="x-small" :color="item.color">
                      {{ item.value }}
                    </v-chip>
                  </template>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </slot>
      </v-col>

      <v-col cols="12" md="4">
        <slot name="activity-card">
          <v-card elevation="0" rounded="lg" class="h-100">
            <v-card-title class="text-subtitle-1">
              <v-icon start>mdi-history</v-icon>
              Recent Activity
            </v-card-title>
            <v-card-text class="pa-0">
              <v-list density="compact" max-height="200" class="overflow-y-auto">
                <v-list-item 
                  v-for="activity in recentActivity" 
                  :key="activity.id"
                  :subtitle="formatTimeAgo(activity.created_at)"
                >
                  <template #prepend>
                    <v-avatar :color="activity.color || 'grey'" size="32">
                      <v-icon size="16" color="white">{{ activity.icon }}</v-icon>
                    </v-avatar>
                  </template>
                  <v-list-item-title class="text-body-2">
                    {{ activity.message }}
                  </v-list-item-title>
                </v-list-item>
                
                <v-list-item v-if="!recentActivity?.length" class="text-center">
                  <v-list-item-title class="text-caption text-medium-emphasis">
                    No recent activity
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </slot>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
/**
 * Dashboard Overview Section Component
 * 
 * Displays key stats, quick actions, and overview cards
 * for admin dashboard landing page.
 */
import { computed } from 'vue';

const props = defineProps({
  stats: {
    type: Array,
    default: () => [],
    validator: (value) => value.every(s => s.title && s.value !== undefined),
  },
  quickActions: {
    type: Array,
    default: () => [
      { label: 'Add Caregiver', icon: 'mdi-account-plus', action: 'add-caregiver', color: 'primary' },
      { label: 'View Bookings', icon: 'mdi-calendar', action: 'view-bookings' },
      { label: 'Reports', icon: 'mdi-chart-bar', action: 'reports' },
    ],
  },
  staffData: {
    type: Array,
    default: () => [],
  },
  bookingStats: {
    type: Array,
    default: () => [],
  },
  recentActivity: {
    type: Array,
    default: () => [],
  },
});

defineEmits(['action']);

const formatValue = (value, format) => {
  if (format === 'currency') {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 0,
    }).format(value);
  }
  if (format === 'number') {
    return new Intl.NumberFormat('en-US').format(value);
  }
  return value;
};

const formatTimeAgo = (dateString) => {
  if (!dateString) return '';
  
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
  
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};
</script>

<style scoped>
.stat-card {
  animation: fadeInUp 0.3s ease-out both;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (prefers-reduced-motion: reduce) {
  .stat-card {
    animation: none;
  }
}
</style>
