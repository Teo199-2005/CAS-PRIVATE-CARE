<template>
  <div>
    <!-- Notification Filters -->
    <div class="mb-6">
      <v-row class="align-center">
        <v-col cols="12" md="3">
          <v-select v-model="notificationFilter" :items="['All', 'Unread', 'Today', 'This Week']" variant="outlined" density="compact" hide-details />
        </v-col>
        <v-col cols="12" md="3">
          <v-select v-model="notificationType" :items="['All', 'Appointments', 'Payments', 'System', 'Clients', 'Caregivers']" variant="outlined" density="compact" hide-details />
        </v-col>
        <v-col cols="12" md="6">
          <v-text-field v-model="notificationSearch" placeholder="Search notifications..." prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details />
        </v-col>
      </v-row>
    </div>

    <!-- Quick Actions -->
    <v-card elevation="0" class="mb-6 notification-center-card">
      <v-card-title class="card-header pa-6 d-flex justify-space-between align-center">
        <span class="section-title" :class="primaryColor">Notification Center</span>
        <div class="notification-center-actions d-flex gap-2">
          <v-btn :color="primaryColorName" variant="tonal" density="compact" size="small" prepend-icon="mdi-check-all" class="notification-action-btn" @click="markAllRead" :loading="loading">Mark all read</v-btn>
          <v-btn color="error" variant="tonal" density="compact" size="small" prepend-icon="mdi-delete-sweep" class="notification-action-btn" @click="clearAll" :loading="loading">Clear all</v-btn>
          <v-btn color="primary" variant="tonal" density="compact" size="small" prepend-icon="mdi-refresh" class="notification-action-btn" @click="loadNotifications" :loading="loading">Refresh</v-btn>
        </div>
      </v-card-title>
    </v-card>

    <!-- Notifications List -->
    <v-row>
      <v-col cols="12">
        <v-card elevation="0">
          <v-card-title class="card-header pa-6">
            <span class="section-title" :class="primaryColor">Recent Notifications ({{ unreadCount }} unread)</span>
          </v-card-title>
          <v-list class="pa-0" v-if="!loading && notifications.length > 0">
            <template v-for="(notification, i) in filteredNotifications" :key="notification.id">
              <v-list-item class="notification-item" :class="{ 'unread': !notification.read }" @click="markAsRead(notification)">
                <template v-slot:prepend>
                  <v-avatar :color="notification.color" size="48" class="mr-4">
                    <v-icon color="white" size="24">{{ notification.icon }}</v-icon>
                  </v-avatar>
                </template>
                <div class="notification-content">
                  <div class="d-flex justify-space-between align-start mb-1">
                    <v-list-item-title class="notification-title">{{ notification.title }}</v-list-item-title>
                    <div class="d-flex align-center gap-2">
                      <v-chip v-if="notification.priority === 'high'" color="error" size="x-small" class="priority-chip">High Priority</v-chip>
                      <v-chip :color="notification.typeColor" size="x-small" class="type-chip">{{ notification.type }}</v-chip>
                      <span class="notification-time">{{ notification.time }}</span>
                    </div>
                  </div>
                  <v-list-item-subtitle class="notification-message">{{ notification.message }}</v-list-item-subtitle>
                </div>
                <template v-slot:append>
                  <div class="d-flex flex-column align-center gap-2">
                    <v-icon v-if="!notification.read" :color="primaryColorName" size="small">mdi-circle</v-icon>
                    <v-menu>
                      <template v-slot:activator="{ props }">
                        <v-btn icon="mdi-dots-vertical" size="x-small" variant="text" v-bind="props" @click.stop />
                      </template>
                      <v-list>
                        <v-list-item @click="toggleRead(notification)">
                          <v-list-item-title>{{ notification.read ? 'Mark as Unread' : 'Mark as Read' }}</v-list-item-title>
                        </v-list-item>
                        <v-list-item @click="deleteNotification(notification)">
                          <v-list-item-title>Delete</v-list-item-title>
                        </v-list-item>
                      </v-list>
                    </v-menu>
                  </div>
                </template>
              </v-list-item>
              <v-divider v-if="i < filteredNotifications.length - 1" />
            </template>
          </v-list>
          <div v-else-if="loading" class="text-center pa-8">
            <v-progress-circular indeterminate color="primary"></v-progress-circular>
            <p class="mt-4">Loading notifications...</p>
          </div>
          <div v-else class="text-center pa-8">
            <v-icon size="64" color="grey-lighten-2">mdi-bell-off</v-icon>
            <p class="mt-4 text-grey">No notifications found</p>
          </div>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  userType: { type: String, default: 'client' },
  userId: { type: Number, default: 1 }
});

