<template>
  <v-alert
    v-if="hasError"
    :type="alertType"
    :class="['error-message', `error-message--${variant}`, customClass]"
    :closable="dismissible"
    :density="density"
    :rounded="rounded"
    @click:close="handleDismiss"
  >
    <template #prepend>
      <v-icon :icon="icon" :size="iconSize" />
    </template>

    <template #title v-if="title">
      {{ title }}
    </template>

    <div class="error-message__content">
      <p class="error-message__text">{{ displayMessage }}</p>
      
      <!-- Validation errors list -->
      <ul v-if="hasDetails" class="error-message__details">
        <li v-for="(messages, field) in details" :key="field">
          <strong>{{ formatFieldName(field) }}:</strong>
          <span v-if="Array.isArray(messages)">{{ messages.join(', ') }}</span>
          <span v-else>{{ messages }}</span>
        </li>
      </ul>
      
      <!-- Actions -->
      <div v-if="showActions" class="error-message__actions">
        <v-btn
          v-if="retryable"
          variant="outlined"
          size="small"
          color="error"
          :loading="retrying"
          @click="handleRetry"
        >
          <v-icon start>mdi-refresh</v-icon>
          {{ retryText }}
        </v-btn>
        
        <v-btn
          v-if="showSupport && status >= 500"
          variant="text"
          size="small"
          color="error"
          @click="handleContactSupport"
        >
          <v-icon start>mdi-help-circle-outline</v-icon>
          Contact Support
        </v-btn>
      </div>
    </div>
  </v-alert>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  /**
   * Error object or message string
   * Can be: string, Error, or normalized error object with { status, message, details }
   */
  error: {
    type: [String, Object, Error],
    default: null,
  },
  
  /**
   * Optional title override
   */
  title: {
    type: String,
    default: '',
  },
  
  /**
   * Alert variant style
   */
  variant: {
    type: String,
    default: 'standard',
    validator: (v) => ['standard', 'outlined', 'tonal', 'flat', 'text', 'elevated'].includes(v),
  },
  
  /**
   * Whether the alert can be dismissed
   */
  dismissible: {
    type: Boolean,
    default: true,
  },
  
  /**
   * Whether to show retry button
   */
  retryable: {
    type: Boolean,
    default: false,
  },
  
  /**
   * Custom retry button text
   */
  retryText: {
    type: String,
    default: 'Try Again',
  },
  
  /**
   * Whether to show contact support option for server errors
   */
  showSupport: {
    type: Boolean,
    default: true,
  },
  
  /**
   * Custom class for the alert
   */
  customClass: {
    type: String,
    default: '',
  },
  
  /**
   * Alert density
   */
  density: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'comfortable', 'compact'].includes(v),
  },
  
  /**
   * Corner rounding
   */
  rounded: {
    type: [Boolean, String],
    default: 'md',
  },
})

const emit = defineEmits(['dismiss', 'retry', 'contact-support'])

const retrying = ref(false)

/**
 * Check if there's an error to display
 */
const hasError = computed(() => {
  if (!props.error) return false
  if (typeof props.error === 'string') return props.error.length > 0
  return true
})

/**
 * Get HTTP status from error
 */
const status = computed(() => {
  if (typeof props.error === 'object' && props.error !== null) {
    return props.error.status || props.error.response?.status || 500
  }
  return 500
})

/**
 * Get display message based on error type
 */
const displayMessage = computed(() => {
  if (!props.error) return ''
  
  // String error
  if (typeof props.error === 'string') return props.error
  
  // Normalized error object
  if (props.error.message) return props.error.message
  
  // Standard Error object
  if (props.error instanceof Error) return getUserFriendlyMessage(500, props.error.message)
  
  // Axios response error
  if (props.error.response) {
    const data = props.error.response.data
    return getUserFriendlyMessage(
      props.error.response.status,
      data?.message || data?.error
    )
  }
  
  return 'An unexpected error occurred. Please try again.'
})

