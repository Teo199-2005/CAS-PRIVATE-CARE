<template>
  <v-dialog v-model="isOpen" max-width="400" persistent>
    <v-card class="alert-card">
      <v-card-title class="alert-header pa-6">
        <v-icon :icon="icon" :color="iconColor" size="24" class="mr-3"></v-icon>
        <span class="alert-title">{{ title }}</span>
      </v-card-title>
      <v-divider />
      <v-card-text class="alert-content pa-6">
        {{ message }}
      </v-card-text>
      <v-divider />
      <v-card-actions class="alert-actions pa-6">
        <v-spacer />
        <v-btn variant="outlined" @click="closeModal" v-if="showCancel">Cancel</v-btn>
        <v-btn :color="confirmColor" @click="confirmAction">{{ confirmText }}</v-btn>
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
  border-radius: 16px !important;
}

.alert-header {
  background: #fafafa;
}

.alert-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1a1a1a;
}

.alert-content {
  font-size: 1rem;
  color: #4a5568;
  line-height: 1.5;
}

.alert-actions {
  background: #fafafa;
}
</style>