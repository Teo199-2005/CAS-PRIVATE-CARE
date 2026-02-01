<template>
  <Transition name="fade">
    <div 
      v-if="isLoading" 
      class="global-loading-overlay"
      role="alert"
      aria-live="polite"
      :aria-busy="isLoading"
    >
      <div class="loading-content">
        <div class="loading-spinner" aria-hidden="true">
          <svg viewBox="0 0 50 50" class="spinner-svg">
            <circle
              cx="25"
              cy="25"
              r="20"
              fill="none"
              stroke="currentColor"
              stroke-width="4"
              stroke-linecap="round"
              class="spinner-circle"
            />
          </svg>
        </div>
        <div class="loading-text">
          <span class="loading-message">{{ displayMessage }}</span>
          <span v-if="showProgress" class="loading-progress">{{ progress }}%</span>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue';
import { useLoading } from '@/composables/useLoading';

const props = defineProps({
  progress: {
    type: Number,
    default: null
  }
});

const { isLoading, loadingMessage, loadingContext } = useLoading();

const showProgress = computed(() => props.progress !== null);

// Context-aware messages
const contextMessages = {
  default: 'Loading...',
  dashboard: 'Loading dashboard...',
  bookings: 'Loading bookings...',
  users: 'Loading users...',
  payments: 'Processing payment...',
  stripe: 'Connecting to Stripe...',
  upload: 'Uploading file...',
  save: 'Saving changes...',
  submit: 'Submitting form...',
  search: 'Searching...',
  filter: 'Applying filters...',
  export: 'Exporting data...',
  refresh: 'Refreshing...',
};

const displayMessage = computed(() => {
  if (loadingMessage.value) {
    return loadingMessage.value;
  }
  return contextMessages[loadingContext.value] || contextMessages.default;
});
</script>

<style scoped>
.global-loading-overlay {
  position: fixed;
  inset: 0;
  background: rgba(255, 255, 255, 0.92);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  backdrop-filter: blur(2px);
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .global-loading-overlay {
    background: rgba(18, 18, 18, 0.92);
  }
}

.loading-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 2rem;
  text-align: center;
}

.loading-spinner {
  width: 48px;
  height: 48px;
  color: var(--primary-color, #1976D2);
}

.spinner-svg {
  width: 100%;
  height: 100%;
  animation: rotate 1.4s linear infinite;
}

.spinner-circle {
  stroke-dasharray: 80, 200;
  stroke-dashoffset: 0;
  animation: dash 1.4s ease-in-out infinite;
}

@keyframes rotate {
  100% {
    transform: rotate(360deg);
  }
}

@keyframes dash {
  0% {
    stroke-dasharray: 1, 200;
    stroke-dashoffset: 0;
  }
  50% {
    stroke-dasharray: 89, 200;
    stroke-dashoffset: -35;
  }
  100% {
    stroke-dasharray: 89, 200;
    stroke-dashoffset: -124;
  }
}

.loading-text {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.loading-message {
  font-size: 1rem;
  font-weight: 500;
  color: var(--text-primary, #212121);
}

.loading-progress {
  font-size: 0.875rem;
  color: var(--text-secondary, #757575);
}

@media (prefers-color-scheme: dark) {
  .loading-message {
    color: var(--text-primary-dark, #ffffff);
  }
  .loading-progress {
    color: var(--text-secondary-dark, #b0b0b0);
  }
}

/* Transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .loading-content {
    padding: 1.5rem;
  }
  
  .loading-spinner {
    width: 40px;
    height: 40px;
  }
  
  .loading-message {
    font-size: 0.9375rem;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .spinner-svg,
  .spinner-circle {
    animation: none;
  }
  
  .spinner-circle {
    stroke-dasharray: 89, 200;
    stroke-dashoffset: -35;
  }
  
  .fade-enter-active,
  .fade-leave-active {
    transition: none;
  }
}
</style>
