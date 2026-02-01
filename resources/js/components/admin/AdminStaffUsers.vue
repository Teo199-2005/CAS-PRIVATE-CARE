<template>
  <div class="admin-staff-users">
    <!-- Header with search and filters -->
    <v-card elevation="0" class="mb-4 filter-card">
      <v-card-text class="pa-4">
        <v-row align="center">
          <v-col cols="12" sm="6" md="4">
            <v-text-field
              v-model="searchQuery"
              label="Search users..."
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              hide-details
              clearable
              @update:model-value="onSearch"
            />
          </v-col>
          <v-col cols="6" sm="3" md="2">
            <v-select
              v-model="roleFilter"
              :items="roleOptions"
              label="Role"
              variant="outlined"
              density="compact"
              hide-details
              @update:model-value="onFilterChange"
            />
          </v-col>
          <v-col cols="6" sm="3" md="2">
            <v-select
              v-model="statusFilter"
              :items="statusOptions"
              label="Status"
              variant="outlined"
              density="compact"
              hide-details
              @update:model-value="onFilterChange"
            />
          </v-col>
          <v-col cols="12" md="4" class="d-flex gap-2 justify-end">
            <v-btn 
              color="primary" 
              variant="elevated"
              @click="$emit('add-user')"
            >
              <v-icon start>mdi-plus</v-icon>
              Add User
            </v-btn>
            <v-btn 
              color="secondary" 
              variant="tonal"
              @click="$emit('refresh')"
            >
              <v-icon start>mdi-refresh</v-icon>
              Refresh
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- User Stats -->
    <v-row class="mb-4">
      <v-col v-for="stat in userStats" :key="stat.label" cols="6" sm="3">
        <v-card elevation="0" class="stat-card" :class="stat.colorClass">
          <v-card-text class="pa-3 text-center">
            <v-icon :color="stat.iconColor" size="24" class="mb-1">{{ stat.icon }}</v-icon>
            <div class="stat-value">{{ stat.value }}</div>
            <div class="stat-label">{{ stat.label }}</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Desktop Table -->
    <v-card v-if="!isMobile" elevation="0" class="users-table-card">
      <v-data-table
        :headers="tableHeaders"
        :items="filteredUsers"
        :loading="loading"
        :items-per-page="10"
        class="elevation-0"
        hover
      >
        <!-- Avatar & Name -->
        <template #item.name="{ item }">
          <div class="d-flex align-center">
            <v-avatar size="36" :color="getRoleColor(item.role)" class="mr-3">
              <v-img v-if="item.avatar" :src="item.avatar" />
              <span v-else class="text-white text-caption">{{ getInitials(item.name) }}</span>
            </v-avatar>
            <div>
              <div class="user-name">{{ item.name }}</div>
              <div class="user-email">{{ item.email }}</div>
            </div>
          </div>
        </template>

        <!-- Role Badge -->
        <template #item.role="{ item }">
          <v-chip :color="getRoleColor(item.role)" size="small">
            <v-icon start size="14">{{ getRoleIcon(item.role) }}</v-icon>
            {{ formatRole(item.role) }}
          </v-chip>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <v-chip 
            :color="item.status === 'active' ? 'success' : 'grey'" 
            size="small" 
            variant="tonal"
          >
            <v-icon start size="12">
              {{ item.status === 'active' ? 'mdi-check-circle' : 'mdi-pause-circle' }}
            </v-icon>
            {{ item.status === 'active' ? 'Active' : 'Inactive' }}
          </v-chip>
        </template>

        <!-- Last Login -->
        <template #item.last_login="{ item }">
          <span class="text-body-2">{{ formatLastLogin(item.last_login) }}</span>
        </template>

        <!-- Created Date -->
        <template #item.created_at="{ item }">
          <span class="text-body-2">{{ formatDate(item.created_at) }}</span>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="action-buttons">
            <v-btn 
              icon 
              size="small" 
              variant="text"
              color="primary"
              @click="$emit('view', item)"
            >
              <v-icon size="18">mdi-eye</v-icon>
              <v-tooltip activator="parent" location="top">View Profile</v-tooltip>
            </v-btn>
            <v-btn 
              icon 
              size="small" 
              variant="text"
              color="warning"
              @click="$emit('edit', item)"
            >
              <v-icon size="18">mdi-pencil</v-icon>
              <v-tooltip activator="parent" location="top">Edit User</v-tooltip>
            </v-btn>
            <v-menu>
              <template #activator="{ props }">
                <v-btn 
                  icon 
                  size="small" 
                  variant="text"
                  v-bind="props"
                >
                  <v-icon size="18">mdi-dots-vertical</v-icon>
                </v-btn>
              </template>
              <v-list density="compact">
                <v-list-item @click="$emit('toggle-status', item)">
                  <v-list-item-title>
                    <v-icon start size="18">
                      {{ item.status === 'active' ? 'mdi-pause' : 'mdi-play' }}
                    </v-icon>
                    {{ item.status === 'active' ? 'Deactivate' : 'Activate' }}
                  </v-list-item-title>
                </v-list-item>
                <v-list-item @click="$emit('reset-password', item)">
                  <v-list-item-title>
                    <v-icon start size="18">mdi-lock-reset</v-icon>
                    Reset Password
                  </v-list-item-title>
                </v-list-item>
                <v-divider class="my-1" />
                <v-list-item 
                  class="text-error"
                  @click="$emit('delete', item)"
                >
                  <v-list-item-title>
                    <v-icon start size="18" color="error">mdi-delete</v-icon>
                    Delete User
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </div>
        </template>

        <!-- Empty State -->
        <template #no-data>
          <div class="empty-state py-8">
            <v-icon size="64" color="grey-lighten-1">mdi-account-group</v-icon>
            <h3 class="mt-4 text-h6">No Users Found</h3>
            <p class="text-body-2 text-muted">
              {{ searchQuery || roleFilter !== 'all' ? 'Try adjusting your filters' : 'No users registered yet' }}
            </p>
            <v-btn color="primary" class="mt-4" @click="$emit('add-user')">
              <v-icon start>mdi-plus</v-icon>
              Add First User
            </v-btn>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Mobile Card View -->
    <div v-else class="mobile-users-list">
      <v-card 
        v-for="user in filteredUsers" 
        :key="user.id"
        elevation="0"
        class="mobile-user-card mb-3"
        @click="$emit('view', user)"
      >
        <v-card-text class="pa-4">
          <!-- Header -->
          <div class="d-flex justify-space-between align-center mb-3">
            <div class="d-flex align-center">
              <v-avatar size="48" :color="getRoleColor(user.role)" class="mr-3">
                <v-img v-if="user.avatar" :src="user.avatar" />
                <span v-else class="text-white">{{ getInitials(user.name) }}</span>
              </v-avatar>
              <div>
                <div class="font-weight-medium">{{ user.name }}</div>
                <div class="text-caption text-muted">{{ user.email }}</div>
              </div>
            </div>
            <v-menu>
              <template #activator="{ props }">
                <v-btn icon size="small" variant="text" v-bind="props" @click.stop>
                  <v-icon>mdi-dots-vertical</v-icon>
                </v-btn>
              </template>
              <v-list density="compact">
                <v-list-item @click.stop="$emit('edit', user)">
                  <v-list-item-title>
                    <v-icon start size="18">mdi-pencil</v-icon>
                    Edit
                  </v-list-item-title>
                </v-list-item>
                <v-list-item @click.stop="$emit('toggle-status', user)">
                  <v-list-item-title>
                    <v-icon start size="18">
                      {{ user.status === 'active' ? 'mdi-pause' : 'mdi-play' }}
                    </v-icon>
                    {{ user.status === 'active' ? 'Deactivate' : 'Activate' }}
                  </v-list-item-title>
                </v-list-item>
                <v-divider />
                <v-list-item class="text-error" @click.stop="$emit('delete', user)">
                  <v-list-item-title>
                    <v-icon start size="18" color="error">mdi-delete</v-icon>
                    Delete
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </div>

          <!-- Details -->
          <div class="d-flex gap-2 flex-wrap">
            <v-chip :color="getRoleColor(user.role)" size="small">
              <v-icon start size="12">{{ getRoleIcon(user.role) }}</v-icon>
              {{ formatRole(user.role) }}
            </v-chip>
            <v-chip 
              :color="user.status === 'active' ? 'success' : 'grey'" 
              size="small" 
              variant="tonal"
            >
              {{ user.status === 'active' ? 'Active' : 'Inactive' }}
            </v-chip>
          </div>

          <!-- Footer -->
          <div class="mt-3 d-flex justify-space-between text-caption text-muted">
            <span>Joined {{ formatDate(user.created_at) }}</span>
            <span>Last login: {{ formatLastLogin(user.last_login) }}</span>
          </div>
        </v-card-text>
      </v-card>

      <!-- Empty Mobile State -->
      <div v-if="filteredUsers.length === 0" class="empty-state py-8 text-center">
        <v-icon size="64" color="grey-lighten-1">mdi-account-group</v-icon>
        <h3 class="mt-4 text-h6">No Users Found</h3>
        <p class="text-body-2 text-muted">Try adjusting your filters</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

