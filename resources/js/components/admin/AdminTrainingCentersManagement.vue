<template>
  <div class="training-centers-management">
    <!-- Filters -->
    <div class="mb-6">
      <v-row class="align-center">
        <v-col cols="12" md="3">
          <v-text-field 
            v-model="search" 
            placeholder="Search training centers..." 
            prepend-inner-icon="mdi-magnify" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Search training centers"
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select 
            v-model="statusFilter" 
            :items="['All', 'Active', 'pending', 'Inactive']" 
            label="All Status" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Filter by status"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-btn color="error" prepend-icon="mdi-plus" @click="$emit('add-center')">
            Add Training Center
          </v-btn>
        </v-col>
      </v-row>
    </div>
    
    <v-card elevation="0">
      <v-card-title class="card-header pa-4 pa-md-8 d-flex justify-space-between align-center flex-wrap ga-2">
        <span class="section-title error--text">Accredited Training Centers</span>
        <v-btn 
          v-if="selectedCenters.length > 0" 
          color="error" 
          variant="outlined" 
          prepend-icon="mdi-delete" 
          size="small" 
          @click="handleDeleteSelected"
        >
          Delete ({{ selectedCenters.length }})
        </v-btn>
      </v-card-title>
      
      <!-- Desktop Table View -->
      <v-data-table 
        v-if="!isMobile" 
        v-model="selectedCenters" 
        :headers="headers" 
        :items="filteredCenters" 
        :items-per-page="10" 
        :loading="loading"
        show-select 
        item-value="id" 
        class="elevation-0" 
        density="compact"
      >
        <template v-slot:item.location="{ item }">
          {{ item.place_indicator || item.location || 'Unknown ZIP' }}
        </template>
        <template v-slot:item.zip_code="{ item }">
          {{ item.zip_code || 'Unknown ZIP' }}
        </template>
        <template v-slot:item.caregiverCount="{ item }">
          <v-chip color="info" size="small">
            <v-icon size="14" class="mr-1">mdi-account-heart</v-icon>
            {{ item.caregiverCount || 0 }}
          </v-chip>
        </template>
        <template v-slot:item.commissionEarned="{ item }">
          <span class="font-weight-bold text-success">${{ item.commissionEarned || 0 }}</span>
        </template>
        <template v-slot:item.status="{ item }">
          <v-chip
            :color="getStatusColor(item.status)"
            size="small"
            class="font-weight-bold"
            :style="(String(item.status).toLowerCase() === 'pending') ? 'background-color: #f59e0b !important; color: #ffffff !important;' : ''"
            :prepend-icon="getStatusIcon(item.status)"
          >
            {{ item.status }}
          </v-chip>
        </template>
        <template v-slot:item.bank_status="{ item }">
          <v-chip :color="item.bank_connected ? 'success' : 'warning'" size="small" variant="flat">
            <v-icon start size="small">{{ item.bank_connected ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
            {{ item.bank_status || (item.bank_connected ? 'Connected' : 'Not Connected') }}
          </v-chip>
        </template>
        <template v-slot:item.actions="{ item }">
          <div class="action-buttons d-flex gap-1">
            <v-btn 
              class="action-btn-view" 
              icon="mdi-eye" 
              size="small" 
              @click="$emit('view-center', item)"
              aria-label="View center details"
            />
            <v-btn 
              class="action-btn-edit" 
              icon="mdi-pencil" 
              size="small" 
              @click="$emit('edit-center', item)"
              aria-label="Edit center"
            />
            <v-btn 
              class="action-btn-unapprove" 
              icon="mdi-undo" 
              size="small" 
              @click="$emit('unapprove', item)"
              title="Unapprove (set back to pending)"
              aria-label="Unapprove center"
            />
          </div>
        </template>
      </v-data-table>
      
      <!-- Mobile Card View -->
      <div v-else class="mobile-cards-container pa-3">
        <div v-if="filteredCenters.length === 0" class="text-center py-8 text-grey">
          No training centers found
        </div>
        <v-card 
          v-for="item in paginatedCenters" 
          :key="item.id" 
          class="mobile-data-card mb-3" 
          elevation="2"
        >
          <v-card-text class="pa-0">
            <div 
              class="mobile-card-header d-flex justify-space-between align-center pa-3" 
              style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%);"
            >
              <div class="d-flex align-center">
                <v-checkbox 
                  v-model="selectedCenters" 
                  :value="item.id" 
                  hide-details 
                  density="compact" 
                  color="white" 
                  class="mr-2"
                  aria-label="Select center"
                />
                <span class="text-white font-weight-bold">{{ item.name }}</span>
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
                <span class="mobile-card-value">{{ item.place_indicator || item.location || 'Unknown' }}</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Caregivers:</span>
                <v-chip color="info" size="x-small">
                  <v-icon size="12" class="mr-1">mdi-account-heart</v-icon>
                  {{ item.caregiverCount || 0 }}
                </v-chip>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Commission:</span>
                <span class="mobile-card-value font-weight-bold text-success">${{ item.commissionEarned || 0 }}</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2">
                <span class="mobile-card-label text-grey-darken-1">Bank Status:</span>
                <v-chip :color="item.bank_connected ? 'success' : 'warning'" size="x-small" variant="flat">
                  <v-icon start size="x-small">{{ item.bank_connected ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
                  {{ item.bank_connected ? 'Connected' : 'Not Connected' }}
                </v-chip>
              </div>
            </div>
            <div class="mobile-card-actions d-flex justify-center ga-2 pa-3" style="background: #f9fafb; border-top: 1px solid #e5e7eb;">
              <v-btn color="primary" variant="tonal" size="small" prepend-icon="mdi-eye" @click="$emit('view-center', item)">View</v-btn>
              <v-btn color="info" variant="tonal" size="small" prepend-icon="mdi-pencil" @click="$emit('edit-center', item)">Edit</v-btn>
              <v-btn color="warning" variant="tonal" size="small" prepend-icon="mdi-undo" @click="$emit('unapprove', item)">Undo</v-btn>
            </div>
          </v-card-text>
        </v-card>
        
        <!-- Mobile Pagination -->
        <v-pagination
          v-if="totalPages > 1"
          v-model="currentPage"
          :length="totalPages"
          :total-visible="5"
          density="compact"
          class="mt-4"
        />
      </div>
    </v-card>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

/**
 * AdminTrainingCentersManagement - Training centers management section
 * Provides CRUD operations for accredited training centers
 */

const props = defineProps({
  /** List of training centers */
  trainingCenters: {
    type: Array,
    default: () => []
  },
  /** Whether the view is on mobile */
  isMobile: {
    type: Boolean,
    default: false
  },
  /** Loading state */
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits([
  'add-center',
  'view-center',
  'edit-center',
  'unapprove',
  'delete-centers'
]);

// Local state
const search = ref('');
const statusFilter = ref('All');
const selectedCenters = ref([]);
const currentPage = ref(1);
const itemsPerPage = 10;

const headers = ref([
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Phone', key: 'phone' },
  { title: 'Location', key: 'location' },
  { title: 'ZIP Code', key: 'zip_code' },
  { title: 'Caregivers', key: 'caregiverCount' },
  { title: 'Commission', key: 'commissionEarned' },
  { title: 'Bank Status', key: 'bank_status' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false }
]);

/**
 * Filter centers based on search and status
 */
const filteredCenters = computed(() => {
  let result = [...props.trainingCenters];
  
  // Search filter
  if (search.value) {
    const searchLower = search.value.toLowerCase();
    result = result.filter(c => 
      (c.name || '').toLowerCase().includes(searchLower) ||
      (c.email || '').toLowerCase().includes(searchLower) ||
      (c.phone || '').toLowerCase().includes(searchLower) ||
      (c.location || '').toLowerCase().includes(searchLower)
    );
  }
  
  // Status filter
  if (statusFilter.value !== 'All') {
    result = result.filter(c => 
      c.status?.toLowerCase() === statusFilter.value.toLowerCase()
    );
  }
  
  return result;
});

/**
 * Paginated centers for mobile view
 */
const paginatedCenters = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredCenters.value.slice(start, start + itemsPerPage);
});

/**
 * Total pages for pagination
 */
const totalPages = computed(() => Math.ceil(filteredCenters.value.length / itemsPerPage));

/**
 * Get status chip color
 */
const getStatusColor = (status) => {
  const statusLower = String(status).toLowerCase();
  const colors = {
    'active': 'success',
    'inactive': 'grey',
    'pending': 'warning'
  };
  return colors[statusLower] || 'grey';
};

/**
 * Get status icon
 */
const getStatusIcon = (status) => {
  const statusLower = String(status).toLowerCase();
  const icons = {
    'active': 'mdi-check-circle',
    'inactive': 'mdi-close-circle',
    'pending': 'mdi-clock'
  };
  return icons[statusLower] || 'mdi-help-circle';
};

/**
 * Handle delete selected centers
 */
const handleDeleteSelected = () => {
  emit('delete-centers', [...selectedCenters.value]);
};

/**
 * Clear selection
 */
const clearSelection = () => {
  selectedCenters.value = [];
};

/**
 * Get selected centers
 */
const getSelected = () => [...selectedCenters.value];

// Expose methods
defineExpose({
  clearSelection,
  getSelected
});
</script>

<style scoped>
.mobile-data-card {
  overflow: hidden;
  border-radius: 12px;
}

.action-btn-view {
  color: #2196F3 !important;
}

.action-btn-edit {
  color: #00BCD4 !important;
}

.action-btn-unapprove {
  color: #FF9800 !important;
}

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
  * {
    transition: none !important;
  }
}
</style>
