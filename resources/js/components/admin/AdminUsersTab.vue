<template>
  <v-container fluid class="admin-users-tab pa-0">
    <!-- Users Management Section -->
    <v-row>
      <v-col cols="12">
        <v-card elevation="2" rounded="lg">
          <v-card-title class="d-flex align-center py-4 px-6">
            <v-icon color="primary" class="mr-3">mdi-account-group</v-icon>
            <span class="text-h6 font-weight-bold">User Management</span>
            <v-spacer></v-spacer>
            <v-text-field
              v-model="userSearch"
              prepend-inner-icon="mdi-magnify"
              label="Search users..."
              single-line
              hide-details
              density="compact"
              variant="outlined"
              class="max-width-300"
              clearable
              autocomplete="off"
              aria-label="Search users"
              @update:model-value="debouncedSearch"
            ></v-text-field>
          </v-card-title>
          
          <v-divider></v-divider>
          
          <!-- User Type Tabs -->
          <v-tabs v-model="userTypeTab" color="primary" bg-color="grey-lighten-4">
            <v-tab value="all" aria-label="Show all users">All Users</v-tab>
            <v-tab value="client" aria-label="Show clients only">Clients</v-tab>
            <v-tab value="caregiver" aria-label="Show caregivers only">Caregivers</v-tab>
            <v-tab value="housekeeper" aria-label="Show housekeepers only">Housekeepers</v-tab>
            <v-tab value="marketing" aria-label="Show marketing partners only">Marketing</v-tab>
            <v-tab value="training_center" aria-label="Show training centers only">Training Centers</v-tab>
          </v-tabs>
          
          <v-card-text class="pa-0">
            <v-data-table
              :headers="userHeaders"
              :items="filteredUsers"
              :loading="loading"
              :search="userSearch"
              :items-per-page="10"
              class="elevation-0"
              hover
            >
              <!-- Name Column with Avatar -->
              <template v-slot:item.name="{ item }">
                <div class="d-flex align-center py-2">
                  <v-avatar size="36" :color="getUserTypeColor(item.user_type)" class="mr-3">
                    <span class="text-white text-caption font-weight-bold">{{ getInitials(item.name) }}</span>
                  </v-avatar>
                  <div>
                    <div class="font-weight-medium">{{ item.name || 'N/A' }}</div>
                    <div class="text-caption text-grey">ID: {{ item.id }}</div>
                  </div>
                </div>
              </template>
              
              <!-- User Type Column -->
              <template v-slot:item.user_type="{ item }">
                <v-chip :color="getUserTypeColor(item.user_type)" size="small" variant="flat">
                  <v-icon start size="14">{{ getUserTypeIcon(item.user_type) }}</v-icon>
                  {{ formatUserType(item.user_type) }}
                </v-chip>
              </template>
              
              <!-- Status Column -->
              <template v-slot:item.status="{ item }">
                <v-chip
                  :color="getStatusColor(item.status)"
                  size="small"
                  variant="flat"
                >
                  {{ item.status || 'pending' }}
                </v-chip>
              </template>
              
              <!-- Created Date Column -->
              <template v-slot:item.created_at="{ item }">
                {{ formatDate(item.created_at) }}
              </template>
              
              <!-- Actions Column -->
              <template v-slot:item.actions="{ item }">
                <v-btn
                  icon="mdi-eye"
                  size="small"
                  variant="text"
                  color="primary"
                  @click="viewUser(item)"
                  :aria-label="`View ${item.name}`"
                ></v-btn>
                <v-btn
                  icon="mdi-pencil"
                  size="small"
                  variant="text"
                  color="warning"
                  @click="editUser(item)"
                  :aria-label="`Edit ${item.name}`"
                ></v-btn>
                <v-btn
                  icon="mdi-delete"
                  size="small"
                  variant="text"
                  color="error"
                  @click="confirmDeleteUser(item)"
                  :aria-label="`Delete ${item.name}`"
                ></v-btn>
              </template>
              
              <!-- Loading State -->
              <template v-slot:loading>
                <v-skeleton-loader type="table-row@5"></v-skeleton-loader>
              </template>
              
              <!-- Empty State -->
              <template v-slot:no-data>
                <div class="text-center py-8">
                  <v-icon size="64" color="grey-lighten-1">mdi-account-off</v-icon>
                  <p class="text-grey mt-4">No users found</p>
                </div>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    
    <!-- User Detail Dialog -->
    <v-dialog v-model="userDialog" max-width="600" role="dialog" aria-modal="true" aria-labelledby="user-dialog-title">
      <v-card>
        <v-card-title id="user-dialog-title" class="d-flex align-center">
          <v-icon class="mr-2">mdi-account</v-icon>
          {{ editingUser ? 'Edit User' : 'User Details' }}
          <v-spacer></v-spacer>
          <v-btn icon="mdi-close" variant="text" @click="userDialog = false" aria-label="Close dialog"></v-btn>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text v-if="selectedUser" class="pa-6">
          <v-row>
            <v-col cols="12" class="text-center mb-4">
              <v-avatar size="80" :color="getUserTypeColor(selectedUser.user_type)">
                <span class="text-h5 text-white">{{ getInitials(selectedUser.name) }}</span>
              </v-avatar>
              <h3 class="mt-3">{{ selectedUser.name }}</h3>
              <v-chip :color="getUserTypeColor(selectedUser.user_type)" size="small" class="mt-2">
                {{ formatUserType(selectedUser.user_type) }}
              </v-chip>
            </v-col>
            <v-col cols="12" sm="6">
              <p class="text-caption text-grey mb-1">Email</p>
              <p class="font-weight-medium">{{ selectedUser.email }}</p>
            </v-col>
            <v-col cols="12" sm="6">
              <p class="text-caption text-grey mb-1">Phone</p>
              <p class="font-weight-medium">{{ selectedUser.phone || 'N/A' }}</p>
            </v-col>
            <v-col cols="12" sm="6">
              <p class="text-caption text-grey mb-1">Status</p>
              <v-chip :color="getStatusColor(selectedUser.status)" size="small">
                {{ selectedUser.status || 'pending' }}
              </v-chip>
            </v-col>
            <v-col cols="12" sm="6">
              <p class="text-caption text-grey mb-1">Joined</p>
              <p class="font-weight-medium">{{ formatDate(selectedUser.created_at) }}</p>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions v-if="editingUser">
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="userDialog = false">Cancel</v-btn>
          <v-btn color="primary" variant="flat" @click="saveUser">Save Changes</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="400" role="alertdialog" aria-modal="true" aria-labelledby="delete-dialog-title">
      <v-card>
        <v-card-title id="delete-dialog-title" class="text-error">
          <v-icon color="error" class="mr-2">mdi-alert</v-icon>
          Confirm Deletion
        </v-card-title>
        <v-card-text>
          Are you sure you want to delete <strong>{{ userToDelete?.name }}</strong>? This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="deleteDialog = false">Cancel</v-btn>
          <v-btn color="error" variant="flat" @click="deleteUser" :loading="deleting">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