const emit = defineEmits(['openSettings', 'actionClicked']);

const notifications = ref([]);
const unreadCount = ref(0);
const loading = ref(false);
const notificationFilter = ref('All');
const notificationType = ref('All');
const notificationSearch = ref('');

const primaryColorName = computed(() => props.userType === 'caregiver' ? 'success' : 'primary');
const primaryColor = computed(() => props.userType === 'caregiver' ? 'success--text' : 'primary--text');

const filteredNotifications = computed(() => {
  return notifications.value.filter(n => {
    const timeStr = n.time || '';
    const titleStr = n.title || '';
    const messageStr = n.message || '';
    
    const matchesFilter = notificationFilter.value === 'All' || 
      (notificationFilter.value === 'Unread' && !n.read) ||
      (notificationFilter.value === 'Today' && (timeStr.includes('hour') || timeStr.includes('minute'))) ||
      (notificationFilter.value === 'This Week' && !timeStr.includes('week'));
    const matchesType = notificationType.value === 'All' || n.type === notificationType.value;
    const matchesSearch = !notificationSearch.value || 
      titleStr.toLowerCase().includes(notificationSearch.value.toLowerCase()) ||
      messageStr.toLowerCase().includes(notificationSearch.value.toLowerCase());
    return matchesFilter && matchesType && matchesSearch;
  });
});

const loadNotifications = async () => {
  loading.value = true;
  try {
    const response = await fetch(`/api/notifications?user_id=${props.userId}`);
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const result = await response.json();
    
    // Handle wrapped response format: { success: true, data: { notifications: [], unread_count: 0 } }
    const data = result.data || result;
    notifications.value = data.notifications || [];
    unreadCount.value = data.unread_count || 0;
  } catch (error) {
    console.error('Failed to load notifications:', error);
    notifications.value = [];
    unreadCount.value = 0;
  } finally {
    loading.value = false;
  }
};

const markAsRead = async (notification) => {
  if (notification.read) return;
  
  try {
    await fetch(`/api/notifications/${notification.id}/read`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    notification.read = true;
    unreadCount.value = Math.max(0, unreadCount.value - 1);
  } catch (error) {
    console.error('Failed to mark notification as read:', error);
  }
};

const toggleRead = async (notification) => {
  try {
    if (!notification.read) {
      await markAsRead(notification);
    } else {
      // For marking as unread, we'd need a separate endpoint
      notification.read = false;
      unreadCount.value += 1;
    }
  } catch (error) {
    console.error('Failed to toggle notification read status:', error);
  }
};

const markAllRead = async () => {
  loading.value = true;
  try {
    await fetch('/api/notifications/mark-all-read', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ user_id: props.userId })
    });
    notifications.value.forEach(n => n.read = true);
    unreadCount.value = 0;
  } catch (error) {
    console.error('Failed to mark all notifications as read:', error);
  } finally {
    loading.value = false;
  }
};

const clearAll = async () => {
  if (!confirm('Are you sure you want to delete all notifications?')) return;
  
  loading.value = true;
  try {
    await fetch('/api/notifications', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ user_id: props.userId })
    });
    notifications.value = [];
    unreadCount.value = 0;
  } catch (error) {
    console.error('Failed to clear all notifications:', error);
  } finally {
    loading.value = false;
  }
};

