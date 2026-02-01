<template>
  <div class="admin-housekeepers-management">
    <!-- Search and Filter Controls -->
    <div class="mb-6">
      <v-row class="align-center">
        <v-col cols="12" md="3">
          <v-text-field 
            v-model="search" 
            placeholder="Search housekeepers..." 
            prepend-inner-icon="mdi-magnify" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Search housekeepers"
            clearable
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select 
            v-model="locationFilter" 
            :items="locationFilterOptions" 
            label="All Locations" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Filter by location"
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select 
            v-model="statusFilter" 
            :items="statusOptions" 
            label="All Status" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Filter by status"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-btn 
            color="error" 
            prepend-icon="mdi-plus" 
            @click="$emit('add-housekeeper')"
            :block="isMobile"
            aria-label="Add new housekeeper"
          >
            Add Housekeeper
          </v-btn>
        </v-col>
      </v-row>
    </div>

    <!-- Housekeepers Data Card -->
    <v-card elevation="0">
      <v-card-title class="card-header pa-4 pa-md-8 d-flex justify-space-between align-center flex-wrap ga-2">
        <span class="section-title deep-purple--text">Housekeepers</span>
        <div class="d-flex align-center ga-2">
          <v-chip v-if="totalCount > 0" color="grey" variant="tonal" size="small">
            {{ totalCount }} {{ totalCount === 1 ? 'housekeeper' : 'housekeepers' }}
          </v-chip>
          <v-btn 
            v-if="selectedHousekeepers.length > 0" 
            color="error" 
            variant="outlined" 
            prepend-icon="mdi-delete" 
            size="small" 
            @click="deleteSelected"
            :aria-label="`Delete ${selectedHousekeepers.length} selected housekeepers`"
          >
            Delete ({{ selectedHousekeepers.length }})
          </v-btn>
        </div>
      </v-card-title>
      
      <!-- Desktop Table View -->
      <v-data-table 
        v-if="!isMobile" 
        v-model="selectedHousekeepers" 
        :headers="headers" 
        :items="filteredHousekeepers" 
        :items-per-page="10" 
        show-select 
        item-value="userId" 
        class="elevation-0" 
        density="compact"
        :loading="loading"
        hover
      >
        <template #item.name="{ item }">
          <div class="d-flex align-center">
            <v-avatar color="#6A1B9A" size="32" class="mr-2">
              <span class="text-white text-caption font-weight-bold">
                {{ getInitials(item.first_name, item.last_name) }}
              </span>
            </v-avatar>
            <span class="font-weight-medium">{{ item.first_name }} {{ item.last_name }}</span>
          </div>
        </template>
        
        <template #item.location="{ item }">
          {{ item.location || 'Unknown' }}
        </template>
        
        <template #item.status="{ item }">
          <v-chip
            :color="getStatusColor(item.status)"
            size="small"
            class="font-weight-bold"
            :style="isPending(item.status) ? pendingStyle : ''"
          >
            {{ item.status }}
          </v-chip>
        </template>
        
        <template #item.rating="{ item }">
          <div class="d-flex align-center">
            <v-rating
              :model-value="parseFloat(item.rating || 0)"
              :length="5"
              :size="18"
              color="amber"
              active-color="amber"
              half-increments
              readonly
              density="compact"
            />
            <span class="ml-1 text-caption">{{ parseFloat(item.rating || 0).toFixed(1) }}</span>
          </div>
        </template>
        
        <template #item.actions="{ item }">
          <div class="action-buttons d-flex ga-1">
            <v-tooltip text="View details" location="top">
              <template #activator="{ props }">
                <v-btn 
                  v-bind="props"
                  class="action-btn-view" 
                  icon="mdi-eye" 
                  size="small" 
                  variant="text"
                  color="primary"
                  @click="$emit('view-housekeeper', item)"
                  :aria-label="`View ${item.first_name} ${item.last_name}`"
                />
              </template>
            </v-tooltip>
            
            <v-tooltip text="Set back to pending" location="top">
              <template #activator="{ props }">
                <v-btn 
                  v-bind="props"
                  class="action-btn-unapprove" 
                  icon="mdi-undo" 
                  size="small" 
                  variant="text"
                  color="warning"
                  @click="$emit('unapprove', item)"
                  :aria-label="`Unapprove ${item.first_name} ${item.last_name}`"
                />
              </template>
            </v-tooltip>
            
            <v-tooltip text="Edit housekeeper" location="top">
              <template #activator="{ props }">
                <v-btn 
                  v-bind="props"
                  class="action-btn-edit" 
                  icon="mdi-pencil" 
                  size="small" 
                  variant="text"
                  color="info"
                  @click="$emit('edit-housekeeper', item)"
                  :aria-label="`Edit ${item.first_name} ${item.last_name}`"
                />
              </template>
            </v-tooltip>
          </div>
        </template>
      </v-data-table>
      
      <!-- Mobile Card View -->
      <div v-else class="mobile-cards-container pa-3">
        <!-- Loading State -->
        <div v-if="loading" class="d-flex justify-center py-8">
          <v-progress-circular indeterminate color="deep-purple" />
        </div>
        
        <!-- Empty State -->
        <div v-else-if="filteredHousekeepers.length === 0" class="text-center py-8">
          <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-broom</v-icon>
          <p class="text-grey">No housekeepers found</p>
        </div>
        
        <!-- Housekeeper Cards -->
        <v-card 
          v-for="item in paginatedHousekeepers" 
          :key="item.userId" 
          class="mobile-data-card mb-3" 
          elevation="2"
        >
          <v-card-text class="pa-0">
            <div 
              class="mobile-card-header d-flex justify-space-between align-center pa-3" 
              style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);"
            >
              <div class="d-flex align-center">
                <v-checkbox 
                  v-model="selectedHousekeepers" 
                  :value="item.userId" 
                  hide-details 
                  density="compact" 
                  color="white" 
                  class="mr-2"
                  :aria-label="`Select ${item.first_name} ${item.last_name}`"
                />
                <span class="text-white font-weight-bold">{{ item.first_name }} {{ item.last_name }}</span>
              </div>
              <v-chip :color="getStatusColor(item.status)" size="small" class="font-weight-bold">
                {{ item.status }}
              </v-chip>
            </div>
            
            <div class="mobile-card-body pa-3">
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Email:</span>
                <span class="mobile-card-value text-primary font-weight-medium" style="word-break: break-all;">
                  {{ item.email }}
                </span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Phone:</span>
                <span class="mobile-card-value">{{ item.phone || 'N/A' }}</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Location:</span>
                <span class="mobile-card-value">{{ item.location || 'Unknown' }}</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between align-center py-2">
                <span class="mobile-card-label text-grey-darken-1">Rating:</span>
                <div class="d-flex align-center">
                  <v-rating 
                    :model-value="parseFloat(item.rating || 0)" 
                    :length="5" 
                    :size="16" 
                    color="amber" 
                    active-color="amber" 
                    half-increments 
                    readonly 
                    density="compact"
                  />
                  <span class="ml-1 text-caption">{{ parseFloat(item.rating || 0).toFixed(1) }}</span>
                </div>
              </div>
            </div>
            
            <div class="mobile-card-actions d-flex justify-center ga-2 pa-3" style="background: #f9fafb; border-top: 1px solid #e5e7eb;">
              <v-btn 
                color="primary" 
                variant="tonal" 
                size="small" 
                prepend-icon="mdi-eye" 
                @click="$emit('view-housekeeper', item)"
              >
                View
              </v-btn>
              <v-btn 
                color="warning" 
                variant="tonal" 
                size="small" 
                prepend-icon="mdi-undo" 
                @click="$emit('unapprove', item)"
              >
                Undo
              </v-btn>
              <v-btn 
                color="info" 
                variant="tonal" 
                size="small" 
                prepend-icon="mdi-pencil" 
                @click="$emit('edit-housekeeper', item)"
              >
                Edit
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
        
        <!-- Mobile Pagination -->
        <div v-if="filteredHousekeepers.length > mobilePageSize" class="d-flex justify-center mt-4">
          <v-pagination
            v-model="mobilePage"
            :length="Math.ceil(filteredHousekeepers.length / mobilePageSize)"
            :total-visible="5"
            density="compact"
            rounded="circle"
            color="deep-purple"
          />
        </div>
      </div>
    </v-card>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

