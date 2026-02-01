<template>
  <div class="admin-users-management">
    <!-- Search and Filter Controls -->
    <div class="mb-6">
      <v-row class="align-center">
        <v-col cols="12" md="3">
          <v-text-field 
            v-model="userSearch" 
            placeholder="Search users..." 
            prepend-inner-icon="mdi-magnify" 
            variant="outlined" 
            density="compact" 
            hide-details
            aria-label="Search users"
            clearable
            @update:model-value="emitSearchChange"
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select 
            v-model="userType" 
            :items="userTypeOptions" 
            variant="outlined" 
            density="compact" 
            hide-details
            label="User Type"
            aria-label="Filter by user type"
            @update:model-value="emitFilterChange"
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select 
            v-model="userStatus" 
            :items="userStatusOptions" 
            variant="outlined" 
            density="compact" 
            hide-details
            label="Status"
            aria-label="Filter by status"
            @update:model-value="emitFilterChange"
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
            @update:model-value="emitFilterChange"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-btn 
            color="error" 
            prepend-icon="mdi-plus" 
            @click="openAddUserDialog"
            :block="isMobile"
            aria-label="Add new user"
          >
            Add User
          </v-btn>
        </v-col>
      </v-row>
    </div>

    <!-- Users Data Card -->
    <v-card elevation="0" class="users-card">
      <v-card-title class="card-header pa-4 pa-md-8 d-flex justify-space-between align-center flex-wrap ga-2">
        <span class="section-title error--text">Users</span>
        <div class="d-flex align-center ga-2">
          <v-chip v-if="totalCount > 0" color="grey" variant="tonal" size="small">
            {{ totalCount }} {{ totalCount === 1 ? 'user' : 'users' }}
          </v-chip>
          <v-btn 
            v-if="selectedUsers.length > 0" 
            color="error" 
            variant="outlined" 
            prepend-icon="mdi-delete" 
            @click="deleteSelectedUsers" 
            :size="isMobile ? 'small' : 'default'"
            :aria-label="`Delete ${selectedUsers.length} selected users`"
          >
            Delete Selected ({{ selectedUsers.length }})
          </v-btn>
        </div>
      </v-card-title>
      
      <!-- Desktop Table View -->
      <v-data-table 
        v-if="!isMobile" 
        v-model="selectedUsers" 
        :headers="userHeaders" 
        :items="filteredUsers" 
        :items-per-page="10" 
        show-select 
        item-value="id" 
        class="elevation-0" 
        density="compact"
        :loading="loading"
        :no-data-text="noDataText"
        hover
      >
        <template #item.name="{ item }">
          <div class="d-flex align-center">
            <v-avatar :color="getAvatarColor(item.type || item.role)" size="32" class="mr-2">
              <span class="text-white text-caption font-weight-bold">
                {{ getInitials(item.name) }}
              </span>
            </v-avatar>
            <span class="font-weight-medium">{{ item.name }}</span>
          </div>
        </template>
        
        <template #item.status="{ item }">
          <v-chip
            :color="getUserStatusColor(item.status)"
            size="small"
            class="font-weight-bold"
            :style="isPending(item.status) ? pendingStyle : ''"
            :prepend-icon="getStatusIcon(item.status)"
          >
            {{ item.status }}
          </v-chip>
        </template>
        
        <template #item.type="{ item }">
          <v-chip
            :color="getRoleColor(item.type || item.role)"
            size="small"
            variant="tonal"
          >
            {{ item.type || item.role }}
          </v-chip>
        </template>
        
        <template #item.joined="{ item }">
          <span class="text-caption text-grey-darken-1">
            {{ formatDate(item.joined || item.created_at) }}
          </span>
        </template>
        
        <template #item.actions="{ item }">
          <div class="action-buttons d-flex ga-1">
            <v-tooltip text="Edit user" location="top">
              <template #activator="{ props }">
                <v-btn 
                  v-bind="props"
                  class="action-btn-edit" 
                  icon="mdi-pencil" 
                  size="small" 
                  variant="text"
                  color="primary"
                  @click="editUser(item)"
                  :aria-label="`Edit ${item.name}`"
                />
              </template>
            </v-tooltip>
            
            <v-tooltip :text="item.status === 'Suspended' ? 'Reactivate user' : 'Suspend user'" location="top">
              <template #activator="{ props }">
                <v-btn 
                  v-bind="props"
                  class="action-btn-suspend" 
                  :icon="item.status === 'Suspended' ? 'mdi-account-check' : 'mdi-account-cancel'" 
                  size="small" 
                  variant="text"
                  :color="item.status === 'Suspended' ? 'success' : 'error'"
                  @click="toggleSuspendUser(item)"
                  :aria-label="item.status === 'Suspended' ? `Reactivate ${item.name}` : `Suspend ${item.name}`"
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
          <v-progress-circular indeterminate color="error" />
        </div>
        
        <!-- Empty State -->
        <div v-else-if="filteredUsers.length === 0" class="text-center py-8">
          <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-account-off</v-icon>
          <p class="text-grey">{{ noDataText }}</p>
        </div>
        
        <!-- User Cards -->
        <v-card 
          v-for="item in paginatedUsers" 
          :key="item.id" 
          class="mobile-data-card mb-3" 
          elevation="2" 
          rounded="lg"
        >
          <v-card-text class="pa-0">
            <!-- Card Header -->
            <div 
              class="mobile-card-header d-flex align-center justify-space-between pa-3" 
              style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);"
            >
              <div class="d-flex align-center">
                <v-checkbox 
                  v-model="selectedUsers" 
                  :value="item.id" 
                  hide-details 
                  density="compact" 
                  color="white" 
                  class="mr-2"
                  :aria-label="`Select ${item.name}`"
                />
                <span class="text-white font-weight-bold text-body-1">{{ item.name }}</span>
              </div>
              <v-chip
                :color="getUserStatusColor(item.status)"
                size="small"
                class="font-weight-bold"
                :style="isPending(item.status) ? pendingStyle : ''"
              >
                {{ item.status }}
              </v-chip>
            </div>
            
            <!-- Card Body -->
            <div class="mobile-card-body pa-3">
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Email:</span>
                <span class="mobile-card-value text-primary" style="word-break: break-all; font-size: 0.85rem;">
                  {{ item.email }}
                </span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Type:</span>
                <v-chip :color="getRoleColor(item.type || item.role)" size="small" variant="tonal">
                  {{ item.type || item.role }}
                </v-chip>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2">
                <span class="mobile-card-label text-grey-darken-1">Joined:</span>
                <span class="mobile-card-value">{{ formatDate(item.joined || item.created_at) }}</span>
              </div>
            </div>
            
            <!-- Card Actions -->
            <div class="mobile-card-actions d-flex justify-center ga-2 pa-3" style="background: #f9fafb; border-top: 1px solid #e5e7eb;">
              <v-btn 
                color="primary" 
                variant="tonal" 
                size="small" 
                prepend-icon="mdi-pencil" 
                @click="editUser(item)"
                :aria-label="`Edit ${item.name}`"
              >
                Edit
              </v-btn>
              <v-btn 
                :color="item.status === 'Suspended' ? 'success' : 'error'" 
                variant="tonal" 
                size="small" 
                :prepend-icon="item.status === 'Suspended' ? 'mdi-account-check' : 'mdi-account-cancel'" 
                @click="toggleSuspendUser(item)"
                :aria-label="item.status === 'Suspended' ? `Reactivate ${item.name}` : `Suspend ${item.name}`"
              >
                {{ item.status === 'Suspended' ? 'Reactivate' : 'Suspend' }}
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
        
        <!-- Mobile Pagination -->
        <div v-if="filteredUsers.length > mobilePageSize" class="d-flex justify-center mt-4">
          <v-pagination
            v-model="mobilePage"
            :length="Math.ceil(filteredUsers.length / mobilePageSize)"
            :total-visible="5"
            density="compact"
            rounded="circle"
            color="error"
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
  /** All users data */
  users: {
    type: Array,
    default: () => []
  },
  /** Whether currently loading */
  loading: {
    type: Boolean,
    default: false
  },
  /** Whether in mobile view */
  isMobile: {
    type: Boolean,
    default: false
  },
  /** Location filter options */
  locationFilterOptions: {
    type: Array,
    default: () => ['All']
  },
  /** Initial search value */
  initialSearch: {
    type: String,
    default: ''
  },
  /** Initial type filter */
  initialType: {
    type: String,
    default: 'All'
  },
  /** Initial status filter */
  initialStatus: {
    type: String,
    default: 'All'
  },
  /** Initial location filter */
  initialLocation: {
    type: String,
    default: 'All'
  }
});

