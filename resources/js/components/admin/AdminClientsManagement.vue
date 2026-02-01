<template>
  <div class="admin-clients-management">
    <!-- Search and Filter Controls -->
    <div class="mb-6">
      <v-row class="align-center">
        <v-col cols="12" md="3">
          <v-text-field 
            v-model="search" 
            placeholder="Search clients..." 
            prepend-inner-icon="mdi-magnify" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Search clients"
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
            @click="$emit('add-client')"
            :block="isMobile"
            aria-label="Add new client"
          >
            Add Client
          </v-btn>
        </v-col>
      </v-row>
    </div>

    <!-- Clients Data Card -->
    <v-card elevation="0">
      <v-card-title class="card-header pa-4 pa-md-8 d-flex justify-space-between align-center flex-wrap ga-2">
        <span class="section-title error--text">Clients</span>
        <div class="d-flex align-center ga-2">
          <v-chip v-if="totalCount > 0" color="grey" variant="tonal" size="small">
            {{ totalCount }} {{ totalCount === 1 ? 'client' : 'clients' }}
          </v-chip>
          <v-btn 
            v-if="selectedClients.length > 0" 
            color="error" 
            variant="outlined" 
            prepend-icon="mdi-delete" 
            size="small" 
            @click="deleteSelected"
            :aria-label="`Delete ${selectedClients.length} selected clients`"
          >
            Delete ({{ selectedClients.length }})
          </v-btn>
        </div>
      </v-card-title>
      
      <!-- Desktop Table View -->
      <v-data-table 
        v-if="!isMobile" 
        v-model="selectedClients" 
        :headers="headers" 
        :items="filteredClients" 
        :items-per-page="10" 
        show-select 
        item-value="id" 
        class="elevation-0" 
        density="compact"
        :loading="loading"
        hover
      >
        <template #item.name="{ item }">
          <div class="d-flex align-center">
            <v-avatar color="primary" size="32" class="mr-2">
              <span class="text-white text-caption font-weight-bold">
                {{ getInitials(item.first_name, item.last_name) }}
              </span>
            </v-avatar>
            <span class="font-weight-medium">{{ item.first_name }} {{ item.last_name }}</span>
          </div>
        </template>
        
        <template #item.zip_code="{ item }">
          {{ item.zip_code || 'Unknown ZIP' }}
        </template>
        
        <template #item.location="{ item }">
          {{ item.place_indicator || item.location || 'Unknown' }}
        </template>
        
        <template #item.status="{ item }">
          <v-chip
            :color="getStatusColor(item.status)"
            size="small"
            class="font-weight-bold"
            :style="isPending(item.status) ? pendingStyle : ''"
            :prepend-icon="getStatusIcon(item.status)"
          >
            {{ item.status }}
          </v-chip>
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
                  @click="$emit('view-client', item)"
                  :aria-label="`View ${item.first_name} ${item.last_name}`"
                />
              </template>
            </v-tooltip>
            
            <v-tooltip text="Edit client" location="top">
              <template #activator="{ props }">
                <v-btn 
                  v-bind="props"
                  class="action-btn-edit" 
                  icon="mdi-pencil" 
                  size="small" 
                  variant="text"
                  color="info"
                  @click="$emit('edit-client', item)"
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
          <v-progress-circular indeterminate color="primary" />
        </div>
        
        <!-- Empty State -->
        <div v-else-if="filteredClients.length === 0" class="text-center py-8">
          <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-account-group</v-icon>
          <p class="text-grey">No clients found</p>
        </div>
        
        <!-- Client Cards -->
        <v-card 
          v-for="item in paginatedClients" 
          :key="item.id" 
          class="mobile-data-card mb-3" 
          elevation="2"
        >
          <v-card-text class="pa-0">
            <div 
              class="mobile-card-header d-flex justify-space-between align-center pa-3" 
              style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);"
            >
              <div class="d-flex align-center">
                <v-checkbox 
                  v-model="selectedClients" 
                  :value="item.id" 
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
                <span class="mobile-card-label text-grey-darken-1">ZIP Code:</span>
                <span class="mobile-card-value">{{ item.zip_code || 'Unknown' }}</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2">
                <span class="mobile-card-label text-grey-darken-1">Location:</span>
                <span class="mobile-card-value">{{ item.place_indicator || item.location || 'Unknown' }}</span>
              </div>
            </div>
            
            <div class="mobile-card-actions d-flex justify-center ga-2 pa-3" style="background: #f9fafb; border-top: 1px solid #e5e7eb;">
              <v-btn 
                color="primary" 
                variant="tonal" 
                size="small" 
                prepend-icon="mdi-eye" 
                @click="$emit('view-client', item)"
              >
                View
              </v-btn>
              <v-btn 
                color="info" 
                variant="tonal" 
                size="small" 
                prepend-icon="mdi-pencil" 
                @click="$emit('edit-client', item)"
              >
                Edit
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
        
        <!-- Mobile Pagination -->
        <div v-if="filteredClients.length > mobilePageSize" class="d-flex justify-center mt-4">
          <v-pagination
            v-model="mobilePage"
            :length="Math.ceil(filteredClients.length / mobilePageSize)"
            :total-visible="5"
            density="compact"
            rounded="circle"
            color="primary"
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
  /** Clients data */
  clients: {
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
  'add-client',
  'view-client',
  'edit-client',
  'delete-clients'
]);