// ============================================================================
// Props
// ============================================================================
const props = defineProps({
  /** Housekeepers data */
  housekeepers: {
    type: Array,
    default: () => []
  },
  /** Loading state */
  loading: {
    type: Boolean,
    default: false
  },
  /** Mobile view */
  isMobile: {
    type: Boolean,
    default: false
  },
  /** Location options */
  locationFilterOptions: {
    type: Array,
    default: () => ['All']
  }
});

// ============================================================================
// Emits
// ============================================================================
defineEmits([
  'add-housekeeper',
  'view-housekeeper',
  'edit-housekeeper',
  'unapprove',
  'delete-housekeepers'
]);

// ============================================================================
// State
// ============================================================================
const search = ref('');
const locationFilter = ref('All');
const statusFilter = ref('All');
const selectedHousekeepers = ref([]);
const mobilePage = ref(1);
const mobilePageSize = 10;

// ============================================================================
// Constants
// ============================================================================
const statusOptions = ['All', 'Active', 'Assigned', 'Inactive'];
const pendingStyle = 'background-color: #f59e0b !important; color: #ffffff !important;';

const headers = [
  { title: 'Name', key: 'name', sortable: true },
  { title: 'Email', key: 'email', sortable: true },
  { title: 'Phone', key: 'phone', sortable: false },
  { title: 'Location', key: 'location', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Rating', key: 'rating', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center', width: '140px' }
];

