<template>
  <div class="admin-analytics-section">
    <!-- Top Stats Row -->
    <v-row class="mb-4">
      <v-col v-for="stat in analyticsStats" :key="stat.title" cols="6" sm="6" md="3">
        <v-card 
          elevation="0" 
          class="compact-stat-card"
          :aria-label="`${stat.title}: ${stat.value}, ${stat.change}`"
        >
          <v-card-text class="pa-4">
            <div class="d-flex align-center">
              <v-icon :color="stat.color" size="24" class="mr-3" aria-hidden="true">
                {{ stat.icon }}
              </v-icon>
              <div>
                <div class="stat-value" :class="stat.color + '--text'">{{ stat.value }}</div>
                <div class="stat-label">{{ stat.title }}</div>
                <div class="stat-change" :class="stat.changeColor">
                  <v-icon 
                    v-if="stat.changeColor === 'text-success'" 
                    size="12" 
                    color="success"
                    aria-hidden="true"
                  >
                    mdi-arrow-up
                  </v-icon>
                  <v-icon 
                    v-else-if="stat.changeColor === 'text-error'" 
                    size="12" 
                    color="error"
                    aria-hidden="true"
                  >
                    mdi-arrow-down
                  </v-icon>
                  {{ stat.change }}
                </div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Charts and Analytics Grid -->
    <v-row>
      <!-- Revenue Chart -->
      <v-col cols="12" md="4">
        <v-card elevation="0" class="mb-3 compact-chart-card">
          <v-card-title class="compact-header pa-4">
            <div class="d-flex justify-space-between align-center">
              <span class="compact-title error--text">Revenue Trend</span>
              <v-chip 
                :color="percentChange >= 0 ? 'success' : 'error'" 
                size="small" 
                class="font-weight-bold"
              >
                {{ percentChange >= 0 ? '+' : '' }}{{ percentChange }}%
              </v-chip>
            </div>
          </v-card-title>
          <v-card-text class="pa-4">
            <div class="mb-2">
              <div class="d-flex justify-space-between align-center mb-1">
                <span class="chart-subtitle">Monthly Growth</span>
                <span class="chart-value success--text">{{ revenueValue }}</span>
              </div>
            </div>
            <div 
              style="height: 180px; position: relative;"
              role="img"
              :aria-label="`Revenue chart showing ${revenueValue} with ${percentChange}% change`"
            >
              <canvas ref="revenueChart" aria-hidden="true"></canvas>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- User Distribution Chart -->
      <v-col cols="12" md="4">
        <v-card elevation="0" class="mb-3 compact-chart-card">
          <v-card-title class="compact-header pa-4">
            <div class="d-flex justify-space-between align-center">
              <span class="compact-title error--text">User Distribution</span>
              <v-chip color="info" size="small" class="font-weight-bold">{{ totalUsers }} Total</v-chip>
            </div>
          </v-card-title>
          <v-card-text class="pa-4">
            <div class="mb-2">
              <div class="user-stats-row">
                <div class="user-stat-item">
                  <div class="stat-dot" style="background-color: #3b82f6;" aria-hidden="true"></div>
                  <span class="stat-text">Clients: {{ clientCount }}</span>
                </div>
                <div class="user-stat-item">
                  <div class="stat-dot" style="background-color: #10b981;" aria-hidden="true"></div>
                  <span class="stat-text">Caregivers: {{ caregiverCount }}</span>
                </div>
                <div class="user-stat-item">
                  <div class="stat-dot" style="background-color: #dc2626;" aria-hidden="true"></div>
                  <span class="stat-text">Admins: {{ adminCount }}</span>
                </div>
              </div>
            </div>
            <div 
              style="height: 140px; position: relative;"
              role="img"
              :aria-label="`User distribution: ${clientCount} clients, ${caregiverCount} caregivers, ${adminCount} admins`"
            >
              <canvas ref="userChart" aria-hidden="true"></canvas>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Booking Status Chart -->
      <v-col cols="12" md="4">
        <v-card elevation="0" class="mb-3 compact-chart-card">
          <v-card-title class="compact-header pa-4">
            <div class="d-flex justify-space-between align-center">
              <span class="compact-title error--text">Booking Status</span>
              <v-chip color="warning" size="small" class="font-weight-bold">{{ totalBookings }} Total</v-chip>
            </div>
          </v-card-title>
          <v-card-text class="pa-4">
            <div class="mb-2">
              <div class="booking-stats-grid">
                <div class="booking-stat-item">
                  <div class="stat-indicator" style="background-color: #f59e0b;" aria-hidden="true"></div>
                  <div class="stat-info">
                    <div class="stat-number">{{ bookingStats.pending }}</div>
                    <div class="stat-label-small">Pending</div>
                  </div>
                </div>
                <div class="booking-stat-item">
                  <div class="stat-indicator" style="background-color: #10b981;" aria-hidden="true"></div>
                  <div class="stat-info">
                    <div class="stat-number">{{ bookingStats.active }}</div>
                    <div class="stat-label-small">Active</div>
                  </div>
                </div>
                <div class="booking-stat-item">
                  <div class="stat-indicator" style="background-color: #3b82f6;" aria-hidden="true"></div>
                  <div class="stat-info">
                    <div class="stat-number">{{ bookingStats.completed }}</div>
                    <div class="stat-label-small">Completed</div>
                  </div>
                </div>
                <div class="booking-stat-item">
                  <div class="stat-indicator" style="background-color: #ef4444;" aria-hidden="true"></div>
                  <div class="stat-info">
                    <div class="stat-number">{{ bookingStats.cancelled }}</div>
                    <div class="stat-label-small">Cancelled</div>
                  </div>
                </div>
              </div>
            </div>
            <div 
              style="height: 120px; position: relative;"
              role="img"
              :aria-label="`Booking status: ${bookingStats.pending} pending, ${bookingStats.active} active, ${bookingStats.completed} completed, ${bookingStats.cancelled} cancelled`"
            >
              <canvas ref="bookingChart" aria-hidden="true"></canvas>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Client, Caregiver & Housekeeper Analytics -->
    <v-row>
      <v-col cols="12" md="4">
        <v-card elevation="0" class="mb-3 analytics-metric-card">
          <v-card-title class="compact-header pa-4">
            <span class="compact-title error--text">Client Analytics</span>
          </v-card-title>
          <v-card-text class="pa-4">
            <v-row>
              <v-col cols="6" v-for="metric in clientMetrics" :key="metric.label">
                <div class="metric-box" :aria-label="`${metric.label}: ${metric.value}`">
                  <div class="metric-number" :class="metric.color + '--text'">{{ metric.value }}</div>
                  <div class="metric-text">{{ metric.label }}</div>
                </div>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card elevation="0" class="mb-3 analytics-metric-card">
          <v-card-title class="compact-header pa-4">
            <span class="compact-title error--text">Caregiver Analytics</span>
          </v-card-title>
          <v-card-text class="pa-4">
            <v-row>
              <v-col cols="6" v-for="metric in caregiverMetrics" :key="metric.label">
                <div class="metric-box" :aria-label="`${metric.label}: ${metric.value}`">
                  <div class="metric-number" :class="metric.color + '--text'">{{ metric.value }}</div>
                  <div class="metric-text">{{ metric.label }}</div>
                </div>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card elevation="0" class="mb-3 analytics-metric-card">
          <v-card-title class="compact-header pa-4">
            <span class="compact-title deep-purple--text">Housekeeper Analytics</span>
          </v-card-title>
          <v-card-text class="pa-4">
            <v-row>
              <v-col cols="6" v-for="metric in housekeeperMetrics" :key="metric.label">
                <div class="metric-box" :aria-label="`${metric.label}: ${metric.value}`">
                  <div class="metric-number" :class="metric.color + '--text'">{{ metric.value }}</div>
                  <div class="metric-text">{{ metric.label }}</div>
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import Chart from 'chart.js/auto';

