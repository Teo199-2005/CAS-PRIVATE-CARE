<template>
  <v-dialog v-model="isOpen" max-width="500" persistent>
    <v-card class="alert-card" elevation="8">
      <v-card-title :class="['alert-header', `alert-header-${type}`, 'pa-6']">
        <div class="d-flex align-center w-100">
          <v-icon :icon="icon" size="32" class="mr-3 header-icon"></v-icon>
          <span class="alert-title">{{ title }}</span>
        </div>
      </v-card-title>
      <v-card-text class="alert-content pa-8">
        <p class="alert-message">{{ message }}</p>
      </v-card-text>
      <v-divider />
      <v-card-actions class="alert-actions pa-6">
        <v-spacer />
        <v-btn 
          v-if="showCancel" 
          variant="outlined" 
          size="large"
          @click="closeModal"
          class="px-6"
          style="border: 2px solid #64748b; color: #64748b; font-weight: 600;"
        >
          Cancel
        </v-btn>
        <v-btn 
          :color="confirmColor" 
          size="large"
          variant="flat"
          @click="confirmAction"
          class="px-6"
          :class="`confirm-btn-${type}`"
        >
          <v-icon v-if="type === 'warning'" start size="20">mdi-alert</v-icon>
          <v-icon v-else-if="type === 'error'" start size="20">mdi-close-circle</v-icon>
          <v-icon v-else start size="20">mdi-check</v-icon>
          {{ confirmText }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, required: true },
  message: { type: String, required: true },
  type: { type: String, default: 'info', validator: v => ['success', 'error', 'warning', 'info'].includes(v) },
  confirmText: { type: String, default: 'OK' },
  showCancel: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue', 'confirm']);

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const icon = computed(() => {
  const icons = {
    success: 'mdi-check-circle',
    error: 'mdi-alert-circle',
    warning: 'mdi-alert',
    info: 'mdi-information'
  };
  return icons[props.type];
});

const iconColor = computed(() => {
  const colors = {
    success: 'success',
    error: 'error',
    warning: 'warning',
    info: 'info'
  };
  return colors[props.type];
});

const confirmColor = computed(() => {
  const colors = {
    success: 'success',
    error: 'error',
    warning: 'warning',
    info: 'primary'
  };
  return colors[props.type];
});

const closeModal = () => {
  emit('update:modelValue', false);
};

const confirmAction = () => {
  emit('confirm');
  closeModal();
};
</script>

<style scoped>
.alert-card {
  border-radius: 20px !important;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
}

/* Header Styles by Type */
.alert-header {
  padding: 24px 32px !important;
  position: relative;
  overflow: hidden;
}

.alert-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  opacity: 0.1;
  background: radial-gradient(circle at top right, rgba(255,255,255,0.3), transparent);
}

/* Warning Header - Orange/Yellow */
.alert-header-warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
}

.alert-header-warning .header-icon {
  color: white !important;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}

/* Error Header - Red */
.alert-header-error {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.alert-header-error .header-icon {
  color: white !important;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}

/* Success Header - Green */
.alert-header-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.alert-header-success .header-icon {
  color: white !important;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}

/* Info Header - Blue */
.alert-header-info {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
}

.alert-header-info .header-icon {
  color: white !important;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}

.alert-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: inherit;
  text-shadow: 0 1px 2px rgba(0,0,0,0.1);
  letter-spacing: -0.02em;
  position: relative;
  z-index: 1;
}

.alert-content {
  background: white;
  min-height: 80px;
  display: flex;
  align-items: center;
}

.alert-message {
  font-size: 1.05rem;
  color: #334155;
  line-height: 1.7;
  margin: 0;
  font-weight: 500;
}

.alert-actions {
  background: #f8fafc;
  padding: 20px 32px !important;
}

/* Confirm Button Styles */
.confirm-btn-warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
  color: white !important;
  font-weight: 700 !important;
  text-transform: none !important;
  letter-spacing: 0.02em !important;
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4) !important;
}

.confirm-btn-warning:hover {
  box-shadow: 0 6px 16px rgba(245, 158, 11, 0.5) !important;
  transform: translateY(-1px);
}

.confirm-btn-error {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
  color: white !important;
  font-weight: 700 !important;
  text-transform: none !important;
  letter-spacing: 0.02em !important;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4) !important;
}

.confirm-btn-error:hover {
  box-shadow: 0 6px 16px rgba(239, 68, 68, 0.5) !important;
  transform: translateY(-1px);
}

.confirm-btn-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
  color: white !important;
  font-weight: 700 !important;
  text-transform: none !important;
  letter-spacing: 0.02em !important;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4) !important;
}

.confirm-btn-success:hover {
  box-shadow: 0 6px 16px rgba(16, 185, 129, 0.5) !important;
  transform: translateY(-1px);
}

.confirm-btn-info {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
  color: white !important;
  font-weight: 700 !important;
  text-transform: none !important;
  letter-spacing: 0.02em !important;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4) !important;
}

.confirm-btn-info:hover {
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.5) !important;
  transform: translateY(-1px);
}
</style>