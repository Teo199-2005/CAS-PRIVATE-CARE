<template>
  <slot v-if="!error" />
  <div v-else class="error-boundary" role="alert">
    <div class="error-boundary__content">
      <div class="error-boundary__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </div>
      <h3 class="error-boundary__title">{{ title }}</h3>
      <p class="error-boundary__message">{{ displayMessage }}</p>
      
      <div class="error-boundary__actions">
        <button 
          v-if="showRetry"
          type="button"
          class="error-boundary__btn error-boundary__btn--primary"
          @click="handleRetry"
        >
          Try Again
        </button>
        <button 
          v-if="showReload"
          type="button"
          class="error-boundary__btn error-boundary__btn--secondary"
          @click="handleReload"
        >
          Reload Page
        </button>
      </div>
      
      <!-- Development mode: show error details -->
      <details v-if="isDev && error" class="error-boundary__details">
        <summary>Error Details</summary>
        <pre>{{ errorDetails }}</pre>
      </details>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onErrorCaptured, watch } from 'vue';

const props = defineProps({
  /**
   * Error title
   */
  title: {
    type: String,
    default: 'Something went wrong'
  },
  /**
   * Custom fallback message
   */
  fallbackMessage: {
    type: String,
    default: 'An unexpected error occurred. Please try again.'
  },
  /**
   * Show retry button
   */
  showRetry: {
    type: Boolean,
    default: true
  },
  /**
   * Show reload page button
   */
  showReload: {
    type: Boolean,
    default: true
  },
  /**
   * Callback when retry is clicked
   */
  onRetry: {
    type: Function,
    default: null
  },
  /**
   * Report errors to external service
   */
  reportError: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['error', 'retry', 'reset']);

const error = ref(null);
const errorInfo = ref(null);

const isDev = computed(() => {
  return import.meta.env?.DEV || process.env?.NODE_ENV === 'development';
});

const displayMessage = computed(() => {
  if (isDev.value && error.value) {
    return error.value.message || props.fallbackMessage;
  }
  return props.fallbackMessage;
});

const errorDetails = computed(() => {
  if (!error.value) return '';
  
  return JSON.stringify({
    name: error.value.name,
    message: error.value.message,
    stack: error.value.stack,
    info: errorInfo.value
  }, null, 2);
});

// Capture errors from child components
onErrorCaptured((err, instance, info) => {
  error.value = err;
  errorInfo.value = info;
  
  emit('error', { error: err, info });
  
  // Report error to logging service
  if (props.reportError) {
    reportErrorToService(err, info);
  }
  
  // Prevent propagation
  return false;
});

const handleRetry = () => {
  error.value = null;
  errorInfo.value = null;
  
  emit('retry');
  
  if (props.onRetry) {
    props.onRetry();
  }
};

const handleReload = () => {
  window.location.reload();
};

const reportErrorToService = async (err, info) => {
  try {
    await fetch('/api/errors/log', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      },
      body: JSON.stringify({
        error: {
          name: err.name,
          message: err.message,
          stack: err.stack
        },
        info,
        url: window.location.href,
        userAgent: navigator.userAgent,
        timestamp: new Date().toISOString()
      })
    });
  } catch (e) {
    console.error('Failed to report error:', e);
  }
};

// Reset error when props change (allows parent to reset)
watch(() => props.fallbackMessage, () => {
  if (error.value) {
    emit('reset');
  }
});

// Expose reset method
defineExpose({
  reset: () => {
    error.value = null;
    errorInfo.value = null;
  },
  hasError: () => !!error.value
});
</script>

<style scoped>
.error-boundary {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  padding: 2rem;
  background: var(--error-bg, #fff5f5);
  border-radius: 12px;
  border: 1px solid var(--error-border, #ffcdd2);
}

.error-boundary__content {
  text-align: center;
  max-width: 400px;
}

.error-boundary__icon {
  width: 48px;
  height: 48px;
  margin: 0 auto 1rem;
  color: var(--error-color, #d32f2f);
}

.error-boundary__icon svg {
  width: 100%;
  height: 100%;
}

.error-boundary__title {
  margin: 0 0 0.5rem;
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-primary, #212121);
}

.error-boundary__message {
  margin: 0 0 1.5rem;
  font-size: 0.9375rem;
  color: var(--text-secondary, #757575);
  line-height: 1.5;
}

.error-boundary__actions {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
  flex-wrap: wrap;
}

.error-boundary__btn {
  padding: 0.625rem 1.25rem;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
}

.error-boundary__btn--primary {
  background: var(--primary-color, #1976D2);
  color: white;
}

.error-boundary__btn--primary:hover {
  background: var(--primary-dark, #1565C0);
}

.error-boundary__btn--secondary {
  background: transparent;
  color: var(--text-secondary, #757575);
  border: 1px solid var(--border-color, #e0e0e0);
}

.error-boundary__btn--secondary:hover {
  background: var(--hover-bg, #f5f5f5);
}

.error-boundary__details {
  margin-top: 1.5rem;
  text-align: left;
}

.error-boundary__details summary {
  cursor: pointer;
  font-size: 0.875rem;
  color: var(--text-secondary, #757575);
}

.error-boundary__details pre {
  margin-top: 0.5rem;
  padding: 1rem;
  font-size: 0.75rem;
  background: var(--code-bg, #f5f5f5);
  border-radius: 8px;
  overflow-x: auto;
  white-space: pre-wrap;
  word-break: break-word;
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
  .error-boundary {
    background: var(--error-bg-dark, #2d1f1f);
    border-color: var(--error-border-dark, #5a3333);
  }
  
  .error-boundary__title {
    color: var(--text-primary-dark, #ffffff);
  }
  
  .error-boundary__message {
    color: var(--text-secondary-dark, #b0b0b0);
  }
  
  .error-boundary__btn--secondary {
    border-color: var(--border-color-dark, #444444);
    color: var(--text-secondary-dark, #b0b0b0);
  }
  
  .error-boundary__btn--secondary:hover {
    background: var(--hover-bg-dark, #333333);
  }
  
  .error-boundary__details pre {
    background: var(--code-bg-dark, #1e1e1e);
    color: var(--text-primary-dark, #ffffff);
  }
}

/* Mobile */
@media (max-width: 768px) {
  .error-boundary {
    padding: 1.5rem;
    min-height: 150px;
  }
  
  .error-boundary__icon {
    width: 40px;
    height: 40px;
  }
  
  .error-boundary__title {
    font-size: 1.125rem;
  }
  
  .error-boundary__message {
    font-size: 0.875rem;
  }
  
  .error-boundary__actions {
    flex-direction: column;
  }
  
  .error-boundary__btn {
    width: 100%;
  }
}
</style>