// ============================================================================
// State
// ============================================================================
const search = ref('');
const locationFilter = ref('All');
const statusFilter = ref('All');
const selectedClients = ref([]);
const mobilePage = ref(1);
const mobilePageSize = 10;

// ============================================================================
// Constants
// ============================================================================
const statusOptions = ['All', 'Active', 'Inactive'];
const pendingStyle = 'background-color: #f59e0b !important; color: #ffffff !important;';

const headers = [
  { title: 'Name', key: 'name', sortable: true },
  { title: 'Email', key: 'email', sortable: true },
  { title: 'Phone', key: 'phone', sortable: false },
  { title: 'ZIP Code', key: 'zip_code', sortable: true },
  { title: 'Location', key: 'location', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center', width: '100px' }
];

// ============================================================================
// Computed
// ============================================================================
const filteredClients = computed(() => {
  let result = [...props.clients];
  
  if (search.value) {
    const s = search.value.toLowerCase().trim();
    result = result.filter(c => 
      (c.first_name && c.first_name.toLowerCase().includes(s)) ||
      (c.last_name && c.last_name.toLowerCase().includes(s)) ||
      (c.email && c.email.toLowerCase().includes(s))
    );
  }
  
  if (locationFilter.value && locationFilter.value !== 'All') {
    result = result.filter(c => 
      c.location === locationFilter.value ||
      c.place_indicator === locationFilter.value
    );
  }
  
  if (statusFilter.value && statusFilter.value !== 'All') {
    result = result.filter(c => 
      (c.status || '').toLowerCase() === statusFilter.value.toLowerCase()
    );
  }
  
  return result;
});

const totalCount = computed(() => filteredClients.value.length);

const paginatedClients = computed(() => {
  const start = (mobilePage.value - 1) * mobilePageSize;
  return filteredClients.value.slice(start, start + mobilePageSize);
});

// ============================================================================
// Methods
// ============================================================================
function getStatusColor(status) {
  const map = {
    active: 'success',
    inactive: 'grey',
    pending: 'warning'
  };
  return map[(status || '').toLowerCase()] || 'grey';
}

function getStatusIcon(status) {
  const map = {
    active: 'mdi-check-circle',
    inactive: 'mdi-minus-circle',
    pending: 'mdi-clock-outline'
  };
  return map[(status || '').toLowerCase()] || 'mdi-circle';
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
  // Handled by parent
}

// Reset page on filter change
watch([search, locationFilter, statusFilter], () => {
  mobilePage.value = 1;
});

watch(() => props.clients, () => {
  selectedClients.value = [];
});

// ============================================================================
// Expose
// ============================================================================
defineExpose({
  clearSelection: () => { selectedClients.value = []; },
  getSelectedClients: () => [...selectedClients.value]
});
</script>

<style scoped>
.section-title {
  font-size: 1.25rem;
  font-weight: 700;
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