// ============================================================================
// Props
// ============================================================================
const props = defineProps({
  /** Revenue percent change */
  percentChange: {
    type: Number,
    default: 0
  },
  /** Revenue value display string */
  revenueValue: {
    type: String,
    default: '$0'
  },
  /** Analytics stats cards data */
  analyticsStats: {
    type: Array,
    default: () => []
  },
  /** Client metrics */
  clientMetrics: {
    type: Array,
    default: () => []
  },
  /** Caregiver metrics */
  caregiverMetrics: {
    type: Array,
    default: () => []
  },
  /** Housekeeper metrics */
  housekeeperMetrics: {
    type: Array,
    default: () => []
  },
  /** Booking statistics */
  bookingStats: {
    type: Object,
    default: () => ({
      pending: 0,
      active: 0,
      completed: 0,
      cancelled: 0
    })
  },
  /** User counts */
  clientCount: {
    type: Number,
    default: 0
  },
  caregiverCount: {
    type: Number,
    default: 0
  },
  adminCount: {
    type: Number,
    default: 0
  },
  /** Revenue chart data */
  revenueChartData: {
    type: Array,
    default: () => []
  }
});

// ============================================================================
// Chart Refs
// ============================================================================
const revenueChart = ref(null);
const userChart = ref(null);
const bookingChart = ref(null);

let revenueChartInstance = null;
let userChartInstance = null;
let bookingChartInstance = null;

// ============================================================================
// Computed
// ============================================================================
const totalUsers = computed(() => 
  props.clientCount + props.caregiverCount + props.adminCount
);

const totalBookings = computed(() => 
  props.bookingStats.pending + 
  props.bookingStats.active + 
  props.bookingStats.completed + 
  props.bookingStats.cancelled
);

// ============================================================================
// Chart Initialization
// ============================================================================
function initCharts() {
  initRevenueChart();
  initUserChart();
  initBookingChart();
}

