<template>
  <div class="client-analytics">
    <!-- Summary Card -->
    <v-card elevation="0" class="mb-6">
      <v-card-text class="pa-8">
        <div class="d-flex justify-between align-center mb-4 flex-wrap">
          <div class="transaction-stats" style="flex: 1;">
            <div class="stat-item">
              <div class="stat-amount primary--text">${{ formatNumber(analyticsData.totalSpent) }}</div>
              <div class="stat-label-text">Total Spent</div>
            </div>
            <v-divider vertical class="mx-4 hidden-xs" />
            <div class="stat-item">
              <div class="stat-amount info--text">${{ formatNumber(analyticsData.thisMonth) }}</div>
              <div class="stat-label-text">This Month</div>
            </div>
            <v-divider vertical class="mx-4 hidden-xs" />
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
            aria-label="Export analytics to PDF"
          >
            <v-icon start>mdi-file-pdf-box</v-icon>
            Export to PDF
          </v-btn>
        </div>
      </v-card-text>
    </v-card>

    <v-row>
      <!-- Chart Section -->
      <v-col cols="12" md="9">
        <v-card elevation="0" class="mb-6">
          <v-card-title class="card-header pa-8 d-flex justify-space-between align-center flex-wrap">
            <span class="section-title primary--text">Spending Over Time</span>
            <div class="d-flex align-center mt-2 mt-md-0">
              <v-btn-toggle v-model="localPeriod" mandatory color="primary" size="small">
                <v-btn value="week">Week</v-btn>
                <v-btn value="month">Month</v-btn>
                <v-btn value="year">Year</v-btn>
              </v-btn-toggle>
              <v-select
                v-model="localYear"
                :items="availableYears"
                variant="outlined"
                density="compact"
                style="width: 100px; margin-left: 16px;"
                hide-details
                aria-label="Select year"
              />
            </div>
          </v-card-title>
          <v-card-text class="pa-8">
            <div style="height: 300px; position: relative;">
              <canvas ref="spendingChart" aria-label="Spending chart"></canvas>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Quick Stats Sidebar -->
      <v-col cols="12" md="3">
        <v-card elevation="0">
          <v-card-title class="card-header pa-8">
            <span class="section-title primary--text">Quick Stats</span>
          </v-card-title>
          <v-card-text class="pa-8">
            <div class="quick-stat-item d-flex align-center">
              <v-icon color="primary" class="mr-3">mdi-calendar-check</v-icon>
              <div>
                <div class="quick-stat-value text-h5 font-weight-bold">{{ analyticsData.totalBookings }}</div>
                <div class="quick-stat-label text-caption text-grey">Total Bookings</div>
              </div>
            </div>
            <v-divider class="my-4" />
            <div class="quick-stat-item d-flex align-center">
              <v-icon color="info" class="mr-3">mdi-clock-outline</v-icon>
              <div>
                <div class="quick-stat-value text-h5 font-weight-bold">{{ analyticsData.totalHours }} hrs</div>
                <div class="quick-stat-label text-caption text-grey">Total Hours</div>
              </div>
            </div>
            <v-divider class="my-4" />
            <div class="quick-stat-item d-flex align-center">
              <v-icon color="warning" class="mr-3">mdi-account-multiple</v-icon>
              <div>
                <div class="quick-stat-value text-h5 font-weight-bold">{{ analyticsData.activeCaregivers }}</div>
                <div class="quick-stat-label text-caption text-grey">Active Caregivers</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

/**
 * ClientAnalytics - Spending analytics and charts for clients
 */

const props = defineProps({
  /** Analytics data object */
  analyticsData: {
    type: Object,
    default: () => ({
      totalSpent: 0,
      thisMonth: 0,
      avgPerMonth: 0,
      totalBookings: 0,
      totalHours: 0,
      activeCaregivers: 0
    })
  },
  /** Chart period (week/month/year) */
  period: {
    type: String,
    default: 'month'
  },
  /** Selected year */
  selectedYear: {
    type: [Number, String],
    default: () => new Date().getFullYear()
  },
  /** Available years for selection */
  availableYears: {
    type: Array,
    default: () => {
      const currentYear = new Date().getFullYear();
      return [currentYear - 2, currentYear - 1, currentYear, currentYear + 1];
    }
  },
  /** Chart data */
  chartData: {
    type: Object,
    default: () => ({
      labels: [],
      data: []
    })
  },
  /** Whether PDF export is in progress */
  exporting: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['export-pdf', 'update:period', 'update:selected-year']);

// Local state
const spendingChart = ref(null);
let chartInstance = null;

const localPeriod = ref(props.period);
const localYear = ref(props.selectedYear);

// Watch for prop changes
watch(() => props.period, (val) => {
  localPeriod.value = val;
});

watch(() => props.selectedYear, (val) => {
  localYear.value = val;
});

// Emit changes to parent
watch(localPeriod, (val) => {
  emit('update:period', val);
});

watch(localYear, (val) => {
  emit('update:selected-year', val);
});

// Watch chart data changes
watch(() => props.chartData, () => {
  updateChart();
}, { deep: true });

/**
 * Format number with commas
 */
const formatNumber = (num) => {
  return (num || 0).toLocaleString();
};

/**
 * Initialize or update chart
 */
const updateChart = () => {
  if (!spendingChart.value) return;

  const ctx = spendingChart.value.getContext('2d');
  
  if (chartInstance) {
    chartInstance.destroy();
  }

  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: props.chartData.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [{
        label: 'Spending',
        data: props.chartData.data || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        borderColor: '#2196F3',
        backgroundColor: 'rgba(33, 150, 243, 0.1)',
        tension: 0.4,
        fill: true
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
          callbacks: {
            label: (context) => `$${context.raw.toLocaleString()}`
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: (value) => `$${value.toLocaleString()}`
          }
        }
      }
    }
  });
};

onMounted(() => {
  updateChart();
});

onBeforeUnmount(() => {
  if (chartInstance) {
    chartInstance.destroy();
  }
});
</script>

<style scoped>
.transaction-stats {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.stat-item {
  text-align: center;
  padding: 8px 16px;
}

.stat-amount {
  font-size: 1.5rem;
  font-weight: 700;
}

.stat-label-text {
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 4px;
}

.hidden-xs {
  display: block;
}

@media (max-width: 600px) {
  .hidden-xs {
    display: none;
  }
  
  .transaction-stats {
    flex-direction: column;
  }
}

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
  * {
    transition: none !important;
  }
}
</style>
