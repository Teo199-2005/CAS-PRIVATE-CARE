<template>
  <div class="admin-pending-applications">
    <v-card elevation="0">
      <v-card-title class="card-header pa-6 pa-md-8 d-flex justify-space-between align-center">
        <span class="section-title error--text">Contractors Application</span>
        <v-chip color="warning" size="small">
          {{ applications.length }} Pending
        </v-chip>
      </v-card-title>

      <!-- Desktop Table View -->
      <v-data-table 
        v-if="!isMobile"
        :headers="headers" 
        :items="applications" 
        :items-per-page="10" 
        class="elevation-0 table-no-checkbox"
      >
        <template v-slot:item.type="{ item }">
          <v-chip 
            :color="getTypeColor(item.type)" 
            size="small" 
            class="font-weight-bold" 
            :prepend-icon="getTypeIcon(item.type)"
          >
            {{ item.type }}
          </v-chip>
        </template>
        <template v-slot:item.applied="{ item }">
          <span v-if="item.applied_at">
            {{ formatDate(item.applied_at) }}
          </span>
          <span v-else>N/A</span>
        </template>
        <template v-slot:item.documents="{ item }">
          <v-chip 
            :color="isApproved(item) ? 'success' : 'warning'" 
            size="small" 
            class="font-weight-bold" 
            :prepend-icon="isApproved(item) ? 'mdi-check-circle' : 'mdi-clock-outline'"
          >
            {{ isApproved(item) ? 'Approved' : 'Pending' }}
          </v-chip>
        </template>
        <template v-slot:item.actions="{ item }">
          <div class="action-buttons">
            <v-btn 
              class="action-btn-view" 
              icon="mdi-eye" 
              size="small" 
              @click="$emit('view', item)"
            ></v-btn>
            <v-btn 
              class="action-btn-approve" 
              icon="mdi-check" 
              size="small" 
              @click="$emit('approve', item)"
            ></v-btn>
            <v-btn 
              class="action-btn-reject" 
              icon="mdi-close" 
              size="small" 
              @click="$emit('reject', item)"
            ></v-btn>
          </div>
        </template>
      </v-data-table>

      <!-- Mobile Card View -->
      <div v-else class="mobile-cards-container pa-3">
        <div v-if="applications.length === 0" class="text-center py-8 text-grey">
          No pending applications
        </div>
        <v-card 
          v-for="item in applications" 
          :key="item.id" 
          class="mobile-data-card mb-3" 
          elevation="2" 
          rounded="lg"
        >
          <v-card-text class="pa-0">
            <div class="mobile-card-header d-flex align-center justify-space-between pa-3" :style="getHeaderStyle(item.type)">
              <span class="text-white font-weight-bold text-body-1">{{ item.name }}</span>
              <v-chip :color="getTypeColor(item.type)" size="small" class="font-weight-bold">
                {{ item.type }}
              </v-chip>
            </div>
            <div class="mobile-card-body pa-3">
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Email:</span>
                <span class="mobile-card-value text-caption" style="word-break: break-all;">{{ item.email }}</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between py-2" style="border-bottom: 1px solid #f3f4f6;">
                <span class="mobile-card-label text-grey-darken-1">Applied:</span>
                <span class="mobile-card-value">{{ formatDate(item.applied_at) }}</span>
              </div>
              <div class="mobile-card-row d-flex justify-space-between align-center py-2">
                <span class="mobile-card-label text-grey-darken-1">Status:</span>
                <v-chip 
                  :color="isApproved(item) ? 'success' : 'warning'" 
                  size="x-small"
                >
                  {{ isApproved(item) ? 'Approved' : 'Pending' }}
                </v-chip>
              </div>
            </div>
            <div class="mobile-card-actions d-flex justify-center gap-2 pa-3" style="background: #f9fafb; border-top: 1px solid #e5e7eb;">
              <v-btn 
                color="info" 
                variant="tonal" 
                size="small" 
                icon="mdi-eye"
                @click="$emit('view', item)"
              ></v-btn>
              <v-btn 
                color="success" 
                variant="tonal" 
                size="small" 
                icon="mdi-check"
                @click="$emit('approve', item)"
              ></v-btn>
              <v-btn 
                color="error" 
                variant="tonal" 
                size="small" 
                icon="mdi-close"
                @click="$emit('reject', item)"
              ></v-btn>
            </div>
          </v-card-text>
        </v-card>
      </div>
    </v-card>

    <!-- View Application Details Dialog -->
    <v-dialog v-model="showDetailDialog" max-width="800">
      <v-card v-if="selectedApplication">
        <v-card-title class="pa-6" style="background: #dc2626; color: white;">
          <div class="d-flex align-center justify-space-between w-100">
            <span class="section-title" style="color: white;">Application Details</span>
            <v-btn icon="mdi-close" variant="text" style="color: white;" @click="showDetailDialog = false"></v-btn>
          </div>
        </v-card-title>
        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12" class="text-center mb-4">
              <v-avatar size="120" :color="getTypeColor(selectedApplication.type)" class="mb-3">
                <span class="text-h3 font-weight-bold text-white">
                  {{ getInitials(selectedApplication.name) }}
                </span>
              </v-avatar>
              <h2>{{ selectedApplication.name }}</h2>
              <v-chip 
                :color="getTypeColor(selectedApplication.type)" 
                size="large" 
                class="mt-2 font-weight-bold"
                :prepend-icon="getTypeIcon(selectedApplication.type)"
              >
                {{ selectedApplication.type }}
              </v-chip>
              <v-chip 
                :color="isApproved(selectedApplication) ? 'success' : 'warning'" 
                class="mt-2 ml-2" 
                size="large"
              >
                <v-icon size="16" class="mr-1">
                  {{ isApproved(selectedApplication) ? 'mdi-check-circle' : 'mdi-clock-outline' }}
                </v-icon>
                {{ isApproved(selectedApplication) ? 'Approved' : 'Pending' }}
              </v-chip>
            </v-col>
          </v-row>
          
          <v-divider class="mb-4"></v-divider>
          
          <v-row>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Email</div>
                <div class="detail-value">{{ selectedApplication.email }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Phone</div>
                <div class="detail-value">{{ selectedApplication.phone || 'Not provided' }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Applied Date</div>
                <div class="detail-value">{{ formatDateLong(selectedApplication.applied_at) }}</div>
              </div>
            </v-col>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <div class="detail-label">Document Status</div>
                <div class="detail-value">
                  <v-chip 
                    :color="selectedApplication.documents === 'Complete' ? 'success' : 'warning'" 
                    size="small" 
                    class="font-weight-bold"
                    :prepend-icon="selectedApplication.documents === 'Complete' ? 'mdi-check-circle' : 'mdi-alert-circle'"
                  >
                    {{ selectedApplication.documents === 'Complete' ? 'Documents Complete' : 'Documents Pending' }}
                  </v-chip>
                </div>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="pa-6 pt-0">
          <v-spacer></v-spacer>
          <v-btn color="grey" variant="text" @click="showDetailDialog = false">Close</v-btn>
          <v-btn 
            color="success" 
            variant="flat" 
            prepend-icon="mdi-check" 
            @click="$emit('approve', selectedApplication); showDetailDialog = false"
          >
            Approve
          </v-btn>
          <v-btn 
            color="error" 
            variant="flat" 
            prepend-icon="mdi-close" 
            @click="$emit('reject', selectedApplication); showDetailDialog = false"
          >
            Reject
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useDisplay } from 'vuetify';

const props = defineProps({
  applications: {
    type: Array,
    default: () => []
  },
  viewApplication: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['view', 'approve', 'reject']);

const { smAndDown } = useDisplay();
const isMobile = computed(() => smAndDown.value);

const showDetailDialog = ref(false);
const selectedApplication = ref(null);

const headers = [
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Type', key: 'type' },
  { title: 'Applied', key: 'applied' },
  { title: 'Status', key: 'documents' },
  { title: 'Actions', key: 'actions', sortable: false }
];

// Watch for external view trigger
watch(() => props.viewApplication, (newVal) => {
  if (newVal) {
    selectedApplication.value = newVal;
    showDetailDialog.value = true;
  }
});

const getTypeColor = (type) => {
  const colors = {
    'Caregiver': 'success',
    'Marketing Partner': 'info',
    'Training Center': 'warning',
    'Housekeeper': 'purple'
  };
  return colors[type] || 'grey';
};

const getTypeIcon = (type) => {
  const icons = {
    'Caregiver': 'mdi-account-heart',
    'Marketing Partner': 'mdi-bullhorn-variant',
    'Training Center': 'mdi-school',
    'Housekeeper': 'mdi-home-heart'
  };
  return icons[type] || 'mdi-account';
};

const getHeaderStyle = (type) => {
  const colors = {
    'Caregiver': 'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
    'Marketing Partner': 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)',
    'Training Center': 'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
    'Housekeeper': 'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)'
  };
  return { background: colors[type] || colors['Caregiver'] };
};

const isApproved = (item) => {
  return item.status && item.status.toLowerCase() === 'approved';
};

const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
};

const formatDateLong = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
};
</script>

<style scoped>
.admin-pending-applications {
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
  gap: 0.25rem;
}

.action-btn-view {
  color: #2563eb !important;
}

.action-btn-approve {
  color: #16a34a !important;
}

.action-btn-reject {
  color: #dc2626 !important;
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

.detail-section {
  margin-bottom: 1rem;
}

.detail-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.25rem;
}

.detail-value {
  font-size: 1rem;
  color: #1e293b;
}

@media (max-width: 768px) {
  .card-header {
    padding: 1rem !important;
  }
}
</style>
