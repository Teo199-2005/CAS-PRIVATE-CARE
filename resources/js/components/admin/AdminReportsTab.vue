<template>
  <v-container fluid class="admin-reports-tab pa-0">
    <v-row>
      <!-- Date Range Selector -->
      <v-col cols="12">
        <v-card elevation="2" rounded="lg" class="mb-4">
          <v-card-text class="d-flex align-center flex-wrap ga-4 py-4">
            <v-icon color="primary" class="mr-2">mdi-chart-areaspline</v-icon>
            <span class="text-h6 font-weight-bold mr-4">Analytics & Reports</span>
            
            <v-select
              v-model="dateRange"
              :items="dateRangeOptions"
              label="Date Range"
              density="compact"
              variant="outlined"
              hide-details
              class="max-width-200"
              aria-label="Select date range for reports"
            ></v-select>
            
            <v-text-field
              v-if="dateRange === 'custom'"
              v-model="customStartDate"
              type="date"
              label="Start Date"
              density="compact"
              variant="outlined"
              hide-details
              class="max-width-180"
            ></v-text-field>
            
            <v-text-field
              v-if="dateRange === 'custom'"
              v-model="customEndDate"
              type="date"
              label="End Date"
              density="compact"
              variant="outlined"
              hide-details
              class="max-width-180"
            ></v-text-field>
            
            <v-spacer></v-spacer>
            
            <v-btn 
              color="success" 
              variant="flat" 
              prepend-icon="mdi-download"
              @click="exportReport('pdf')"
              :loading="exporting === 'pdf'"
              class="mr-2"
            >
              Export PDF
            </v-btn>
            
            <v-btn 
              color="primary" 
              variant="outlined" 
              prepend-icon="mdi-file-excel"
              @click="exportReport('excel')"
              :loading="exporting === 'excel'"
            >
              Export Excel
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
      
      <!-- KPI Summary Cards -->
      <v-col cols="12" sm="6" lg="3">
        <v-card elevation="2" rounded="lg" class="pa-4 h-100">
          <div class="d-flex align-center mb-3">
            <v-avatar color="primary" size="48" class="mr-3">
              <v-icon color="white">mdi-currency-usd</v-icon>
            </v-avatar>
            <div>
              <p class="text-caption text-grey mb-0">Total Revenue</p>
              <p class="text-h5 font-weight-bold text-primary mb-0">${{ formatMoney(stats.totalRevenue) }}</p>
            </div>
          </div>
          <v-chip :color="stats.revenueGrowth >= 0 ? 'success' : 'error'" size="small" variant="flat">
            <v-icon start size="14">{{ stats.revenueGrowth >= 0 ? 'mdi-trending-up' : 'mdi-trending-down' }}</v-icon>
            {{ Math.abs(stats.revenueGrowth) }}% vs last period
          </v-chip>
        </v-card>
      </v-col>
      
      <v-col cols="12" sm="6" lg="3">
        <v-card elevation="2" rounded="lg" class="pa-4 h-100">
          <div class="d-flex align-center mb-3">
            <v-avatar color="success" size="48" class="mr-3">
              <v-icon color="white">mdi-calendar-check</v-icon>
            </v-avatar>
            <div>
              <p class="text-caption text-grey mb-0">Total Bookings</p>
              <p class="text-h5 font-weight-bold text-success mb-0">{{ stats.totalBookings }}</p>
            </div>
          </div>
          <v-chip :color="stats.bookingsGrowth >= 0 ? 'success' : 'error'" size="small" variant="flat">
            <v-icon start size="14">{{ stats.bookingsGrowth >= 0 ? 'mdi-trending-up' : 'mdi-trending-down' }}</v-icon>
            {{ Math.abs(stats.bookingsGrowth) }}% vs last period
          </v-chip>
        </v-card>
      </v-col>
      
      <v-col cols="12" sm="6" lg="3">
        <v-card elevation="2" rounded="lg" class="pa-4 h-100">
          <div class="d-flex align-center mb-3">
            <v-avatar color="info" size="48" class="mr-3">
              <v-icon color="white">mdi-account-group</v-icon>
            </v-avatar>
            <div>
              <p class="text-caption text-grey mb-0">Active Users</p>
              <p class="text-h5 font-weight-bold text-info mb-0">{{ stats.activeUsers }}</p>
            </div>
          </div>
          <div class="d-flex ga-2">
            <v-chip size="x-small" color="primary" variant="outlined">{{ stats.newClients }} clients</v-chip>
            <v-chip size="x-small" color="success" variant="outlined">{{ stats.newCaregivers }} caregivers</v-chip>
          </div>
        </v-card>
      </v-col>
      
      <v-col cols="12" sm="6" lg="3">
        <v-card elevation="2" rounded="lg" class="pa-4 h-100">
          <div class="d-flex align-center mb-3">
            <v-avatar color="warning" size="48" class="mr-3">
              <v-icon color="white">mdi-star</v-icon>
            </v-avatar>
            <div>
              <p class="text-caption text-grey mb-0">Avg. Rating</p>
              <p class="text-h5 font-weight-bold text-warning mb-0">{{ stats.avgRating.toFixed(1) }}</p>
            </div>
          </div>
          <v-rating :model-value="stats.avgRating" half-increments readonly size="18" color="warning"></v-rating>
        </v-card>
      </v-col>
      
      <!-- Revenue Chart -->
      <v-col cols="12" lg="8">
        <v-card elevation="2" rounded="lg">
          <v-card-title class="d-flex align-center py-4 px-6">
            <v-icon color="primary" class="mr-2">mdi-chart-line</v-icon>
            <span class="font-weight-bold">Revenue Trend</span>
            <v-spacer></v-spacer>
            <v-btn-toggle v-model="chartView" density="compact" mandatory variant="outlined">
              <v-btn value="daily" size="small">Daily</v-btn>
              <v-btn value="weekly" size="small">Weekly</v-btn>
              <v-btn value="monthly" size="small">Monthly</v-btn>
            </v-btn-toggle>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-6">
            <div v-if="loading" class="d-flex justify-center align-center" style="height: 300px;">
              <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
            </div>
            <div v-else ref="revenueChartRef" style="height: 300px;" aria-label="Revenue trend chart">
              <!-- Chart would be rendered here by ApexCharts or similar -->
              <div class="text-center text-grey py-16">
                <v-icon size="64" color="grey-lighten-2">mdi-chart-line-variant</v-icon>
                <p class="mt-4">Revenue Chart Placeholder</p>
                <p class="text-caption">Integrate with ApexCharts or Chart.js</p>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
      
      <!-- Booking Distribution -->
      <v-col cols="12" lg="4">
        <v-card elevation="2" rounded="lg" class="h-100">
          <v-card-title class="d-flex align-center py-4 px-6">
            <v-icon color="success" class="mr-2">mdi-chart-pie</v-icon>
            <span class="font-weight-bold">Booking Status</span>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-6">
            <div v-if="loading" class="d-flex justify-center align-center" style="height: 250px;">
              <v-progress-circular indeterminate color="success" size="48"></v-progress-circular>
            </div>
            <div v-else>
              <!-- Status breakdown list -->
              <v-list density="compact" class="bg-transparent">
                <v-list-item v-for="status in bookingStatusBreakdown" :key="status.name">
                  <template v-slot:prepend>
                    <v-avatar :color="status.color" size="12" class="mr-3"></v-avatar>
                  </template>
                  <v-list-item-title>{{ status.name }}</v-list-item-title>
                  <template v-slot:append>
                    <span class="font-weight-bold">{{ status.count }}</span>
                    <span class="text-caption text-grey ml-2">({{ status.percentage }}%)</span>
                  </template>
                </v-list-item>
              </v-list>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
      
      <!-- Top Performers -->
      <v-col cols="12" md="6">
        <v-card elevation="2" rounded="lg">
          <v-card-title class="d-flex align-center py-4 px-6">
            <v-icon color="success" class="mr-2">mdi-trophy</v-icon>
            <span class="font-weight-bold">Top Caregivers</span>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-0">
            <v-list density="comfortable">
              <v-list-item 
                v-for="(caregiver, index) in topCaregivers" 
                :key="caregiver.id"
                :class="index === 0 ? 'bg-amber-lighten-5' : ''"
              >
                <template v-slot:prepend>
                  <v-avatar :color="index === 0 ? 'amber' : 'green'" size="40" class="mr-3">
                    <span class="text-white font-weight-bold">{{ index + 1 }}</span>
                  </v-avatar>
                </template>
                <v-list-item-title class="font-weight-medium">{{ caregiver.name }}</v-list-item-title>
                <v-list-item-subtitle>
                  {{ caregiver.bookings }} bookings · {{ caregiver.hours }} hours
                </v-list-item-subtitle>
                <template v-slot:append>
                  <div class="text-right">
                    <p class="font-weight-bold text-success mb-0">${{ formatMoney(caregiver.earnings) }}</p>
                    <v-rating :model-value="caregiver.rating" size="12" readonly density="compact" color="warning"></v-rating>
                  </div>
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
      
      <!-- Recent Activity -->
      <v-col cols="12" md="6">
        <v-card elevation="2" rounded="lg">
          <v-card-title class="d-flex align-center py-4 px-6">
            <v-icon color="info" class="mr-2">mdi-history</v-icon>
            <span class="font-weight-bold">Recent Activity</span>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text class="pa-0" style="max-height: 350px; overflow-y: auto;">
            <v-timeline density="compact" side="end">
              <v-timeline-item
                v-for="activity in recentActivity"
                :key="activity.id"
                :dot-color="getActivityColor(activity.type)"
                size="small"
              >
                <div class="d-flex align-center">
                  <v-icon :color="getActivityColor(activity.type)" size="18" class="mr-2">
                    {{ getActivityIcon(activity.type) }}
                  </v-icon>
                  <div>
                    <p class="font-weight-medium mb-0">{{ activity.description }}</p>
                    <p class="text-caption text-grey mb-0">{{ formatTimeAgo(activity.created_at) }}</p>
                  </div>
                </div>
              </v-timeline-item>
            </v-timeline>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
