<template>
  <div class="admin-announcements">
    <v-row>
      <!-- Send Announcement Form -->
      <v-col cols="12" md="8">
        <v-card elevation="0" class="mb-6">
          <v-card-title class="card-header pa-8 d-flex justify-space-between align-center">
            <span class="section-title error--text">Send Announcement</span>
            <v-btn 
              color="error" 
              prepend-icon="mdi-plus" 
              @click="showNewAnnouncementDialog"
              aria-label="Create new announcement"
            >
              New Announcement
            </v-btn>
          </v-card-title>
          <v-card-text class="pa-8">
            <v-text-field 
              v-model="formData.title" 
              label="Announcement Title" 
              variant="outlined" 
              class="mb-4"
              :error-messages="errors.title"
              aria-required="true"
            />
            <v-textarea 
              v-model="formData.message" 
              label="Message" 
              variant="outlined" 
              rows="4" 
              class="mb-4"
              :error-messages="errors.message"
              aria-required="true"
            />
            <v-row>
              <v-col cols="12" md="4">
                <v-select 
                  v-model="formData.type" 
                  :items="typeOptions" 
                  label="Type" 
                  variant="outlined"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-select 
                  v-model="formData.recipients" 
                  :items="recipientOptions" 
                  label="Recipients" 
                  variant="outlined"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-select 
                  v-model="formData.priority" 
                  :items="priorityOptions" 
                  label="Priority" 
                  variant="outlined"
                />
              </v-col>
            </v-row>
            <v-btn 
              color="error" 
              size="large" 
              prepend-icon="mdi-send" 
              @click="handleSendAnnouncement" 
              class="mt-4"
              :loading="sending"
              :disabled="!isFormValid"
            >
              Send Announcement
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
      
      <!-- Recent Announcements Sidebar -->
      <v-col cols="12" md="4">
        <v-card elevation="0">
          <v-card-title class="card-header pa-8">
            <span class="section-title error--text">Recent Announcements</span>
          </v-card-title>
          <v-card-text class="pa-8">
            <!-- Empty State -->
            <div v-if="recentAnnouncements.length === 0" class="text-center py-4 text-grey">
              <v-icon size="48" color="grey-lighten-1">mdi-bullhorn-outline</v-icon>
              <div class="mt-2">No announcements yet</div>
            </div>
            
            <!-- Announcements List -->
            <div 
              v-for="(announcement, index) in recentAnnouncements" 
              :key="announcement.id || index" 
              class="announcement-item mb-3"
            >
              <div class="d-flex justify-space-between align-center mb-1">
                <span class="announcement-title font-weight-medium">{{ announcement.title }}</span>
                <v-chip 
                  :color="getTypeColor(announcement.type)" 
                  size="x-small"
                >
                  {{ announcement.type }}
                </v-chip>
              </div>
              <div class="announcement-message text-body-2 text-grey-darken-1 mb-1">
                {{ announcement.message }}
              </div>
              <div class="text-caption text-grey">
                {{ formatDate(announcement.sent_at) }} to {{ announcement.recipients }}
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    
    <!-- New Announcement Dialog -->
    <v-dialog v-model="dialogVisible" max-width="600" persistent>
      <v-card>
        <v-card-title class="pa-4 bg-error text-white">
          <v-icon class="mr-2">mdi-bullhorn</v-icon>
          New Announcement
        </v-card-title>
        <v-card-text class="pa-6">
          <v-text-field 
            v-model="dialogFormData.title" 
            label="Title" 
            variant="outlined" 
            class="mb-4"
          />
          <v-textarea 
            v-model="dialogFormData.message" 
            label="Message" 
            variant="outlined" 
            rows="4"
          />
        </v-card-text>
        <v-card-actions class="pa-4">
          <v-spacer />
          <v-btn variant="text" @click="dialogVisible = false">Cancel</v-btn>
          <v-btn color="error" variant="flat" @click="sendFromDialog">Send</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';