// Props
const props = defineProps({
  users: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
});

// Emits
defineEmits(['view', 'edit', 'add-user', 'delete', 'toggle-status', 'reset-password', 'refresh']);

// State
const searchQuery = ref('');
const roleFilter = ref('all');
const statusFilter = ref('all');
const isMobile = ref(window.innerWidth <= 768);

// Options
const roleOptions = [
  { title: 'All Roles', value: 'all' },
  { title: 'Clients', value: 'client' },
  { title: 'Caregivers', value: 'caregiver' },
  { title: 'Housekeepers', value: 'housekeeper' },
  { title: 'Admin', value: 'admin' },
  { title: 'Staff', value: 'staff' },
];

const statusOptions = [
  { title: 'All', value: 'all' },
  { title: 'Active', value: 'active' },
  { title: 'Inactive', value: 'inactive' },
];

const tableHeaders = [
  { title: 'User', key: 'name', width: '250px' },
  { title: 'Role', key: 'role', width: '140px' },
  { title: 'Status', key: 'status', width: '100px' },
  { title: 'Last Login', key: 'last_login', width: '140px' },
  { title: 'Joined', key: 'created_at', width: '120px' },
  { title: 'Actions', key: 'actions', width: '150px', sortable: false },
];

// Computed
const filteredUsers = computed(() => {
  let result = [...props.users];
  
  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(u => 
      u.name?.toLowerCase().includes(query) ||
      u.email?.toLowerCase().includes(query) ||
      u.phone?.includes(query)
    );
  }
  
  // Role filter
  if (roleFilter.value !== 'all') {
    result = result.filter(u => u.role === roleFilter.value);
  }
  
  // Status filter
  if (statusFilter.value !== 'all') {
    result = result.filter(u => u.status === statusFilter.value);
  }
  
  return result;
});