/**
 * AdminReportsTab Component
 * Analytics dashboard with charts, KPIs, and export functionality
 */

import { ref, computed } from 'vue';

// Props
const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      totalRevenue: 0,
      revenueGrowth: 0,
      totalBookings: 0,
      bookingsGrowth: 0,
      activeUsers: 0,
      newClients: 0,
      newCaregivers: 0,
      avgRating: 0
    })
  },
  topCaregivers: { type: Array, default: () => [] },
  recentActivity: { type: Array, default: () => [] },
  bookingStatusBreakdown: {
    type: Array,
    default: () => [
      { name: 'Completed', count: 0, percentage: 0, color: 'success' },
      { name: 'In Progress', count: 0, percentage: 0, color: 'primary' },
      { name: 'Pending', count: 0, percentage: 0, color: 'warning' },
      { name: 'Cancelled', count: 0, percentage: 0, color: 'error' }
    ]
  },
  loading: { type: Boolean, default: false }
});

// Emits
const emit = defineEmits(['export', 'date-change']);

// State
const dateRange = ref('last30days');
const customStartDate = ref('');
const customEndDate = ref('');
const chartView = ref('daily');
const exporting = ref(null);
const revenueChartRef = ref(null);

const dateRangeOptions = [
  { title: 'Last 7 Days', value: 'last7days' },
  { title: 'Last 30 Days', value: 'last30days' },
  { title: 'Last 90 Days', value: 'last90days' },
  { title: 'This Year', value: 'thisYear' },
  { title: 'Custom Range', value: 'custom' }
];

