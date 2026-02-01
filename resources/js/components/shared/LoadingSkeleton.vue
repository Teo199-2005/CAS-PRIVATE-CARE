<template>
  <div class="loading-skeleton-wrapper">
    <!-- Card Skeleton -->
    <template v-if="type === 'card'">
      <v-card class="skeleton-card" :elevation="0">
        <v-skeleton-loader
          :type="cardType"
          :loading="true"
          class="skeleton-card-content"
        />
      </v-card>
    </template>

    <!-- Stats Card Skeleton -->
    <template v-else-if="type === 'stats'">
      <v-card class="skeleton-stats-card pa-4" :elevation="0">
        <div class="d-flex align-center mb-3">
          <v-skeleton-loader type="avatar" :loading="true" class="mr-3" />
          <div class="flex-grow-1">
            <v-skeleton-loader type="text" :loading="true" width="60%" />
          </div>
        </div>
        <v-skeleton-loader type="heading" :loading="true" width="40%" />
        <v-skeleton-loader type="text" :loading="true" width="80%" class="mt-2" />
      </v-card>
    </template>

    <!-- Table Skeleton -->
    <template v-else-if="type === 'table'">
      <v-card class="skeleton-table" :elevation="0">
        <v-skeleton-loader
          type="table-heading, table-row-divider, table-row@5"
          :loading="true"
        />
      </v-card>
    </template>

    <!-- List Skeleton -->
    <template v-else-if="type === 'list'">
      <div class="skeleton-list">
        <v-card v-for="i in count" :key="i" class="mb-3" :elevation="0">
          <v-skeleton-loader
            type="list-item-avatar-three-line"
            :loading="true"
          />
        </v-card>
      </div>
    </template>

    <!-- Dashboard Grid Skeleton -->
    <template v-else-if="type === 'dashboard'">
      <v-row class="skeleton-dashboard">
        <v-col v-for="i in 4" :key="i" cols="12" sm="6" md="3">
          <v-card class="pa-4" :elevation="0">
            <v-skeleton-loader type="card" :loading="true" />
          </v-card>
        </v-col>
      </v-row>
    </template>

    <!-- Form Skeleton -->
    <template v-else-if="type === 'form'">
      <div class="skeleton-form">
        <v-skeleton-loader
          v-for="i in count"
          :key="i"
          type="text, chip"
          :loading="true"
          class="mb-4"
        />
        <v-skeleton-loader type="button" :loading="true" width="120px" />
      </div>
    </template>

    <!-- Profile Skeleton -->
    <template v-else-if="type === 'profile'">
      <v-card class="skeleton-profile pa-6" :elevation="0">
        <div class="d-flex flex-column align-center mb-6">
          <v-skeleton-loader type="avatar" :loading="true" />
          <v-skeleton-loader type="heading" :loading="true" class="mt-4" width="60%" />
          <v-skeleton-loader type="text" :loading="true" class="mt-2" width="40%" />
        </div>
        <v-divider class="mb-6" />
        <v-skeleton-loader type="paragraph" :loading="true" />
      </v-card>
    </template>

    <!-- Chart Skeleton -->
    <template v-else-if="type === 'chart'">
      <v-card class="skeleton-chart pa-4" :elevation="0">
        <v-skeleton-loader type="heading" :loading="true" width="40%" class="mb-4" />
        <div class="chart-placeholder">
          <v-skeleton-loader type="image" :loading="true" height="200px" />
        </div>
      </v-card>
    </template>

    <!-- Text Lines Skeleton -->
    <template v-else-if="type === 'text'">
      <div class="skeleton-text">
        <v-skeleton-loader
          v-for="i in count"
          :key="i"
          type="text"
          :loading="true"
          :width="getRandomWidth()"
        />
      </div>
    </template>

    <!-- Default/Custom Skeleton -->
    <template v-else>
      <v-skeleton-loader
        :type="customType || 'card'"
        :loading="true"
        v-bind="$attrs"
      />
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /**
   * Type of skeleton to display
   * @values card, stats, table, list, dashboard, form, profile, chart, text, custom
   */
  type: {
    type: String,
    default: 'card',
    validator: (value) => [
      'card', 'stats', 'table', 'list', 'dashboard', 
      'form', 'profile', 'chart', 'text', 'custom'
    ].includes(value)
  },
  /**
   * Number of items for list/form/text types
   */
  count: {
    type: Number,
    default: 3
  },
  /**
   * Custom skeleton type for Vuetify's v-skeleton-loader
   */
  customType: {
    type: String,
    default: ''
  },
  /**
   * Card skeleton variant
   * @values default, article, actions
   */
  cardVariant: {
    type: String,
    default: 'default'
  }
});

const cardType = computed(() => {
  switch (props.cardVariant) {
    case 'article':
      return 'card, article';
    case 'actions':
      return 'card, actions';
    default:
      return 'card';
  }
});

const getRandomWidth = () => {
  const widths = ['60%', '75%', '90%', '80%', '70%'];
  return widths[Math.floor(Math.random() * widths.length)];
};
</script>

<style scoped>
.loading-skeleton-wrapper {
  width: 100%;
}

.skeleton-card,
.skeleton-stats-card,
.skeleton-table,
.skeleton-profile,
.skeleton-chart {
  background: rgba(var(--v-theme-surface), 0.8);
  border-radius: 12px;
  overflow: hidden;
}

.skeleton-card-content {
  border-radius: 12px;
}

.skeleton-list .v-card {
  border-radius: 8px;
}

.skeleton-dashboard .v-card {
  border-radius: 12px;
}

.chart-placeholder {
  background: linear-gradient(90deg, 
    rgba(var(--v-theme-surface-variant), 0.3) 0%,
    rgba(var(--v-theme-surface-variant), 0.1) 50%,
    rgba(var(--v-theme-surface-variant), 0.3) 100%);
  border-radius: 8px;
  overflow: hidden;
}

/* Pulse animation for skeleton */
:deep(.v-skeleton-loader__bone) {
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0% {
    opacity: 0.4;
  }
  50% {
    opacity: 0.7;
  }
  100% {
    opacity: 0.4;
  }
}

/* Dark mode adjustments */
.v-theme--dark .skeleton-card,
.v-theme--dark .skeleton-stats-card,
.v-theme--dark .skeleton-table,
.v-theme--dark .skeleton-profile,
.v-theme--dark .skeleton-chart {
  background: rgba(var(--v-theme-surface), 0.6);
}
</style>
