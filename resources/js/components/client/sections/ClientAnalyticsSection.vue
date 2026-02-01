<template>
  <div class="client-analytics-section">
    <!-- Analytics Summary Header -->
    <v-card elevation="0" class="mb-6">
      <v-card-text class="pa-6 pa-md-8">
        <div class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center gap-4">
          <div class="transaction-stats">
            <div class="stat-item">
              <div class="stat-amount primary--text">${{ formatNumber(analyticsData.totalSpent) }}</div>
              <div class="stat-label-text">Total Spent</div>
            </div>
            <v-divider vertical class="d-none d-md-block mx-4" />
            <div class="stat-item">
              <div class="stat-amount info--text">${{ formatNumber(analyticsData.thisMonth) }}</div>
              <div class="stat-label-text">This Month</div>
            </div>
            <v-divider vertical class="d-none d-md-block mx-4" />
            <div class="stat-item">
              <div class="stat-amount primary--text">${{ formatNumber(analyticsData.avgPerMonth) }}</div>
              <div class="stat-label-text">Avg per Month</div>
            </div>
          </div>
          <v-btn 
            color="primary" 
            variant="flat" 
            @click="$emit('export-pdf')" 
            :loading="exporting"
            class="export-btn"
          >
            <v-icon start>mdi-file-pdf-box</v-icon>
            Export to PDF
          </v-btn>
        </div>
      </v-card-text>
    </v-card>

    <v-row>
      <!-- Spending Chart -->
      <v-col cols="12" md="9">
        <v-card elevation="0" class="mb-6">
          <v-card-title class="card-header pa-6 pa-md-8">
            <div class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center gap-3">
              <span class="section-title primary--text">Spending Over Time</span>
              <div class="d-flex align-center gap-2 flex-wrap">
                <v-btn-toggle v-model="chartPeriod" mandatory color="primary" size="small">
                  <v-btn value="week">Week</v-btn>
                  <v-btn value="month">Month</v-btn>
                  <v-btn value="year">Year</v-btn>
                </v-btn-toggle>
                <v-select
                  v-model="selectedYear"
                  :items="availableYears"
                  variant="outlined"
                  density="compact"
                  style="width: 100px;"
                  hide-details
                  @update:model-value="$emit('year-change', $event)"
                />
              </div>
            </div>
          </v-card-title>
          <v-card-text class="pa-6 pa-md-8">
            <div class="chart-container">
              <canvas ref="spendingChart"></canvas>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Quick Stats -->
      <v-col cols="12" md="3">
        <v-card elevation="0" class="h-100">
          <v-card-title class="card-header pa-6 pa-md-8">
            <span class="section-title primary--text">Quick Stats</span>
          </v-card-title>
          <v-card-text class="pa-6 pa-md-8">
            <div class="quick-stat-item">
              <v-icon color="primary" class="mr-3" size="32">mdi-calendar-check</v-icon>
              <div>
                <div class="quick-stat-value">{{ analyticsData.totalBookings }}</div>
                <div class="quick-stat-label">Total Bookings</div>
              </div>
            </div>
            <v-divider class="my-4" />
            <div class="quick-stat-item">
              <v-icon color="info" class="mr-3" size="32">mdi-clock-outline</v-icon>
              <div>
                <div class="quick-stat-value">{{ analyticsData.totalHours }} hrs</div>
                <div class="quick-stat-label">Total Hours</div>
              </div>
            </div>
            <v-divider class="my-4" />
            <div class="quick-stat-item">
              <v-icon color="warning" class="mr-3" size="32">mdi-account-multiple</v-icon>
              <div>
                <div class="quick-stat-value">{{ analyticsData.activeCaregivers }}</div>
                <div class="quick-stat-label">Active Caregivers</div>
              </div>
            </div>
            <v-divider class="my-4" />
            <div class="quick-stat-item">
              <v-icon color="success" class="mr-3" size="32">mdi-star</v-icon>
              <div>
                <div class="quick-stat-value">{{ analyticsData.avgRating?.toFixed(1) || 'N/A' }}</div>
                <div class="quick-stat-label">Avg. Rating</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Service Breakdown -->
    <v-row>
      <v-col cols="12" md="6">
        <v-card elevation="0">
          <v-card-title class="card-header pa-6">
            <span class="section-title primary--text">Service Breakdown</span>
          </v-card-title>
          <v-card-text class="pa-6">
            <div class="service-breakdown">
              <div 
                v-for="service in serviceBreakdown" 
                :key="service.name" 
                class="service-item mb-4"
              >
                <div class="d-flex justify-space-between align-center mb-2">
                  <span class="service-name">{{ service.name }}</span>
                  <span class="service-amount">${{ formatNumber(service.amount) }}</span>
                </div>
                <v-progress-linear
                  :model-value="service.percentage"
                  :color="service.color"
                  height="8"
                  rounded
                ></v-progress-linear>
                <div class="d-flex justify-space-between mt-1">
                  <span class="text-caption text-grey">{{ service.bookings }} bookings</span>
                  <span class="text-caption text-grey">{{ service.percentage.toFixed(1) }}%</span>
                </div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Monthly Comparison -->
      <v-col cols="12" md="6">
        <v-card elevation="0">
          <v-card-title class="card-header pa-6">
            <span class="section-title primary--text">Monthly Comparison</span>
          </v-card-title>
          <v-card-text class="pa-6">
            <div class="monthly-comparison">
              <div 
                v-for="month in monthlyComparison" 
                :key="month.name" 
                class="month-item d-flex align-center mb-3"
              >
                <div class="month-name" style="width: 80px;">{{ month.name }}</div>
                <v-progress-linear
                  :model-value="month.percentage"
                  color="primary"
                  height="20"
                  rounded
                  class="flex-grow-1 mx-3"
                >
                  <template v-slot:default>
                    <span class="text-caption font-weight-medium">
                      ${{ formatNumber(month.amount) }}
                    </span>
                  </template>
                </v-progress-linear>
                <v-icon 
                  :color="month.change >= 0 ? 'success' : 'error'" 
                  size="small"
                >
                  {{ month.change >= 0 ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                </v-icon>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  analyticsData: {
    type: Object,
    default: () => ({
      totalSpent: 0,
      thisMonth: 0,
      avgPerMonth: 0,
      totalBookings: 0,
      totalHours: 0,
      activeCaregivers: 0,
      avgRating: null,
      spendingData: []
    })
  },
  exporting: {
    type: Boolean,
    default: false
  },
  initialPeriod: {
    type: String,
    default: 'month'
  },
  initialYear: {
    type: Number,
    default: () => new Date().getFullYear()
  }
});