// Methods
const formatMoney = (amount) => {
  const num = parseFloat(amount) || 0;
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num.toFixed(2);
};

const formatTimeAgo = (date) => {
  if (!date) return '';
  const now = new Date();
  const past = new Date(date);
  const diffMs = now - past;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);
  
  if (diffMins < 60) return `${diffMins} min ago`;
  if (diffHours < 24) return `${diffHours} hours ago`;
  return `${diffDays} days ago`;
};

const getActivityColor = (type) => {
  const colors = {
    booking: 'primary',
    payment: 'success',
    user: 'info',
    payout: 'warning',
    cancellation: 'error'
  };
  return colors[type] || 'grey';
};

const getActivityIcon = (type) => {
  const icons = {
    booking: 'mdi-calendar-plus',
    payment: 'mdi-cash-check',
    user: 'mdi-account-plus',
    payout: 'mdi-bank-transfer-out',
    cancellation: 'mdi-calendar-remove'
  };
  return icons[type] || 'mdi-information';
};

const exportReport = async (format) => {
  exporting.value = format;
  
  emit('export', {
    format,
    dateRange: dateRange.value,
    startDate: customStartDate.value,
    endDate: customEndDate.value
  });
  
  // Simulate export delay
  setTimeout(() => {
    exporting.value = null;
  }, 2000);
};
</script>

<style scoped>
.max-width-200 {
  max-width: 200px;
}
.max-width-180 {
  max-width: 180px;
}
.h-100 {
  height: 100%;
}

/* Focus visible for accessibility */
.admin-reports-tab :deep(.v-btn:focus-visible) {
  outline: 2px solid var(--v-theme-primary);
  outline-offset: 2px;
}
</style>