// ============================================================================
// Emits
// ============================================================================
const emit = defineEmits([
  'add-user',
  'edit-user',
  'suspend-user',
  'reactivate-user',
  'delete-users',
  'search-change',
  'filter-change'
]);

// ============================================================================
// Reactive State
// ============================================================================
const userSearch = ref(props.initialSearch);
const userType = ref(props.initialType);
const userStatus = ref(props.initialStatus);
const locationFilter = ref(props.initialLocation);
const selectedUsers = ref([]);
const mobilePage = ref(1);
const mobilePageSize = 10;

// ============================================================================
// Constants
// ============================================================================
const userTypeOptions = ['All', 'Clients', 'Caregivers', 'Admins'];
const userStatusOptions = ['All', 'Active', 'Inactive', 'Suspended'];
const pendingStyle = 'background-color: #f59e0b !important; color: #ffffff !important;';

const userHeaders = [
  { title: 'Name', key: 'name', sortable: true },
  { title: 'Email', key: 'email', sortable: true },
  { title: 'Type', key: 'type', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Joined', key: 'joined', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center', width: '120px' }
];

// ============================================================================
// Computed Properties
// ============================================================================
const filteredUsers = computed(() => {
  let result = [...props.users];
  
  // Search filter
  if (userSearch.value) {
    const search = userSearch.value.toLowerCase().trim();
    result = result.filter(user => 
      (user.name && user.name.toLowerCase().includes(search)) ||
      (user.email && user.email.toLowerCase().includes(search))
    );
  }
  
  // Type filter
  if (userType.value && userType.value !== 'All') {
    const type = userType.value.replace(/s$/, '').toLowerCase(); // Remove trailing 's'
    result = result.filter(user => {
      const userRole = (user.type || user.role || '').toLowerCase();
      return userRole.includes(type);
    });
  }
  
  // Status filter
  if (userStatus.value && userStatus.value !== 'All') {
    result = result.filter(user => 
      (user.status || '').toLowerCase() === userStatus.value.toLowerCase()
    );
  }
  
  // Location filter
  if (locationFilter.value && locationFilter.value !== 'All') {
    result = result.filter(user =>
      user.location === locationFilter.value ||
      user.zip_code === locationFilter.value
    );
  }
  
  return result;
});