// ============================================================================
// Computed
// ============================================================================
const filteredHousekeepers = computed(() => {
  let result = [...props.housekeepers];
  
  if (search.value) {
    const s = search.value.toLowerCase().trim();
    result = result.filter(h => 
      (h.first_name && h.first_name.toLowerCase().includes(s)) ||
      (h.last_name && h.last_name.toLowerCase().includes(s)) ||
      (h.email && h.email.toLowerCase().includes(s))
    );
  }
  
  if (locationFilter.value && locationFilter.value !== 'All') {
    result = result.filter(h => h.location === locationFilter.value);
  }
  
  if (statusFilter.value && statusFilter.value !== 'All') {
    result = result.filter(h => 
      (h.status || '').toLowerCase() === statusFilter.value.toLowerCase()
    );
  }
  
  return result;
});

const totalCount = computed(() => filteredHousekeepers.value.length);

const paginatedHousekeepers = computed(() => {
  const start = (mobilePage.value - 1) * mobilePageSize;
  return filteredHousekeepers.value.slice(start, start + mobilePageSize);
});

// ============================================================================
// Methods
// ============================================================================
function getStatusColor(status) {
  const map = {
    active: 'success',
    assigned: 'info',
    inactive: 'grey',
    pending: 'warning'
  };
  return map[(status || '').toLowerCase()] || 'grey';
}

function isPending(status) {
  return (status || '').toLowerCase() === 'pending';
}

function getInitials(firstName, lastName) {
  const f = firstName ? firstName[0] : '';
  const l = lastName ? lastName[0] : '';
  return (f + l).toUpperCase() || '?';
}

function deleteSelected() {
  // Emit parent handler
}

// Reset page on filter change
watch([search, locationFilter, statusFilter], () => {
  mobilePage.value = 1;
});

watch(() => props.housekeepers, () => {
  selectedHousekeepers.value = [];
});

// ============================================================================
// Expose
// ============================================================================
defineExpose({
  clearSelection: () => { selectedHousekeepers.value = []; },
  getSelectedHousekeepers: () => [...selectedHousekeepers.value]
});
</script>

<style scoped>
.section-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #7c3aed;
}

.card-header {
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.action-buttons {
  display: flex;
  align-items: center;
  gap: 4px;
}

.mobile-data-card {
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.mobile-data-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

.mobile-card-label {
  font-size: 0.85rem;
  font-weight: 500;
}

.mobile-card-value {
  font-size: 0.85rem;
}

@media (prefers-reduced-motion: reduce) {
  .mobile-data-card {
    transition: none;
  }
}
</style>