const userStats = computed(() => {
  const total = props.users.length;
  const clients = props.users.filter(u => u.role === 'client').length;
  const caregivers = props.users.filter(u => u.role === 'caregiver' || u.role === 'housekeeper').length;
  const active = props.users.filter(u => u.status === 'active').length;
  
  return [
    { 
      label: 'Total Users', 
      value: total, 
      icon: 'mdi-account-group',
      iconColor: 'primary',
      colorClass: 'bg-primary-light' 
    },
    { 
      label: 'Clients', 
      value: clients, 
      icon: 'mdi-account',
      iconColor: 'info',
      colorClass: 'bg-info-light' 
    },
    { 
      label: 'Caregivers', 
      value: caregivers, 
      icon: 'mdi-account-heart',
      iconColor: 'success',
      colorClass: 'bg-success-light' 
    },
    { 
      label: 'Active', 
      value: active, 
      icon: 'mdi-check-circle',
      iconColor: 'warning',
      colorClass: 'bg-warning-light' 
    },
  ];
});

// Methods
const onSearch = () => {};
const onFilterChange = () => {};

const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatRole = (role) => {
  const labels = {
    client: 'Client',
    caregiver: 'Caregiver',
    housekeeper: 'Housekeeper',
    admin: 'Admin',
    staff: 'Staff',
  };
  return labels[role] || role;
};

const getRoleColor = (role) => {
  const colors = {
    client: 'primary',
    caregiver: 'success',
    housekeeper: 'info',
    admin: 'error',
    staff: 'warning',
  };
  return colors[role] || 'grey';
};

const getRoleIcon = (role) => {
  const icons = {
    client: 'mdi-account',
    caregiver: 'mdi-account-heart',
    housekeeper: 'mdi-broom',
    admin: 'mdi-shield-account',
    staff: 'mdi-account-tie',
  };
  return icons[role] || 'mdi-account';
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const formatLastLogin = (date) => {
  if (!date) return 'Never';
  const now = new Date();
  const loginDate = new Date(date);
  const diffMs = now - loginDate;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);
  
  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  return formatDate(date);
};

// Resize handler
const handleResize = () => {
  isMobile.value = window.innerWidth <= 768;
};

onMounted(() => {
  window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
});
</script>

<style scoped>
.admin-staff-users {
  width: 100%;
}

.filter-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
}

.stat-card {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.stat-label {
  font-size: 0.75rem;
  color: #4b5563;
}

.bg-primary-light { background-color: rgba(59, 130, 246, 0.1) !important; }
.bg-info-light { background-color: rgba(6, 182, 212, 0.1) !important; }
.bg-success-light { background-color: rgba(16, 185, 129, 0.1) !important; }
.bg-warning-light { background-color: rgba(245, 158, 11, 0.1) !important; }

.users-table-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
}

.user-name {
  font-weight: 500;
  font-size: 0.875rem;
}

.user-email {
  font-size: 0.75rem;
  color: #4b5563;
}

.action-buttons {
  display: flex;
  gap: 2px;
}

.action-buttons .v-btn {
  min-width: 44px !important;
  min-height: 44px !important;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.mobile-user-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
}

.text-muted {
  color: #4b5563 !important;
}

.gap-2 {
  gap: 8px;
}
</style>