const totalCount = computed(() => filteredUsers.value.length);

const paginatedUsers = computed(() => {
  const start = (mobilePage.value - 1) * mobilePageSize;
  const end = start + mobilePageSize;
  return filteredUsers.value.slice(start, end);
});

const noDataText = computed(() => {
  if (userSearch.value || userType.value !== 'All' || userStatus.value !== 'All' || locationFilter.value !== 'All') {
    return 'No users match your filters';
  }
  return 'No users found';
});

// ============================================================================
// Methods
// ============================================================================
function getUserStatusColor(status) {
  const statusMap = {
    active: 'success',
    inactive: 'grey',
    suspended: 'error',
    pending: 'warning'
  };
  return statusMap[(status || '').toLowerCase()] || 'grey';
}

function getStatusIcon(status) {
  const iconMap = {
    active: 'mdi-check-circle',
    inactive: 'mdi-minus-circle',
    suspended: 'mdi-cancel',
    pending: 'mdi-clock-outline'
  };
  return iconMap[(status || '').toLowerCase()] || 'mdi-circle';
}

function getRoleColor(role) {
  const roleMap = {
    admin: 'error',
    caregiver: 'primary',
    client: 'success',
    housekeeper: 'purple',
    marketing: 'orange'
  };
  return roleMap[(role || '').toLowerCase()] || 'grey';
}

function getAvatarColor(role) {
  return getRoleColor(role);
}

function getInitials(name) {
  if (!name) return '?';
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2);
}

function isPending(status) {
  return (status || '').toLowerCase() === 'pending';
}

function formatDate(date) {
  if (!date) return 'N/A';
  try {
    return new Date(date).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  } catch {
    return date;
  }
}

function openAddUserDialog() {
  emit('add-user');
}

function editUser(user) {
  emit('edit-user', user);
}

function toggleSuspendUser(user) {
  if ((user.status || '').toLowerCase() === 'suspended') {
    emit('reactivate-user', user);
  } else {
    emit('suspend-user', user);
  }
}

function deleteSelectedUsers() {
  if (selectedUsers.value.length > 0) {
    emit('delete-users', [...selectedUsers.value]);
  }
}

function emitSearchChange() {
  emit('search-change', userSearch.value);
}

function emitFilterChange() {
  emit('filter-change', {
    type: userType.value,
    status: userStatus.value,
    location: locationFilter.value
  });
}

// Reset mobile page when filters change
watch([userSearch, userType, userStatus, locationFilter], () => {
  mobilePage.value = 1;
});

// Clear selection when data changes
watch(() => props.users, () => {
  selectedUsers.value = [];
});

// ============================================================================
// Expose
// ============================================================================
defineExpose({
  clearSelection: () => { selectedUsers.value = []; },
  getSelectedUsers: () => [...selectedUsers.value]
});
</script>

<style scoped>
.admin-users-management {
  width: 100%;
}

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

.mobile-cards-container {
  min-height: 200px;
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

/* Focus visible styles for accessibility */
.v-btn:focus-visible {
  outline: 3px solid #1e40af !important;
  outline-offset: 2px !important;
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .mobile-data-card {
    transition: none;
  }
}
</style>