const emit = defineEmits(['export-pdf', 'period-change', 'year-change']);

// Local state
const spendingChart = ref(null);
const chartPeriod = ref(props.initialPeriod);
const selectedYear = ref(props.initialYear);
let chartInstance = null;

// Available years for selection
const availableYears = computed(() => {
  const currentYear = new Date().getFullYear();
  return Array.from({ length: 5 }, (_, i) => currentYear - i);
});

// Service breakdown from analytics data
const serviceBreakdown = computed(() => {
  if (!props.analyticsData.serviceBreakdown) {
    // Default sample data if not provided
    return [
      { name: 'Elderly Care', amount: 2500, bookings: 12, percentage: 45, color: 'primary' },
      { name: 'Child Care', amount: 1800, bookings: 8, percentage: 32, color: 'info' },
      { name: 'Medical Assistance', amount: 1200, bookings: 5, percentage: 23, color: 'success' }
    ];
  }
  return props.analyticsData.serviceBreakdown;
});

// Monthly comparison data
const monthlyComparison = computed(() => {
  if (!props.analyticsData.monthlyComparison) {
    // Default sample data
    return [
      { name: 'Jan', amount: 1200, percentage: 60, change: 5 },
      { name: 'Feb', amount: 1500, percentage: 75, change: 25 },
      { name: 'Mar', amount: 2000, percentage: 100, change: 33 }
    ];
  }
  return props.analyticsData.monthlyComparison;
});

// Format number with commas
function formatNumber(num) {
  if (!num) return '0';
  return num.toLocaleString();
}

// Initialize spending chart
function initChart() {
  if (!spendingChart.value) return;
  
  const ctx = spendingChart.value.getContext('2d');
  
  // Destroy existing chart if any
  if (chartInstance) {
    chartInstance.destroy();
  }
  
  const spendingData = props.analyticsData.spendingData || generateSampleData();
  
  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: spendingData.labels,
      datasets: [{
        label: 'Spending',
        data: spendingData.values,
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#3b82f6',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          padding: 12,
          cornerRadius: 8,
          callbacks: {
            label: (context) => `$${context.raw.toLocaleString()}`
          }
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          },
          ticks: {
            font: {
              size: 12
            }
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(0, 0, 0, 0.05)'
          },
          ticks: {
            callback: (value) => `$${value.toLocaleString()}`,
            font: {
              size: 12
            }
          }
        }
      },
      interaction: {
        intersect: false,
        mode: 'index'
      }
    }
  });
}

// Generate sample chart data
function generateSampleData() {
  const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
  const values = [1200, 1800, 1500, 2200, 1900, 2500];
  return { labels, values };
}

// Watch for period changes
watch(chartPeriod, (newPeriod) => {
  emit('period-change', newPeriod);
  initChart();
});

// Watch for data changes
watch(() => props.analyticsData.spendingData, () => {
  initChart();
}, { deep: true });

onMounted(() => {
  // Initialize chart after DOM is ready
  setTimeout(initChart, 100);
});

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy();
  }
});
</script>

<style scoped>
.client-analytics-section {
  animation: fadeInUp 0.3s ease-out;
}

.transaction-stats {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: center;
}

.stat-item {
  text-align: center;
  min-width: 100px;
}

.stat-amount {
  font-size: 1.75rem;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.stat-label-text {
  font-size: 0.8125rem;
  color: var(--text-secondary, #64748b);
  font-weight: 500;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  letter-spacing: -0.01em;
}

.chart-container {
  height: 300px;
  position: relative;
}

.quick-stat-item {
  display: flex;
  align-items: center;
}

.quick-stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-primary, #1a1a1a);
}

.quick-stat-label {
  font-size: 0.8125rem;
  color: var(--text-secondary, #64748b);
}

.service-name {
  font-weight: 500;
  color: var(--text-primary, #1a1a1a);
}

.service-amount {
  font-weight: 600;
  color: var(--primary, #3b82f6);
}

.month-name {
  font-weight: 500;
  color: var(--text-secondary, #64748b);
  font-size: 0.875rem;
}

/* Responsive Styles */
@media (max-width: 960px) {
  .stat-amount {
    font-size: 1.5rem;
  }
  
  .chart-container {
    height: 250px;
  }
  
  .quick-stat-value {
    font-size: 1.25rem;
  }
}

@media (max-width: 600px) {
  .transaction-stats {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .stat-item {
    text-align: left;
  }
  
  .export-btn {
    width: 100%;
  }
  
  .chart-container {
    height: 200px;
  }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
