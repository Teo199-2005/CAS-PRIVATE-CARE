<template>
  <div class="user-management">
    <v-card elevation="0" rounded="lg">
      <!-- Tabs Header -->
      <v-card-title class="pa-0">
        <v-tabs v-model="activeTab" bg-color="transparent" grow>
          <v-tab value="caregivers" class="text-none">
            <v-icon start>mdi-heart-pulse</v-icon>
            Caregivers
            <v-badge
              v-if="pendingCaregivers > 0"
              :content="pendingCaregivers"
              color="warning"
              inline
              class="ml-2"
            />
          </v-tab>
          <v-tab value="housekeepers" class="text-none">
            <v-icon start>mdi-broom</v-icon>
            Housekeepers
            <v-badge
              v-if="pendingHousekeepers > 0"
              :content="pendingHousekeepers"
              color="warning"
              inline
              class="ml-2"
            />
          </v-tab>
          <v-tab value="clients" class="text-none">
            <v-icon start>mdi-account-group</v-icon>
            Clients
          </v-tab>
        </v-tabs>
      </v-card-title>
      
      <v-divider />
      
      <!-- Search and Filter Bar -->
      <v-card-text class="pa-3">
        <v-row align="center" dense>
          <v-col cols="12" sm="6" md="4">
            <v-text-field
              v-model="search"
              prepend-inner-icon="mdi-magnify"
              label="Search users..."
              density="compact"
              variant="outlined"
              hide-details
              clearable
              @update:model-value="debouncedSearch"
            />
          </v-col>
          <v-col cols="6" sm="3" md="2">
            <v-select
              v-model="statusFilter"
              :items="statusOptions"
              label="Status"
              density="compact"
              variant="outlined"
              hide-details
              clearable
            />
          </v-col>
          <v-col cols="6" sm="3" md="2">
            <v-btn
              color="primary"
              variant="flat"
              block
              @click="$emit('add-user', activeTab)"
            >
              <v-icon start>mdi-plus</v-icon>
              Add
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
      
      <v-divider />
      
      <!-- Tab Content -->
      <v-window v-model="activeTab">
        <!-- Caregivers Tab -->
        <v-window-item value="caregivers">
          <v-data-table
            :headers="caregiverHeaders"
            :items="filteredCaregivers"
            :search="search"
            :loading="loading"
            :items-per-page="itemsPerPage"
            class="elevation-0"
          >
            <template #item.user="{ item }">
              <div class="d-flex align-center py-2">
                <v-avatar size="36" class="mr-3">
                  <v-img 
                    v-if="item.user?.avatar" 
                    :src="item.user.avatar" 
                    :alt="item.user?.name"
                  />
                  <v-icon v-else>mdi-account</v-icon>
                </v-avatar>
                <div>
                  <div class="font-weight-medium">{{ item.user?.name }}</div>
                  <div class="text-caption text-medium-emphasis">
                    {{ item.user?.email }}
                  </div>
                </div>
              </div>
            </template>
            
            <template #item.status="{ item }">
              <v-chip
                :color="getStatusColor(item.user?.status)"
                size="small"
                variant="tonal"
              >
                {{ item.user?.status || 'Unknown' }}
              </v-chip>
            </template>
            
            <template #item.availability="{ item }">
              <v-chip
                :color="getAvailabilityColor(item.availability_status)"
                size="small"
              >
                {{ item.availability_status }}
              </v-chip>
            </template>
            
            <template #item.rating="{ item }">
              <div class="d-flex align-center">
                <v-icon size="16" color="amber">mdi-star</v-icon>
                <span class="ml-1">{{ item.rating?.toFixed(1) || 'N/A' }}</span>
              </div>
            </template>
            
            <template #item.actions="{ item }">
              <v-btn
                icon="mdi-eye"
                size="small"
                variant="text"
                @click="$emit('view', { type: 'caregiver', item })"
              />
              <v-btn
                icon="mdi-pencil"
                size="small"
                variant="text"
                @click="$emit('edit', { type: 'caregiver', item })"
              />
              <v-menu>
                <template #activator="{ props }">
                  <v-btn
                    icon="mdi-dots-vertical"
                    size="small"
                    variant="text"
                    v-bind="props"
                  />
                </template>
                <v-list density="compact">
                  <v-list-item
                    v-if="item.user?.status === 'pending'"
                    prepend-icon="mdi-check"
                    @click="$emit('approve', { type: 'caregiver', item })"
                  >
                    <v-list-item-title>Approve</v-list-item-title>
                  </v-list-item>
                  <v-list-item
                    prepend-icon="mdi-close"
                    @click="$emit('suspend', { type: 'caregiver', item })"
                  >
                    <v-list-item-title>Suspend</v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </template>
          </v-data-table>
        </v-window-item>

        <!-- Housekeepers Tab -->
        <v-window-item value="housekeepers">
          <v-data-table
            :headers="housekeeperHeaders"
            :items="filteredHousekeepers"
            :search="search"
            :loading="loading"
            :items-per-page="itemsPerPage"
            class="elevation-0"
          >
            <template #item.user="{ item }">
              <div class="d-flex align-center py-2">
                <v-avatar size="36" class="mr-3">
                  <v-img 
                    v-if="item.user?.avatar" 
                    :src="item.user.avatar"
                    :alt="item.user?.name"
                  />
                  <v-icon v-else>mdi-account</v-icon>
                </v-avatar>
                <div>
                  <div class="font-weight-medium">{{ item.user?.name }}</div>
                  <div class="text-caption text-medium-emphasis">
                    {{ item.user?.email }}
                  </div>
                </div>
              </div>
            </template>
            
            <template #item.status="{ item }">
              <v-chip
                :color="getStatusColor(item.user?.status)"
                size="small"
                variant="tonal"
              >
                {{ item.user?.status || 'Unknown' }}
              </v-chip>
            </template>
            
            <template #item.actions="{ item }">
              <v-btn
                icon="mdi-eye"
                size="small"
                variant="text"
                @click="$emit('view', { type: 'housekeeper', item })"
              />
              <v-btn
                icon="mdi-pencil"
                size="small"
                variant="text"
                @click="$emit('edit', { type: 'housekeeper', item })"
              />
            </template>
          </v-data-table>
        </v-window-item>

        <!-- Clients Tab -->
        <v-window-item value="clients">
          <v-data-table
            :headers="clientHeaders"
            :items="filteredClients"
            :search="search"
            :loading="loading"
            :items-per-page="itemsPerPage"
            class="elevation-0"
          >
            <template #item.name="{ item }">
              <div class="d-flex align-center py-2">
                <v-avatar size="36" class="mr-3">
                  <v-img 
                    v-if="item.avatar" 
                    :src="item.avatar"
                    :alt="item.name"
                  />
                  <v-icon v-else>mdi-account</v-icon>
                </v-avatar>
                <div>
                  <div class="font-weight-medium">{{ item.name }}</div>
                  <div class="text-caption text-medium-emphasis">
                    {{ item.email }}
                  </div>
                </div>
              </div>
            </template>
            
            <template #item.status="{ item }">
              <v-chip
                :color="getStatusColor(item.status)"
                size="small"
                variant="tonal"
              >
                {{ item.status }}
              </v-chip>
            </template>
            
            <template #item.actions="{ item }">
              <v-btn
                icon="mdi-eye"
                size="small"
                variant="text"
                @click="$emit('view', { type: 'client', item })"
              />
              <v-btn
                icon="mdi-pencil"
                size="small"
                variant="text"
                @click="$emit('edit', { type: 'client', item })"
              />
            </template>
          </v-data-table>
        </v-window-item>
      </v-window>
    </v-card>
  </div>
