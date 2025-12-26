<template>
  <v-dialog v-model="isOpen" :max-width="maxWidth" persistent>
    <v-card class="modal-card">
      <v-card-title class="modal-header pa-6 d-flex justify-space-between align-center">
        <span class="modal-title">{{ title }}</span>
        <v-btn icon="mdi-close" variant="text" @click="closeModal" />
      </v-card-title>
      <v-divider />
      <v-card-text class="modal-content pa-6">
        <slot></slot>
      </v-card-text>
      <v-divider v-if="showActions" />
      <v-card-actions v-if="showActions" class="modal-actions pa-6">
        <v-spacer />
        <slot name="actions">
          <v-btn variant="outlined" @click="closeModal">Cancel</v-btn>
          <v-btn color="primary" @click="$emit('confirm')">Confirm</v-btn>
        </slot>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, required: true },
  maxWidth: { type: String, default: '600' },
  showActions: { type: Boolean, default: true }
});

const emit = defineEmits(['update:modelValue', 'confirm']);

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const closeModal = () => {
  emit('update:modelValue', false);
};
</script>

<style scoped>
.modal-card {
  border-radius: 16px !important;
}

.modal-header {
  background: #fafafa;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1a1a1a;
}

.modal-content {
  max-height: 70vh;
  overflow-y: auto;
}

.modal-actions {
  background: #fafafa;
}
</style>