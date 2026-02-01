<template>
  <div class="admin-staff-management">
    <!-- Filters -->
    <div class="mb-6">
      <v-row class="align-center">
        <v-col cols="12" md="3">
          <v-text-field 
            v-model="search" 
            placeholder="Search admin staff..." 
            prepend-inner-icon="mdi-magnify" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Search admin staff"
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select 
            v-model="statusFilter" 
            :items="['All', 'Active', 'Inactive']" 
            label="All Status" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Filter by status"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-btn color="error" prepend-icon="mdi-plus" @click="$emit('add-staff')">Add Admin Staff</v-btn>
        </v-col>
      </v-row>
    </div>
    
    <v-card elevation="0">
      <v-card-title class="card-header pa-4 pa-md-8 d-flex justify-space-between align-center flex-wrap ga-2">
        <span class="section-title error--text">Admin Staff Management</span>
        <v-btn 
          v-if="selectedStaff.length > 0" 
          color="error" 
          variant="outlined" 
          prepend-icon="mdi-delete" 
          size="small" 
          @click="handleDeleteSelected"
        >
          Delete ({{ selectedStaff.length }})
        </v-btn>
      </v-card-title>
      
      <!-- Desktop Table View -->
      <v-data-table 
        v-if="!isMobile"
        v-model="selectedStaff" 
        :headers="headers" 
        :items="filteredStaff" 
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
        <template v-slot:item.email_verified="{ item }">
          <v-chip :color="item.email_verified === 'Yes' ? 'success' : 'warning'" size="small">
            <v-icon size="14" class="mr-1">{{ item.email_verified === 'Yes' ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
            {{ item.email_verified }}
          </v-chip>
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
        <template v-slot:item.actions="{ item }">
          <div class="action-buttons d-flex gap-1">
            <v-btn 
              class="action-btn-view" 
              icon="mdi-eye" 
              size="small" 
              @click="$emit('view-staff', item)"
              aria-label="View staff details"
            />
            <v-btn 
              class="action-btn-unapprove" 
              icon="mdi-undo" 
              size="small" 
              @click="$emit('unapprove', item)" 
              title="Unapprove (set back to pending)"
              aria-label="Unapprove staff"
            />
            <v-btn 
              class="action-btn-edit" 
              icon="mdi-pencil" 
              size="small" 
              @click="$emit('edit-staff', item)"
              aria-label="Edit staff"
            />
          </div>
        </template>
      </v-data-table>
      
      <!-- Mobile Card View -->
      <div v-else class="mobile-cards-container pa-3">
        <div v-if="filteredStaff.length === 0" class="text-center py-8 text-grey">
          No admin staff found
        </div>
        <v-card 
          v-for="item in paginatedStaff" 
          :key="item.id" 
          class="mobile-data-card mb-3" 
          elevation="2"
        >
          <v-card-text class="pa-0">
            <div 
              class="mobile-card-header d-flex justify-space-between align-center pa-3" 
              style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);"
            >
              <div class="d-flex align-center">
                <v-checkbox 
                  v-model="selectedStaff" 
                  :value="item.id" 
                  hide-details 
                  density="compact" 
                  color="white" 
                  class="mr-2"
                  aria-label="Select staff"
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
                <span class="mobile-card-value">{{ item.place_indicator || item.location || 'Unknown' }}</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2">
                <span class="mobile-card-label text-grey-darken-1">Email Verified:</span>
                <v-chip :color="item.email_verified === 'Yes' ? 'success' : 'warning'" size="small">
                  {{ item.email_verified }}
                </v-chip>
              </div>
            </div>
            <div class="mobile-card-actions d-flex justify-center ga-2 pa-3" style="background: #f9fafb; border-top: 1px solid #e5e7eb;">
              <v-btn color="primary" variant="tonal" size="small" prepend-icon="mdi-eye" @click="$emit('view-staff', item)">View</v-btn>
              <v-btn color="warning" variant="tonal" size="small" prepend-icon="mdi-undo" @click="$emit('unapprove', item)">Undo</v-btn>
              <v-btn color="info" variant="tonal" size="small" prepend-icon="mdi-pencil" @click="$emit('edit-staff', item)">Edit</v-btn>
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
 * AdminStaffManagement - Admin staff management section
 * Provides CRUD operations for admin staff users with role-based permissions
 */

const props = defineProps({
  /** List of admin staff members */
  adminStaff: {
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
  'add-staff',
  'view-staff',
  'edit-staff',
  'unapprove',
  'delete-staff'
]);

// Local state
const search = ref('');
const statusFilter = ref('All');
const selectedStaff = ref([]);
const currentPage = ref(1);
const itemsPerPage = 10;

const headers = ref([
  { title: 'Name', key: 'first_name' },
  { title: 'Email', key: 'email' },
  { title: 'Phone', key: 'phone' },
  { title: 'Location', key: 'location' },
  { title: 'Email Verified', key: 'email_verified' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false }
]);

/**
 * Filter staff based on search and status
 */
const filteredStaff = computed(() => {
  let result = [...props.adminStaff];
  
  // Search filter
  if (search.value) {
    const searchLower = search.value.toLowerCase();
    result = result.filter(s => 
      (s.first_name || '').toLowerCase().includes(searchLower) ||
      (s.last_name || '').toLowerCase().includes(searchLower) ||
      (s.email || '').toLowerCase().includes(searchLower) ||
      (s.phone || '').toLowerCase().includes(searchLower)
    );
  }
  
  // Status filter
  if (statusFilter.value !== 'All') {
    result = result.filter(s => s.status === statusFilter.value);
  }
  
  return result;
});

/**
 * Paginated staff for mobile view
 */
const paginatedStaff = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredStaff.value.slice(start, start + itemsPerPage);
});

/**
 * Total pages for pagination
 */
const totalPages = computed(() => Math.ceil(filteredStaff.value.length / itemsPerPage));

/**
 * Get status chip color
 */
const getStatusColor = (status) => {
  const colors = {
    'Active': 'success',
    'Inactive': 'grey',
    'pending': 'warning',
    'Pending': 'warning'
  };
  return colors[status] || 'grey';
};

/**
 * Get status icon
 */
const getStatusIcon = (status) => {
  const icons = {
    'Active': 'mdi-check-circle',
    'Inactive': 'mdi-close-circle',
    'pending': 'mdi-clock',
    'Pending': 'mdi-clock'
  };
  return icons[status] || 'mdi-help-circle';
};

/**
 * Handle delete selected staff
 */
const handleDeleteSelected = () => {
  emit('delete-staff', [...selectedStaff.value]);
};

/**
 * Clear selection
 */
const clearSelection = () => {
  selectedStaff.value = [];
};

/**
 * Get selected staff
 */
const getSelected = () => [...selectedStaff.value];

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
