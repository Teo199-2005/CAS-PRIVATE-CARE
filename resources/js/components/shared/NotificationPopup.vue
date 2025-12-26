<template>
  <v-menu v-model="menu" :close-on-content-click="false" location="bottom end" offset="8">
    <template v-slot:activator="{ props }">
      <v-btn
        icon
        variant="text"
        v-bind="props"
        class="notification-btn"
        :class="{ 'has-unread': unreadCount > 0 }"
      >
        <v-badge
          :content="unreadCount"
          :model-value="unreadCount > 0"
          color="error"
          overlap
        >
          <v-icon>mdi-bell</v-icon>
        </v-badge>
      </v-btn>
    </template>

    <v-card class="notification-popup" width="380" max-height="500">
      <v-card-title class="notification-header pa-4">
        <div class="d-flex justify-space-between align-center">
          <span class="notification-title">Notifications</span>
          <div class="d-flex align-center gap-2">
            <v-chip size="small" :color="unreadCount > 0 ? 'error' : 'success'">
              {{ unreadCount }} unread
            </v-chip>
            <v-btn
              icon="mdi-check-all"
              size="small"
              variant="text"
              @click="markAllAsRead"
              :disabled="unreadCount === 0"
            />
          </div>
        </div>
      </v-card-title>

      <v-divider />

      <div class="notification-list" v-if="!loading && notifications.length > 0">
        <div
          v-for="notification in displayedNotifications"
          :key="notification.id"
          class="notification-item"
          :class="{ 'unread': !notification.read }"
          @click="markAsRead(notification)"
        >
          <div class="d-flex align-start gap-3 pa-3">
            <v-avatar :color="notification.color" size="32">
              <v-icon color="white" size="16">{{ notification.icon }}</v-icon>
            </v-avatar>
            <div class="flex-grow-1">
              <div class="notification-item-title">{{ notification.title }}</div>
              <div class="notification-item-message">{{ notification.message }}</div>
              <div class="notification-item-time">{{ notification.time }}</div>
            </div>
            <v-icon v-if="!notification.read" color="primary" size="8">mdi-circle</v-icon>
          </div>
        </div>
      </div>

      <div v-else-if="loading" class="text-center pa-8">
        <v-progress-circular indeterminate size="24" />
        <div class="mt-2 text-caption">Loading...</div>
      </div>

      <div v-else class="text-center pa-8">
        <v-icon size="48" color="grey-lighten-2">mdi-bell-off</v-icon>
        <div class="mt-2 text-caption text-grey">No notifications</div>
      </div>

      <v-divider v-if="notifications.length > 3" />

      <v-card-actions v-if="notifications.length > 3" class="pa-3">
        <v-btn
          block
          variant="text"
          size="small"
          @click="$emit('viewAll')"
        >
          View All Notifications
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-menu>
</template>

<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from 'vue';

const props = defineProps({
  userId: { type: Number, required: true }
});

const emit = defineEmits(['viewAll']);

const menu = ref(false);
const notifications = ref([]);
const loading = ref(false);

const unreadCount = computed(() => 
  notifications.value.filter(n => !n.read).length
);

const displayedNotifications = computed(() => 
  notifications.value.slice(0, 3)
);

const loadNotifications = async () => {
  loading.value = true;
  try {
    const response = await fetch(`/api/notifications?user_id=${props.userId}`);
    const data = await response.json();
    notifications.value = data.notifications || [];
  } catch (error) {
    console.error('Failed to load notifications:', error);
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
  } catch (error) {
    console.error('Failed to mark notification as read:', error);
  }
};

const markAllAsRead = async () => {
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
  } catch (error) {
    console.error('Failed to mark all notifications as read:', error);
  }
};

// Auto-refresh notifications every 30 seconds
let refreshInterval;

onMounted(() => {
  loadNotifications();
  refreshInterval = setInterval(loadNotifications, 30000);
});

watch(() => props.userId, () => {
  loadNotifications();
});

// Cleanup interval on unmount
onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});
</script>

<style scoped>
.notification-btn {
  transition: all 0.2s ease;
}

.notification-btn.has-unread {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.notification-popup {
  border-radius: 12px !important;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15) !important;
}

.notification-header {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-bottom: 1px solid #e2e8f0;
}

.notification-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
}

.notification-list {
  max-height: 300px;
  overflow-y: auto;
}

.notification-list::-webkit-scrollbar {
  width: 4px;
}

.notification-list::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.notification-list::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 2px;
}

.notification-item {
  cursor: pointer;
  transition: background-color 0.2s ease;
  border-bottom: 1px solid #f1f5f9;
}

.notification-item:hover {
  background-color: #f8fafc;
}

.notification-item.unread {
  background-color: #f0f9ff;
  border-left: 3px solid #3b82f6;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-item-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1e293b;
  line-height: 1.2;
  margin-bottom: 2px;
}

.notification-item-message {
  font-size: 0.8rem;
  color: #64748b;
  line-height: 1.3;
  margin-bottom: 4px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.notification-item-time {
  font-size: 0.7rem;
  color: #94a3b8;
  font-weight: 500;
}
</style>