const deleteNotification = async (notification) => {
  if (!confirm('Are you sure you want to delete this notification?')) return;
  
  try {
    await fetch(`/api/notifications/${notification.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    const index = notifications.value.findIndex(n => n.id === notification.id);
    if (index > -1) {
      if (!notification.read) {
        unreadCount.value = Math.max(0, unreadCount.value - 1);
      }
      notifications.value.splice(index, 1);
    }
  } catch (error) {
    console.error('Failed to delete notification:', error);
  }
};

// Expose loadNotifications method to parent components
defineExpose({
  loadNotifications
});

onMounted(() => {
  loadNotifications();
});
</script>

<style scoped>
.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.card-header {
  background: #fafafa;
  border-bottom: 1px solid #f0f0f0;
}

:deep(.v-card) {
  border: 1px solid #c5c5c5ff !important;
  border-radius: 12px !important;
}

.notification-item {
  padding: 20px !important;
  border-radius: 12px;
  margin: 8px 16px;
  transition: all 0.2s ease;
}

.notification-item:hover {
  background: #f8fafc !important;
}

.notification-item.unread {
  background: #f0fdf4 !important;
}

.notification-content {
  flex: 1;
}

.notification-title {
  font-weight: 600 !important;
  font-size: 1rem !important;
  color: #1f2937 !important;
}

.notification-message {
  font-size: 0.875rem !important;
  color: #6b7280 !important;
  line-height: 1.5 !important;
}

.notification-time {
  font-size: 0.75rem;
  color: #9ca3af;
  font-weight: 500;
}

.notification-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.priority-chip {
  background-color: #ef4444 !important;
  color: white !important;
}

.type-chip {
  color: white !important;
}

.type-chip.orange {
  background-color: #f97316 !important;
}

.type-chip.green {
  background-color: #22c55e !important;
}

.type-chip.blue {
  background-color: #3b82f6 !important;
}

.type-chip.purple {
  background-color: #8b5cf6 !important;
}

.type-chip.grey {
  background-color: #6b7280 !important;
}

:deep(.v-chip) {
  color: white !important;
}

:deep(.v-chip.bg-success) {
  background-color: #22c55e !important;
  color: white !important;
}

:deep(.v-chip.bg-primary) {
  background-color: #3b82f6 !important;
  color: white !important;
}

:deep(.v-chip.bg-error) {
  background-color: #ef4444 !important;
  color: white !important;
}

:deep(.v-chip.bg-warning) {
  background-color: #f59e0b !important;
  color: white !important;
}

:deep(.v-chip.bg-info) {
  background-color: #06b6d4 !important;
  color: white !important;
}

/* Mobile Responsive Styles */
@media (max-width: 960px) {
  .card-header {
    padding: 1rem !important;
  }

  .section-title {
    font-size: 1.25rem !important;
  }

  .notification-item {
    padding: 1rem !important;
    margin: 0.5rem 0.75rem !important;
    flex-direction: column !important;
    align-items: flex-start !important;
  }

  .notification-content {
    width: 100% !important;
    margin-bottom: 0.5rem;
  }

  .notification-title {
    font-size: 0.9375rem !important;
    margin-bottom: 0.25rem !important;
  }

  .notification-message {
    font-size: 0.8125rem !important;
  }

  .notification-time {
    font-size: 0.6875rem !important;
  }

  /* Stack notification header elements on mobile */
  .notification-center-card .card-header.d-flex.justify-space-between {
    flex-direction: column !important;
    gap: 0.75rem !important;
    align-items: stretch !important;
  }

  .notification-center-actions {
    flex-wrap: wrap !important;
    width: 100% !important;
    gap: 0.5rem !important;
  }

  .notification-center-actions .notification-action-btn {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    letter-spacing: 0.02em !important;
    text-transform: none !important;
    min-height: 36px !important;
    padding: 0 12px !important;
  }

  /* Compact notification item layout */
  :deep(.v-list-item) {
    padding: 0 !important;
  }

  :deep(.v-list-item__prepend) {
    margin-right: 0.75rem !important;
    margin-bottom: 0.5rem !important;
  }

  :deep(.v-avatar) {
    width: 40px !important;
    height: 40px !important;
  }

  :deep(.v-list-item__append) {
    align-self: flex-end !important;
    margin-top: 0.5rem !important;
  }

  /* Stack notification header info */
  .notification-content .d-flex.justify-space-between {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 0.5rem !important;
  }

  .notification-content .d-flex.align-center.gap-2 {
    flex-wrap: wrap !important;
    width: 100% !important;
  }

  /* Smaller chips on mobile */
  .priority-chip,
  .type-chip {
    font-size: 0.625rem !important;
    padding: 2px 6px !important;
    height: 18px !important;
  }

  /* Filter row stacking */
  .mb-6 .v-row {
    margin: 0 !important;
  }

  .mb-6 .v-col {
    padding: 0.5rem !important;
  }
}

@media (max-width: 480px) {
  .section-title {
    font-size: 1.125rem !important;
  }

  .notification-item {
    padding: 0.875rem !important;
    margin: 0.5rem !important;
  }

  .card-header {
    padding: 0.875rem !important;
  }

  .notification-center-actions .notification-action-btn {
    min-width: 0 !important;
    flex: 1 1 100% !important;
    font-size: 0.8125rem !important;
    min-height: 40px !important;
  }

  :deep(.v-avatar) {
    width: 36px !important;
    height: 36px !important;
  }

  :deep(.v-icon) {
    font-size: 18px !important;
  }

  .notification-title {
    font-size: 0.875rem !important;
  }

  .notification-message {
    font-size: 0.75rem !important;
  }
}
</style>