/**
 * Get validation error details
 */
const details = computed(() => {
  if (!props.error) return null
  
  // Check for Laravel validation errors format
  if (props.error.details) return props.error.details
  if (props.error.errors) return props.error.errors
  if (props.error.response?.data?.errors) return props.error.response.data.errors
  
  return null
})

/**
 * Check if there are details to display
 */
const hasDetails = computed(() => {
  if (!details.value) return false
  return Object.keys(details.value).length > 0
})

/**
 * Show actions if retryable or showing support
 */
const showActions = computed(() => {
  return props.retryable || (props.showSupport && status.value >= 500)
})

/**
 * Get appropriate alert type based on error status
 */
const alertType = computed(() => {
  if (status.value >= 500) return 'error'
  if (status.value === 429) return 'warning'
  if (status.value >= 400) return 'error'
  return 'error'
})

/**
 * Get appropriate icon based on error type
 */
const icon = computed(() => {
  if (status.value >= 500) return 'mdi-server-off'
  if (status.value === 429) return 'mdi-timer-sand'
  if (status.value === 408) return 'mdi-clock-alert-outline'
  if (status.value === 404) return 'mdi-file-question-outline'
  if (status.value === 403) return 'mdi-lock-outline'
  if (status.value === 401) return 'mdi-account-lock-outline'
  if (status.value === 0) return 'mdi-wifi-off'
  return 'mdi-alert-circle-outline'
})

/**
 * Icon size
 */
const iconSize = computed(() => {
  return props.density === 'compact' ? 'small' : 'default'
})

/**
 * Get user-friendly error message based on HTTP status
 */
function getUserFriendlyMessage(httpStatus, serverMessage) {
  const messages = {
    0: 'Unable to connect. Please check your internet connection and try again.',
    400: 'The request was invalid. Please check your input and try again.',
    401: 'Your session has expired. Please log in again.',
    403: 'You don\'t have permission to perform this action.',
    404: 'The requested resource was not found.',
    408: 'The request timed out. Please try again.',
    409: 'There was a conflict with existing data. Please refresh and try again.',
    422: serverMessage || 'Please check your input and correct any errors.',
    429: 'Too many requests. Please wait a moment and try again.',
    500: 'Something went wrong on our end. Please try again later.',
    502: 'Service temporarily unavailable. Please try again in a few minutes.',
    503: 'Service temporarily unavailable. We\'re working on it!',
  }

  return messages[httpStatus] || serverMessage || 'An unexpected error occurred. Please try again.'
}

/**
 * Format field name from snake_case to Title Case
 */
function formatFieldName(field) {
  return field
    .replace(/_/g, ' ')
    .replace(/\./g, ' → ')
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}

/**
 * Handle dismiss button click
 */
function handleDismiss() {
  emit('dismiss')
}

/**
 * Handle retry button click
 */
async function handleRetry() {
  retrying.value = true
  try {
    await emit('retry')
  } finally {
    retrying.value = false
  }
}

/**
 * Handle contact support click
 */
function handleContactSupport() {
  emit('contact-support')
}
</script>

<style scoped>
.error-message {
  margin-bottom: 16px;
}

.error-message__content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.error-message__text {
  margin: 0;
  line-height: 1.5;
}

.error-message__details {
  margin: 0;
  padding-left: 20px;
  font-size: 0.875rem;
}

.error-message__details li {
  margin-bottom: 4px;
}

.error-message__details li:last-child {
  margin-bottom: 0;
}

.error-message__actions {
  display: flex;
  gap: 8px;
  margin-top: 4px;
}

/* Mobile responsive */
@media (max-width: 480px) {
  .error-message__actions {
    flex-direction: column;
  }
  
  .error-message__actions .v-btn {
    width: 100%;
  }
}

/* Compact variant */
.error-message--compact {
  padding: 8px 12px;
}

.error-message--compact .error-message__text {
  font-size: 0.875rem;
}
</style>
