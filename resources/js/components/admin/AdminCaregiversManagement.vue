<template>
  <div class="admin-caregivers-management">
    <!-- Filters & Actions -->
    <div class="mb-6">
      <v-row class="align-center">
        <v-col cols="12" md="3">
          <v-text-field 
            v-model="searchQuery" 
            placeholder="Search caregivers..." 
            prepend-inner-icon="mdi-magnify" 
            variant="outlined" 
            density="compact" 
            hide-details
            @update:model-value="$emit('search', searchQuery)"
          />
        </v-col>
        <v-col cols="12" md="2">
          <v-select 
            v-model="locationFilter" 
            :items="locationOptions" 
            label="All Locations" 
            variant="outlined" 
            density="compact" 
            hide-details
            @update:model-value="$emit('filter-location', locationFilter)"
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
            @update:model-value="$emit('filter-status', statusFilter)"
          />
        </v-col>
        <v-col cols="12" md="3">
          <v-btn color="error" prepend-icon="mdi-plus" @click="$emit('add')">
            Add Caregiver
          </v-btn>
        </v-col>
      </v-row>
    </div>

    <v-card elevation="0">
      <v-card-title class="card-header pa-6 pa-md-8 d-flex justify-space-between align-center">
        <span class="section-title error--text">Caregivers</span>
        <v-chip color="primary" size="small">
          {{ caregivers.length }} Total
        </v-chip>
      </v-card-title>

      <!-- Desktop Table View -->
      <v-data-table 
        v-if="!isMobile" 
        :headers="headers" 
        :items="caregivers" 
        :items-per-page="10" 
        class="elevation-0" 
        density="compact"
      >
        <template v-slot:item.status="{ item }">
          <v-chip 
            :color="getStatusColor(item.status)" 
            size="small" 
            class="font-weight-bold" 
            :prepend-icon="getStatusIcon(item.status)"
          >
            {{ item.status }}
          </v-chip>
        </template>
        <template v-slot:item.rating="{ item }">
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
            ></v-rating>
            <span class="ml-1 text-caption">{{ parseFloat(item.rating || 0).toFixed(1) }}</span>
          </div>
        </template>
        <template v-slot:item.actions="{ item }">
          <div class="action-buttons">
            <v-btn 
              class="action-btn-view" 
              icon="mdi-eye" 
              size="small" 
              @click="$emit('view', item)"
            ></v-btn>
          </div>
        </template>
      </v-data-table>

      <!-- Mobile Card View -->
      <div v-else class="mobile-cards-container pa-3">
        <div v-if="caregivers.length === 0" class="text-center py-8 text-grey">
          No caregivers found
        </div>
        <v-card 
          v-for="item in caregivers" 
          :key="item.id" 
          class="mobile-data-card mb-3" 
          elevation="2" 
          rounded="lg"
        >
          <v-card-text class="pa-0">
            <div class="mobile-card-header d-flex align-center justify-space-between pa-3" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
              <span class="text-white font-weight-bold text-body-1">{{ item.name }}</span>
              <v-chip :color="getStatusColor(item.status)" size="small" class="font-weight-bold">
                {{ item.status }}
              </v-chip>
            </div>
            <div class="mobile-card-body pa-3">
              <div class="mobile-card-row d-flex justify-space-between align-center py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Rating:</span>
                <div class="d-flex align-center">
                  <v-rating 
                    :model-value="parseFloat(item.rating || 0)" 
                    :length="5" 
                    :size="14" 
                    color="amber" 
                    active-color="amber" 
                    half-increments 
                    readonly 
                    density="compact"
                  ></v-rating>
                  <span class="ml-1 text-caption">{{ parseFloat(item.rating || 0).toFixed(1) }}</span>
                </div>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Email:</span>
                <span class="mobile-card-value text-caption" style="word-break: break-all;">{{ item.email }}</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2">
                <span class="mobile-card-label text-grey-darken-1">Joined:</span>
                <span class="mobile-card-value">{{ item.joined }}</span>
              </div>
            </div>
            <div class="mobile-card-actions d-flex justify-center pa-3" style="background: #f9fafb; border-top: 1px solid #e5e7eb;">
              <v-btn 
                color="primary" 
                variant="tonal" 
                size="small" 
                prepend-icon="mdi-eye" 
                @click="$emit('view', item)"
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
import { ref, computed } from 'vue';
import { useDisplay } from 'vuetify';

const props = defineProps({
  caregivers: {
    type: Array,
    default: () => []
  },
  locationOptions: {
    type: Array,
    default: () => ['All', 'Manhattan', 'Brooklyn', 'Queens', 'Bronx', 'Staten Island']
  }
});

const emit = defineEmits(['add', 'view', 'edit', 'search', 'filter-location', 'filter-status']);

const { smAndDown } = useDisplay();
const isMobile = computed(() => smAndDown.value);

const searchQuery = ref('');
const locationFilter = ref('All');
const statusFilter = ref('All');
const statusOptions = ['All', 'Active', 'Assigned', 'Inactive'];

const headers = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Status', key: 'status' },
  { title: 'Rating', key: 'rating' },
  { title: 'Clients', key: 'clients' },
  { title: 'Joined', key: 'joined' },
  { title: 'Actions', key: 'actions', sortable: false }
];

const getStatusColor = (status) => {
  const colors = {
    'Active': 'success',
    'Assigned': 'info',
    'Inactive': 'grey',
    'Pending': 'warning'
  };
  return colors[status] || 'grey';
};

const getStatusIcon = (status) => {
  const icons = {
    'Active': 'mdi-check-circle',
    'Assigned': 'mdi-account-arrow-right',
    'Inactive': 'mdi-close-circle',
    'Pending': 'mdi-clock-outline'
  };
  return icons[status] || 'mdi-help-circle';
};
</script>

<style scoped>
.admin-caregivers-management {
  width: 100%;
}

.card-header {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-bottom: 1px solid #e2e8f0;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.action-btn-view {
  color: #2563eb !important;
}

.mobile-data-card {
  border-radius: 12px;
  overflow: hidden;
}

.mobile-card-label {
  font-size: 0.875rem;
  font-weight: 500;
}

.mobile-card-value {
  font-size: 0.875rem;
  color: #1e293b;
}

@media (max-width: 768px) {
  .card-header {
    padding: 1rem !important;
  }
}
</style>
