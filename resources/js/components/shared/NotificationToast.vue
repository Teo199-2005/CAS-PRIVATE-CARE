<template>
  <v-snackbar
    v-model="show"
    :color="getColor()"
    :timeout="timeout"
    location="top right"
    class="notification-toast"
  >
    <div class="d-flex align-center">
      <v-icon :icon="getIcon()" class="mr-3" size="20"></v-icon>
      <div>
        <div v-if="title" class="notification-title">{{ title }}</div>
        <div class="notification-message">{{ message }}</div>
      </div>
    </div>
    <template v-slot:actions>
      <v-btn
        variant="text"
        icon="mdi-close"
        size="small"
        @click="show = false"
      ></v-btn>
    </template>
  </v-snackbar>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: Boolean,
  type: {
    type: String,
    default: 'success',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  title: String,
  message: {
    type: String,
    required: true
  },
  timeout: {
    type: Number,
    default: 4000
  }
});

const emit = defineEmits(['update:modelValue']);

const show = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const getColor = () => {
  const colors = {
    success: 'success',
    error: 'error',
    warning: 'warning',
    info: 'info'
  };
  return colors[props.type] || 'success';
};

const getIcon = () => {
  const icons = {
    success: 'mdi-check-circle',
    error: 'mdi-alert-circle',
    warning: 'mdi-alert',
    info: 'mdi-information'
  };
  return icons[props.type] || 'mdi-check-circle';
};
</script>

<style scoped>
.notification-toast :deep(.v-snackbar__wrapper) {
  border-radius: 12px !important;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15) !important;
}

.notification-title {
  font-weight: 600;
  font-size: 0.95rem;
  margin-bottom: 2px;
}

.notification-message {
  font-size: 0.875rem;
  opacity: 0.9;
}
</style>
