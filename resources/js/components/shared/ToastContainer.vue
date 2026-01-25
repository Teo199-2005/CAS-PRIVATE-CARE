<template>
  <Teleport to="body">
    <div class="toast-container" :class="positionClass">
      <TransitionGroup name="toast">
        <div 
          v-for="toast in toasts" 
          :key="toast.id"
          class="toast"
          :class="[`toast-${toast.type}`, { 'toast-with-action': toast.action }]"
          :style="toastStyle(toast)"
          role="alert"
          :aria-live="toast.type === 'error' ? 'assertive' : 'polite'"
        >
          <!-- Icon -->
          <span class="toast-icon" :style="{ color: toast.color }">
            <span v-if="toast.type === 'loading'" class="toast-spinner"></span>
            <span v-else>{{ toast.icon }}</span>
          </span>
          
          <!-- Content -->
          <div class="toast-content">
            <strong v-if="toast.title" class="toast-title">{{ toast.title }}</strong>
            <p class="toast-message">{{ toast.message }}</p>
            
            <!-- Actions -->
            <div v-if="toast.action" class="toast-actions">
              <button 
                v-if="toast.action.confirm"
                class="toast-btn toast-btn-confirm"
                @click="toast.action.confirm.handler"
              >
                {{ toast.action.confirm.label }}
              </button>
              <button 
                v-if="toast.action.cancel"
                class="toast-btn toast-btn-cancel"
                @click="toast.action.cancel.handler"
              >
                {{ toast.action.cancel.label }}
              </button>
            </div>
          </div>
          
          <!-- Dismiss button -->
          <button 
            v-if="toast.dismissible"
            class="toast-close"
            @click="remove(toast.id)"
            aria-label="Dismiss notification"
          >
            ✕
          </button>
          
          <!-- Progress bar -->
          <div 
            v-if="toast.duration > 0"
            class="toast-progress"
            :style="{ animationDuration: `${toast.duration}ms` }"
          ></div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
  position: {
    type: String,
    default: 'top-right',
    validator: (v) => [
      'top-left', 'top-center', 'top-right',
      'bottom-left', 'bottom-center', 'bottom-right'
    ].includes(v)
  }
});

const { toasts, remove } = useToast();

const positionClass = computed(() => `toast-${props.position}`);

const toastStyle = (toast) => ({
  '--toast-bg': toast.bgColor,
  '--toast-color': toast.color
});
</script>

<style scoped>
.toast-container {
  position: fixed;
  z-index: 10000;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  pointer-events: none;
  padding: 1rem;
  max-width: 100%;
}

/* Position variants */
.toast-top-left {
  top: 0;
  left: 0;
}

.toast-top-center {
  top: 0;
  left: 50%;
  transform: translateX(-50%);
}

.toast-top-right {
  top: 0;
  right: 0;
}

.toast-bottom-left {
  bottom: 0;
  left: 0;
  flex-direction: column-reverse;
}

.toast-bottom-center {
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  flex-direction: column-reverse;
}

.toast-bottom-right {
  bottom: 0;
  right: 0;
  flex-direction: column-reverse;
}

/* Toast item */
.toast {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem;
  background: var(--toast-bg, #fff);
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  min-width: 300px;
  max-width: 400px;
  pointer-events: auto;
  position: relative;
  overflow: hidden;
}

.toast-icon {
  font-size: 1.25rem;
  flex-shrink: 0;
  line-height: 1;
}

.toast-spinner {
  display: inline-block;
  width: 1rem;
  height: 1rem;
  border: 2px solid currentColor;
  border-right-color: transparent;
  border-radius: 50%;
  animation: spin 0.75s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.toast-content {
  flex: 1;
  min-width: 0;
}

.toast-title {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #111;
  margin-bottom: 0.25rem;
}

.toast-message {
  font-size: 0.875rem;
  color: #333;
  margin: 0;
  word-wrap: break-word;
}

.toast-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.75rem;
}

.toast-btn {
  padding: 0.375rem 0.75rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.toast-btn-confirm {
  background: var(--toast-color, #3b82f6);
  color: white;
  border: none;
}

.toast-btn-confirm:hover {
  opacity: 0.9;
}

.toast-btn-cancel {
  background: transparent;
  border: 1px solid #ddd;
  color: #666;
}

.toast-btn-cancel:hover {
  background: #f5f5f5;
}

.toast-close {
  background: none;
  border: none;
  color: #999;
  cursor: pointer;
  padding: 0.25rem;
  font-size: 0.875rem;
  line-height: 1;
  transition: color 0.2s;
}

.toast-close:hover {
  color: #333;
}

.toast-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  background: var(--toast-color, #3b82f6);
  animation: progress linear forwards;
  width: 100%;
}

@keyframes progress {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}

/* Transitions */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.9);
}

/* Position-specific transitions */
.toast-top-left .toast-enter-from,
.toast-bottom-left .toast-enter-from {
  transform: translateX(-100%);
}

.toast-top-left .toast-leave-to,
.toast-bottom-left .toast-leave-to {
  transform: translateX(-100%) scale(0.9);
}

.toast-top-center .toast-enter-from,
.toast-bottom-center .toast-enter-from {
  transform: translateY(-100%);
}

.toast-top-center .toast-leave-to,
.toast-bottom-center .toast-leave-to {
  transform: translateY(-100%) scale(0.9);
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
  .toast {
    background: #1f2937;
  }
  
  .toast-title {
    color: #f9fafb;
  }
  
  .toast-message {
    color: #d1d5db;
  }
  
  .toast-close {
    color: #6b7280;
  }
  
  .toast-close:hover {
    color: #f9fafb;
  }
}

/* Mobile responsiveness */
@media (max-width: 480px) {
  .toast-container {
    padding: 0.5rem;
  }
  
  .toast {
    min-width: 0;
    max-width: 100%;
  }
  
  .toast-top-center,
  .toast-bottom-center {
    left: 0;
    right: 0;
    transform: none;
  }
}
</style>