function initRevenueChart() {
  if (!revenueChart.value) return;
  
  const ctx = revenueChart.value.getContext('2d');
  
  // Destroy existing chart
  if (revenueChartInstance) {
    revenueChartInstance.destroy();
  }
  
  const labels = props.revenueChartData.length > 0 
    ? props.revenueChartData.map(d => d.label)
    : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
  
  const data = props.revenueChartData.length > 0
    ? props.revenueChartData.map(d => d.value)
    : [0, 0, 0, 0, 0, 0];
  
  revenueChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Revenue',
        data,
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        fill: true,
        tension: 0.4,
        pointRadius: 4,
        pointBackgroundColor: '#10b981'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.05)' },
          ticks: {
            callback: (value) => '$' + value.toLocaleString()
          }
        },
        x: {
          grid: { display: false }
        }
      }
    }
  });
}

function initUserChart() {
  if (!userChart.value) return;
  
  const ctx = userChart.value.getContext('2d');
  
  if (userChartInstance) {
    userChartInstance.destroy();
  }
  
  userChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Clients', 'Caregivers', 'Admins'],
      datasets: [{
        data: [props.clientCount, props.caregiverCount, props.adminCount],
        backgroundColor: ['#3b82f6', '#10b981', '#dc2626'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      plugins: {
        legend: { display: false }
      }
    }
  });
}

function initBookingChart() {
  if (!bookingChart.value) return;
  
  const ctx = bookingChart.value.getContext('2d');
  
  if (bookingChartInstance) {
    bookingChartInstance.destroy();
  }
  
  bookingChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Pending', 'Active', 'Completed', 'Cancelled'],
      datasets: [{
        data: [
          props.bookingStats.pending,
          props.bookingStats.active,
          props.bookingStats.completed,
          props.bookingStats.cancelled
        ],
        backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444'],
        borderRadius: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.05)' }
        },
        x: {
          grid: { display: false }
        }
      }
    }
  });
}

function destroyCharts() {
  if (revenueChartInstance) {
    revenueChartInstance.destroy();
    revenueChartInstance = null;
  }
  if (userChartInstance) {
    userChartInstance.destroy();
    userChartInstance = null;
  }
  if (bookingChartInstance) {
    bookingChartInstance.destroy();
    bookingChartInstance = null;
  }
}

// ============================================================================
// Lifecycle
// ============================================================================
onMounted(() => {
  // Use requestAnimationFrame to ensure DOM is ready
  requestAnimationFrame(() => {
    initCharts();
  });
});

onUnmounted(() => {
  destroyCharts();
});

// Watch for data changes and update charts
watch(() => [props.clientCount, props.caregiverCount, props.adminCount], () => {
  if (userChartInstance) {
    userChartInstance.data.datasets[0].data = [
      props.clientCount, 
      props.caregiverCount, 
      props.adminCount
    ];
    userChartInstance.update();
  }
});

watch(() => props.bookingStats, () => {
  if (bookingChartInstance) {
    bookingChartInstance.data.datasets[0].data = [
      props.bookingStats.pending,
      props.bookingStats.active,
      props.bookingStats.completed,
      props.bookingStats.cancelled
    ];
    bookingChartInstance.update();
  }
}, { deep: true });

// ============================================================================
// Expose
// ============================================================================
defineExpose({
  refreshCharts: initCharts
});
</script>

<style scoped>
.compact-stat-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.compact-stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
}

.stat-change {
  font-size: 0.7rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 2px;
}

.text-success {
  color: #10b981;
}

.text-error {
  color: #ef4444;
}

.compact-chart-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
}

.compact-header {
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.compact-title {
  font-size: 1rem;
  font-weight: 700;
}

.chart-subtitle {
  font-size: 0.75rem;
  color: #6b7280;
}

.chart-value {
  font-size: 0.875rem;
  font-weight: 700;
}

.user-stats-row {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.user-stat-item {
  display: flex;
  align-items: center;
  gap: 6px;
}

.stat-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.stat-text {
  font-size: 0.75rem;
  color: #374151;
}

.booking-stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}

.booking-stat-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.stat-indicator {
  width: 4px;
  height: 28px;
  border-radius: 2px;
  flex-shrink: 0;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-number {
  font-size: 1rem;
  font-weight: 700;
  color: #111827;
  line-height: 1.2;
}

.stat-label-small {
  font-size: 0.65rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.analytics-metric-card {
  border: 1px solid #c5c5c5;
  border-radius: 12px;
}

.metric-box {
  text-align: center;
  padding: 12px 8px;
  border-radius: 8px;
  background: #f9fafb;
}

.metric-number {
  font-size: 1.25rem;
  font-weight: 700;
  line-height: 1.2;
}

.metric-text {
  font-size: 0.7rem;
  color: #6b7280;
  margin-top: 4px;
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .compact-stat-card {
    transition: none;
  }
}
</style>
