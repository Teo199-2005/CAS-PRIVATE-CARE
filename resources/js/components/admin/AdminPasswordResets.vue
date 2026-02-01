<template>
  <div class="admin-password-resets">
    <v-card elevation="0">
      <v-card-title class="card-header pa-8">
        <span class="section-title error--text">Password Reset Requests</span>
      </v-card-title>
      
      <!-- Desktop Table View -->
      <v-data-table 
        v-if="!isMobile" 
        :headers="headers" 
        :items="passwordResets" 
        :items-per-page="10" 
        :loading="loading"
        class="elevation-0 table-no-checkbox"
      >
        <template v-slot:item.status="{ item }">
          <v-chip 
            :color="item.status === 'Completed' ? 'success' : 'warning'" 
            size="small" 
            class="font-weight-bold" 
            :prepend-icon="getStatusIcon(item.status)"
          >
            {{ item.status }}
          </v-chip>
        </template>
        <template v-slot:item.userType="{ item }">
          <v-chip 
            :color="item.userType === 'Caregiver' ? 'success' : 'primary'" 
            size="small" 
            class="font-weight-bold" 
            :prepend-icon="item.userType === 'Caregiver' ? 'mdi-account-heart' : 'mdi-account'"
          >
            {{ item.userType }}
          </v-chip>
        </template>
        <template v-slot:item.actions="{ item }">
          <div class="action-buttons">
            <v-btn 
              v-if="item.status === 'Pending'" 
              class="action-btn-approve" 
              icon="mdi-check" 
              size="small" 
              @click="$emit('process-reset', item)"
              aria-label="Process password reset"
            />
            <v-icon v-else color="grey" size="small">mdi-check-circle</v-icon>
          </div>
        </template>
      </v-data-table>
      
      <!-- Mobile Card View -->
      <div v-else class="mobile-cards-container pa-3">
        <div v-if="passwordResets.length === 0" class="text-center py-8 text-grey">
          No password reset requests found
        </div>
        <v-card 
          v-for="item in paginatedResets" 
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
              <span class="text-white font-weight-bold text-body-1">{{ item.email || item.name }}</span>
              <v-chip 
                :color="item.status === 'Completed' ? 'success' : 'warning'" 
                size="small" 
                class="font-weight-bold" 
                :prepend-icon="getStatusIcon(item.status)"
              >
                {{ item.status }}
              </v-chip>
            </div>
            <div class="mobile-card-body pa-3">
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">User Type:</span>
                <v-chip 
                  :color="item.userType === 'Caregiver' ? 'success' : 'primary'" 
                  size="x-small" 
                  class="font-weight-bold" 
                  :prepend-icon="item.userType === 'Caregiver' ? 'mdi-account-heart' : 'mdi-account'"
                >
                  {{ item.userType }}
                </v-chip>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Requested:</span>
                <span class="mobile-card-value">{{ item.requested_at || item.created_at }}</span>
              </div>
            </div>
            <div class="mobile-card-actions d-flex justify-center pa-3" style="background: #f9fafb; border-top: 1px solid #e5e7eb;">
              <v-btn 
                v-if="item.status === 'Pending'" 
                color="success" 
                variant="tonal" 
                size="small" 
                prepend-icon="mdi-check" 
                @click="$emit('process-reset', item)"
              >
                Process Reset
              </v-btn>
              <v-chip v-else color="success" size="small" prepend-icon="mdi-check-circle">
                Completed
              </v-chip>
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
 * AdminPasswordResets - Password reset requests management
 * Shows pending and completed password reset requests
 */

const props = defineProps({
  /** List of password reset requests */
  passwordResets: {
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

defineEmits(['process-reset']);

// Local state
const currentPage = ref(1);
const itemsPerPage = 10;

const headers = ref([
  { title: 'Email', key: 'email' },
  { title: 'Name', key: 'name' },
  { title: 'User Type', key: 'userType' },
  { title: 'Requested', key: 'requested_at' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false }
]);

/**
 * Paginated resets for mobile view
 */
const paginatedResets = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return props.passwordResets.slice(start, start + itemsPerPage);
});

/**
 * Total pages for pagination
 */
const totalPages = computed(() => Math.ceil(props.passwordResets.length / itemsPerPage));

/**
 * Get status icon
 */
const getStatusIcon = (status) => {
  return status === 'Completed' ? 'mdi-check-circle' : 'mdi-clock';
};
</script>

<style scoped>
.mobile-data-card {
  overflow: hidden;
  border-radius: 12px;
}

.action-btn-approve {
  color: #16a34a !important;
}

.table-no-checkbox :deep(.v-data-table__checkbox) {
  display: none;
}

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
  * {
    transition: none !important;
  }
}
</style>
