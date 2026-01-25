<template>
  <div v-if="hasError" class="error-boundary">
    <div class="error-content">
      <v-icon size="64" color="error" class="mb-4">mdi-alert-circle-outline</v-icon>
      <h2 class="error-title">Something went wrong</h2>
      <p class="error-message">{{ errorMessage }}</p>
      <div class="error-actions">
        <v-btn color="primary" @click="resetError" class="mr-2">
          <v-icon start>mdi-refresh</v-icon>
          Try Again
        </v-btn>
        <v-btn variant="outlined" @click="goHome">
          <v-icon start>mdi-home</v-icon>
          Go Home
        </v-btn>
      </div>
      <details v-if="showDetails && errorDetails" class="error-details mt-4">
        <summary>Technical Details</summary>
        <pre>{{ errorDetails }}</pre>
      </details>
    </div>
  </div>
  <slot v-else></slot>
</template>

<script setup>
import { ref, onErrorCaptured } from 'vue';

const props = defineProps({
  fallbackMessage: {
    type: String,
    default: 'We encountered an unexpected error. Please try again or contact support if the problem persists.'
  },
  showDetails: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['error']);

const hasError = ref(false);
const errorMessage = ref('');
const errorDetails = ref('');

onErrorCaptured((error, instance, info) => {
  hasError.value = true;
  errorMessage.value = props.fallbackMessage;
  errorDetails.value = `Error: ${error.message}\nComponent: ${instance?.$options?.name || 'Unknown'}\nInfo: ${info}`;
  
  // Log error for monitoring
  console.error('[ErrorBoundary] Caught error:', {
    error: error.message,
    stack: error.stack,
    component: instance?.$options?.name,
    info
  });
  
  // Emit error event for parent handling
  emit('error', { error, instance, info });
  
  // Prevent error from propagating
  return false;
});

const resetError = () => {
  hasError.value = false;
  errorMessage.value = '';
  errorDetails.value = '';
};

const goHome = () => {
  window.location.href = '/';
};
</script>

<style scoped>
.error-boundary {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  padding: 2rem;
  background: linear-gradient(135deg, #fff5f5 0%, #fff 100%);
  border-radius: 16px;
  border: 1px solid #fee2e2;
}

.error-content {
  text-align: center;
  max-width: 500px;
}

.error-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.error-message {
  color: #6b7280;
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.error-actions {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.error-details {
  text-align: left;
  background: #f9fafb;
  border-radius: 8px;
  padding: 1rem;
  border: 1px solid #e5e7eb;
}

.error-details summary {
  cursor: pointer;
  font-weight: 500;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.error-details pre {
  font-size: 0.75rem;
  color: #374151;
  white-space: pre-wrap;
  word-break: break-word;
  margin: 0;
}

@media (max-width: 480px) {
  .error-boundary {
    padding: 1rem;
    min-height: 300px;
  }
  
  .error-title {
    font-size: 1.25rem;
  }
  
  .error-actions {
    flex-direction: column;
  }
  
  .error-actions .v-btn {
    width: 100%;
    margin: 0.25rem 0 !important;
  }
}
</style>