</template>

<script setup>
/**
 * User Management Section Component
 * 
 * Provides tabbed interface for managing caregivers,
 * housekeepers, and clients from the admin dashboard.
 */
import { ref, computed } from 'vue';

const props = defineProps({
  caregivers: {
    type: Array,
    default: () => [],
  },
  housekeepers: {
    type: Array,
    default: () => [],
  },
  clients: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  itemsPerPage: {
    type: Number,
    default: 10,
  },
});

defineEmits(['view', 'edit', 'approve', 'suspend', 'add-user']);

const activeTab = ref('caregivers');
const search = ref('');
const statusFilter = ref(null);

const statusOptions = [
  { title: 'All', value: null },
  { title: 'Active', value: 'Active' },
  { title: 'Pending', value: 'pending' },
  { title: 'Suspended', value: 'suspended' },
];

const caregiverHeaders = [
  { title: 'User', key: 'user', sortable: false },
  { title: 'Status', key: 'status', width: '120px' },
  { title: 'Availability', key: 'availability', width: '120px' },
  { title: 'Rate', key: 'hourly_rate', width: '100px' },
  { title: 'Rating', key: 'rating', width: '100px' },
  { title: 'Actions', key: 'actions', sortable: false, width: '150px' },
];

const housekeeperHeaders = [
  { title: 'User', key: 'user', sortable: false },
  { title: 'Status', key: 'status', width: '120px' },
  { title: 'Availability', key: 'availability_status', width: '120px' },
  { title: 'Rate', key: 'hourly_rate', width: '100px' },
  { title: 'Actions', key: 'actions', sortable: false, width: '150px' },
];

const clientHeaders = [
  { title: 'Name', key: 'name', sortable: false },
  { title: 'Status', key: 'status', width: '120px' },
  { title: 'Phone', key: 'phone', width: '140px' },
  { title: 'City', key: 'city', width: '120px' },
  { title: 'Actions', key: 'actions', sortable: false, width: '120px' },
];

const pendingCaregivers = computed(() => 
  props.caregivers.filter(c => c.user?.status === 'pending').length
);

const pendingHousekeepers = computed(() => 
  props.housekeepers.filter(h => h.user?.status === 'pending').length
);

const filteredCaregivers = computed(() => {
  if (!statusFilter.value) return props.caregivers;
  return props.caregivers.filter(c => c.user?.status === statusFilter.value);
});

const filteredHousekeepers = computed(() => {
  if (!statusFilter.value) return props.housekeepers;
  return props.housekeepers.filter(h => h.user?.status === statusFilter.value);
});

const filteredClients = computed(() => {
  if (!statusFilter.value) return props.clients;
  return props.clients.filter(c => c.status === statusFilter.value);
});

const getStatusColor = (status) => {
  const colors = {
    'Active': 'success',
    'approved': 'success',
    'pending': 'warning',
    'suspended': 'error',
    'rejected': 'error',
  };
  return colors[status] || 'grey';
};

const getAvailabilityColor = (status) => {
  const colors = {
    'available': 'success',
    'busy': 'warning',
    'unavailable': 'error',
  };
  return colors[status] || 'grey';
};

let searchTimeout = null;
const debouncedSearch = (value) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    search.value = value;
  }, 300);
};
</script>

<style scoped>
.user-management :deep(.v-data-table) {
  background: transparent;
}
</style>
