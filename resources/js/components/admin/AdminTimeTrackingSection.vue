<template>
  <div class="admin-time-tracking-section">
    <!-- Filters -->
    <div class="mb-6">
      <v-row class="align-center">
        <v-col cols="12" md="3">
          <v-text-field 
            v-model="localSearch" 
            placeholder="Search clients..." 
            prepend-inner-icon="mdi-magnify" 
            variant="outlined" 
            density="compact" 
            hide-details
            @update:model-value="$emit('update:search', $event)"
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select 
            v-model="localDateFilter" 
            :items="dateFilterOptions" 
            variant="outlined" 
            density="compact" 
            hide-details
            @update:model-value="$emit('update:dateFilter', $event)"
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select 
            v-model="localStatusFilter" 
            :items="statusFilterOptions" 
            variant="outlined" 
            density="compact" 
            hide-details
            @update:model-value="$emit('update:statusFilter', $event)"
          />
        </v-col>
      </v-row>
    </div>

    <v-card elevation="0">
      <v-card-title class="card-header pa-8">
        <div class="d-flex justify-space-between align-center w-100 flex-wrap ga-2">
          <span class="section-title error--text">Client Time Tracking</span>
          <div class="d-flex gap-2 flex-wrap">
            <v-btn 
              color="info" 
              prepend-icon="mdi-history" 
              @click="$emit('view-history')"
              :size="isMobile ? 'small' : 'default'"
            >
              View History
            </v-btn>
            <v-btn 
              color="error" 
              prepend-icon="mdi-refresh" 
              @click="$emit('refresh')"
              :loading="refreshing"
              :size="isMobile ? 'small' : 'default'"
            >
              Refresh Data
            </v-btn>
          </div>
        </div>
      </v-card-title>

      <!-- Desktop Table View -->
      <v-data-table 
        v-if="!isMobile" 
        :headers="headers" 
        :items="items" 
        :items-per-page="15" 
        class="elevation-0 table-no-checkbox"
      >
        <template v-slot:item.client="{ item }">
          <div class="font-weight-bold text-h6">{{ item.client }}</div>
        </template>

        <template v-slot:item.caregivers="{ item }">
          <div class="d-flex flex-column gap-2">
            <div v-if="item.caregivers.length === 0" class="text-grey text-caption">
              No caregivers assigned
            </div>
            <template v-else>
              <!-- Show first 2 caregivers directly -->
              <div 
                v-for="caregiver in item.caregivers.slice(0, 2)" 
                :key="caregiver.id" 
                class="caregiver-row pa-2" 
                style="border: 1px solid #e0e0e0; border-radius: 4px;"
              >
                <div class="d-flex align-center justify-space-between">
                  <span class="font-weight-medium">{{ caregiver.name }}</span>
                  <v-chip 
                    :color="caregiver.status === 'Clocked In' ? 'success' : 'default'" 
                    size="small" 
                    variant="flat"
                  >
                    {{ caregiver.status }}
                  </v-chip>
                </div>
                <div v-if="caregiver.status === 'Clocked In'" class="text-body-2 text-grey mt-1" style="font-size: 0.95rem;">
                  Clock In: {{ caregiver.clockIn || 'N/A' }} | Today: {{ formatHours(caregiver.todayHours) }} | Week: {{ formatHours(caregiver.weekHours) }}
                </div>
              </div>
              
              <!-- Dropdown for remaining caregivers if more than 2 -->
              <v-menu v-if="item.caregivers.length > 2" location="bottom">
                <template v-slot:activator="{ props }">
                  <v-btn 
                    v-bind="props"
                    variant="outlined"
                    size="small"
                    color="primary"
                    prepend-icon="mdi-chevron-down"
                  >
                    View {{ item.caregivers.length - 2 }} More Caregiver{{ item.caregivers.length - 2 > 1 ? 's' : '' }}
                  </v-btn>
                </template>
                <v-list max-height="400" style="max-width: 500px;">
                  <v-list-item 
                    v-for="caregiver in item.caregivers.slice(2)" 
                    :key="caregiver.id"
                    class="pa-3"
                  >
                    <v-list-item-title class="font-weight-medium mb-2">{{ caregiver.name }}</v-list-item-title>
                    <div class="d-flex align-center mb-2">
                      <v-chip 
                        :color="caregiver.status === 'Clocked In' ? 'success' : 'default'" 
                        size="small" 
                        variant="flat"
                      >
                        {{ caregiver.status }}
                      </v-chip>
                    </div>
                    <div v-if="caregiver.status === 'Clocked In'" class="text-body-2 text-grey" style="font-size: 0.95rem;">
                      Clock In: {{ caregiver.clockIn || 'N/A' }} | Today: {{ formatHours(caregiver.todayHours) }} | Week: {{ formatHours(caregiver.weekHours) }}
                    </div>
                  </v-list-item>
                </v-list>
              </v-menu>
            </template>
          </div>
        </template>

        <template v-slot:item.status="{ item }">
          <v-chip 
            :color="item.status === 'Active' ? 'success' : 'grey-darken-1'" 
            size="small" 
            class="font-weight-bold"
            variant="flat"
          >
            {{ item.status }}
          </v-chip>
        </template>

        <template v-slot:item.actions="{ item }">
          <div class="action-buttons">
            <v-btn 
              class="action-btn-view" 
              icon="mdi-eye" 
              size="small" 
              variant="text"
              @click="$emit('view-details', item)"
            />
          </div>
        </template>
      </v-data-table>

      <!-- Mobile Card View -->
      <div v-else class="mobile-cards-container pa-3">
        <div v-if="items.length === 0" class="text-center py-8 text-grey">
          No time tracking records found
        </div>
        <v-card 
          v-for="item in items" 
          :key="item.id" 
          class="mobile-data-card mb-3" 
          elevation="2" 
          rounded="lg"
        >
          <v-card-text class="pa-0">
            <div 
              class="mobile-card-header d-flex align-center justify-space-between pa-3" 
              style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);"
            >
              <span class="text-white font-weight-bold text-body-1">{{ item.client }}</span>
              <v-chip 
                :color="item.status === 'Active' ? 'success' : 'grey-darken-1'" 
                size="small" 
                class="font-weight-bold"
                variant="flat"
              >
                {{ item.status }}
              </v-chip>
            </div>
            <div class="mobile-card-body pa-3">
              <div class="mobile-card-row py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1 d-block mb-2">Assigned Caregivers:</span>
                <div v-if="item.caregivers.length === 0" class="text-grey text-caption">
                  No caregivers assigned
                </div>
                <div v-else class="d-flex flex-column gap-2">
                  <div 
                    v-for="caregiver in item.caregivers" 
                    :key="caregiver.id" 
                    class="pa-2" 
                    style="border: 1px solid #e0e0e0; border-radius: 8px; background: #f9fafb;"
                  >
                    <div class="d-flex align-center justify-space-between flex-wrap ga-1">
                      <span class="font-weight-medium text-body-2">{{ caregiver.name }}</span>
                      <v-chip 
                        :color="caregiver.status === 'Clocked In' ? 'success' : 'default'" 
                        size="x-small" 
                        variant="flat"
                      >
                        {{ caregiver.status }}
                      </v-chip>
                    </div>
                    <div v-if="caregiver.status === 'Clocked In'" class="text-caption text-grey mt-1">
                      <div>Clock In: {{ caregiver.clockIn || 'N/A' }}</div>
                      <div>Today: {{ formatHours(caregiver.todayHours) }} | Week: {{ formatHours(caregiver.weekHours) }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div 
              class="mobile-card-actions d-flex justify-center pa-3" 
              style="background: #f9fafb; border-top: 1px solid #e5e7eb;"
            >
              <v-btn 
                color="primary" 
                variant="tonal" 
                size="small" 
                prepend-icon="mdi-eye" 
                @click="$emit('view-details', item)"
              >
                View Details
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </div>
    </v-card>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

// Props
const props = defineProps({
  items: {
    type: Array,
    required: true,
    default: () => []
  },
  search: {
    type: String,
    default: ''
  },
  dateFilter: {
    type: String,
    default: 'Today'
  },
  statusFilter: {
    type: String,
    default: 'All'
  },
  isMobile: {
    type: Boolean,
    default: false
  },
  refreshing: {
    type: Boolean,
    default: false
  }
});

// Emits
defineEmits([
  'update:search',
  'update:dateFilter',
  'update:statusFilter',
  'view-history',
  'refresh',
  'view-details'
]);

// Local state for filters
const localSearch = ref(props.search);
const localDateFilter = ref(props.dateFilter);
const localStatusFilter = ref(props.statusFilter);

// Watch for prop changes
watch(() => props.search, (newVal) => { localSearch.value = newVal; });
watch(() => props.dateFilter, (newVal) => { localDateFilter.value = newVal; });
watch(() => props.statusFilter, (newVal) => { localStatusFilter.value = newVal; });

// Filter options
const dateFilterOptions = ['Today', 'This Week', 'This Month', 'All Time'];
const statusFilterOptions = ['All', 'Clocked In', 'Clocked Out'];

// Table headers
const headers = [
  { title: 'Client', key: 'client' },
  { title: 'Caregivers', key: 'caregivers', sortable: false },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false }
];

// Helper function
const formatHours = (hours) => {
  if (hours === null || hours === undefined) return '0h 0m';
  const h = Math.floor(hours);
  const m = Math.round((hours - h) * 60);
  return `${h}h ${m}m`;
};
</script>

<style scoped>
.admin-time-tracking-section {
  width: 100%;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
}

.caregiver-row {
  background: #f9fafb;
}

.caregiver-row:hover {
  background: #f1f5f9;
}

.action-btn-view {
  color: #3b82f6;
}

.action-btn-view:hover {
  background: #eff6ff;
}

.mobile-card-label {
  font-size: 0.75rem;
  font-weight: 500;
}

.mobile-data-card {
  overflow: hidden;
}

@media (max-width: 600px) {
  .section-title {
    font-size: 1rem;
  }
}
</style>