/**
 * AdminAnnouncements - Announcement management section
 * Allows sending announcements to different user groups
 */

const props = defineProps({
  /** List of recent announcements */
  recentAnnouncements: {
    type: Array,
    default: () => []
  },
  /** Initial announcement data */
  announcementData: {
    type: Object,
    default: () => ({
      title: '',
      message: '',
      type: 'info',
      recipients: 'all',
      priority: 'normal'
    })
  }
});

const emit = defineEmits(['send-announcement', 'open-dialog']);

// Local state
const dialogVisible = ref(false);
const sending = ref(false);
const errors = reactive({
  title: '',
  message: ''
});

// Form data synced with props
const formData = reactive({
  title: props.announcementData.title || '',
  message: props.announcementData.message || '',
  type: props.announcementData.type || 'info',
  recipients: props.announcementData.recipients || 'all',
  priority: props.announcementData.priority || 'normal'
});

const dialogFormData = reactive({
  title: '',
  message: ''
});

// Options for dropdowns
const typeOptions = [
  { title: 'Information', value: 'info' },
  { title: 'Warning', value: 'warning' },
  { title: 'Success', value: 'success' },
  { title: 'Error', value: 'error' }
];

const recipientOptions = [
  { title: 'All Users', value: 'all' },
  { title: 'Caregivers Only', value: 'caregivers' },
  { title: 'Clients Only', value: 'clients' },
  { title: 'Housekeepers Only', value: 'housekeepers' }
];

const priorityOptions = [
  { title: 'Low', value: 'low' },
  { title: 'Normal', value: 'normal' },
  { title: 'High', value: 'high' },
  { title: 'Urgent', value: 'urgent' }
];

/**
 * Check if form is valid
 */
const isFormValid = computed(() => {
  return formData.title.trim() && formData.message.trim();
});

/**
 * Get color based on announcement type
 */
const getTypeColor = (type) => {
  const colors = {
    info: 'info',
    warning: 'warning',
    success: 'success',
    error: 'error'
  };
  return colors[type] || 'grey';
};

/**
 * Format date for display
 */
const formatDate = (date) => {
  if (!date) return '';
  try {
    return new Date(date).toLocaleString();
  } catch {
    return date;
  }
};

/**
 * Validate form before sending
 */
const validateForm = () => {
  errors.title = '';
  errors.message = '';
  let valid = true;
  
  if (!formData.title.trim()) {
    errors.title = 'Title is required';
    valid = false;
  }
  
  if (!formData.message.trim()) {
    errors.message = 'Message is required';
    valid = false;
  }
  
  return valid;
};

/**
 * Handle sending announcement
 */
const handleSendAnnouncement = () => {
  if (!validateForm()) return;
  
  sending.value = true;
  emit('send-announcement', { ...formData });
  
  // Reset form after sending
  setTimeout(() => {
    formData.title = '';
    formData.message = '';
    formData.type = 'info';
    formData.recipients = 'all';
    formData.priority = 'normal';
    sending.value = false;
  }, 500);
};

/**
 * Show new announcement dialog
 */
const showNewAnnouncementDialog = () => {
  dialogFormData.title = '';
  dialogFormData.message = '';
  dialogVisible.value = true;
  emit('open-dialog');
};

/**
 * Send from dialog
 */
const sendFromDialog = () => {
  if (!dialogFormData.title.trim() || !dialogFormData.message.trim()) return;
  
  emit('send-announcement', {
    title: dialogFormData.title,
    message: dialogFormData.message,
    type: 'info',
    recipients: 'all',
    priority: 'normal'
  });
  
  dialogVisible.value = false;
};
</script>

<style scoped>
.announcement-item {
  padding: 12px;
  border-radius: 8px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
}

.announcement-title {
  font-size: 14px;
  color: #374151;
}

.announcement-message {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
  * {
    transition: none !important;
  }
}
</style>