/**
 * AdminUsersTab Component
 * Manages user listing, viewing, editing, and deletion for admin dashboard
 */

import { ref, computed } from 'vue';
import { useDebounce } from '@/composables/useDebounce';

// Props
const props = defineProps({
  users: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false }
});

// Emits
const emit = defineEmits(['refresh', 'view', 'edit', 'delete', 'save']);

// Composables
const { debounce } = useDebounce();

// State
const userSearch = ref('');
const userTypeTab = ref('all');
const userDialog = ref(false);
const deleteDialog = ref(false);
const selectedUser = ref(null);
const userToDelete = ref(null);
const editingUser = ref(false);
const deleting = ref(false);

// Table Headers
const userHeaders = [
  { title: 'Name', key: 'name', sortable: true },
  { title: 'Email', key: 'email', sortable: true },
  { title: 'Type', key: 'user_type', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Created', key: 'created_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center' }
];

// Computed
const filteredUsers = computed(() => {
  if (userTypeTab.value === 'all') return props.users;
  return props.users.filter(u => u.user_type === userTypeTab.value);
});

// Methods
const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const getStatusColor = (status) => {
  const colors = {
    active: 'success',
    approved: 'success',
    pending: 'warning',
    rejected: 'error',
    suspended: 'grey'
  };
  return colors[status] || 'grey';
};

const getUserTypeColor = (type) => {
  const colors = {
    client: 'teal',
    caregiver: 'green',
    housekeeper: 'deep-purple',
    marketing: 'orange',
    training_center: 'purple',
    admin: 'red'
  };
  return colors[type] || 'grey';
};

const getUserTypeIcon = (type) => {
  const icons = {
    client: 'mdi-account',
    caregiver: 'mdi-heart-pulse',
    housekeeper: 'mdi-broom',
    marketing: 'mdi-bullhorn',
    training_center: 'mdi-school',
    admin: 'mdi-shield-account'
  };
  return icons[type] || 'mdi-account';
};

const formatUserType = (type) => {
  const types = {
    client: 'Client',
    caregiver: 'Caregiver',
    housekeeper: 'Housekeeper',
    marketing: 'Marketing',
    training_center: 'Training Center',
    admin: 'Admin'
  };
  return types[type] || type;
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric' 
  });
};

const debouncedSearch = debounce(() => {
  // Search logic handled by v-data-table
}, 300);

const viewUser = (user) => {
  selectedUser.value = user;
  editingUser.value = false;
  userDialog.value = true;
  emit('view', user);
};

const editUser = (user) => {
  selectedUser.value = { ...user };
  editingUser.value = true;
  userDialog.value = true;
  emit('edit', user);
};

const saveUser = () => {
  emit('save', selectedUser.value);
  userDialog.value = false;
};

const confirmDeleteUser = (user) => {
  userToDelete.value = user;
  deleteDialog.value = true;
};

const deleteUser = async () => {
  deleting.value = true;
  try {
    emit('delete', userToDelete.value);
    deleteDialog.value = false;
  } finally {
    deleting.value = false;
  }
};

// Expose for parent
defineExpose({
  refresh: () => emit('refresh')
});
</script>

<style scoped>
.max-width-300 {
  max-width: 300px;
}

.admin-users-tab :deep(.v-data-table) {
  border-radius: 0;
}

/* Focus visible for accessibility */
.admin-users-tab :deep(.v-btn:focus-visible) {
  outline: 2px solid var(--v-theme-primary);
  outline-offset: 2px;
}
</style>
