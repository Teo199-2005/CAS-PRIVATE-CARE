<template>
  <v-btn
    v-bind="$attrs"
    :loading="loading"
    :disabled="loading || disabled"
    :color="color"
    :variant="variant"
    :size="size"
    :block="block"
    :class="[buttonClass, { 'loading-btn': loading }]"
    @click="handleClick"
  >
    <template v-if="loading" #prepend>
      <v-progress-circular
        indeterminate
        :size="spinnerSize"
        :width="2"
        :color="spinnerColor"
      />
    </template>
    <template v-else-if="prependIcon" #prepend>
      <v-icon>{{ prependIcon }}</v-icon>
    </template>
    
    <span :class="{ 'opacity-0': loading && hideTextOnLoad }">
      <slot>{{ loading ? loadingText : text }}</slot>
    </span>
    
    <template v-if="appendIcon && !loading" #append>
      <v-icon>{{ appendIcon }}</v-icon>
    </template>
  </v-btn>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /**
   * Loading state
   */
  loading: {
    type: Boolean,
    default: false
  },
  /**
   * Disabled state (separate from loading)
   */
  disabled: {
    type: Boolean,
    default: false
  },
  /**
   * Button text
   */
  text: {
    type: String,
    default: ''
  },
  /**
   * Text to show when loading
   */
  loadingText: {
    type: String,
    default: 'Loading...'
  },
  /**
   * Hide text when loading (show only spinner)
   */
  hideTextOnLoad: {
    type: Boolean,
    default: false
  },
  /**
   * Button color
   */
  color: {
    type: String,
    default: 'primary'
  },
  /**
   * Button variant
   */
  variant: {
    type: String,
    default: 'flat'
  },
  /**
   * Button size
   */
  size: {
    type: String,
    default: 'default'
  },
  /**
   * Full width button
   */
  block: {
    type: Boolean,
    default: false
  },
  /**
   * Prepend icon
   */
  prependIcon: {
    type: String,
    default: null
  },
  /**
   * Append icon
   */
  appendIcon: {
    type: String,
    default: null
  },
  /**
   * Additional button classes
   */
  buttonClass: {
    type: String,
    default: ''
  },
  /**
   * Prevent double-click
   */
  preventDoubleClick: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['click']);

// Computed spinner size based on button size
const spinnerSize = computed(() => {
  const sizes = {
    'x-small': 12,
    'small': 16,
    'default': 20,
    'large': 24,
    'x-large': 28
  };
  return sizes[props.size] || 20;
});

// Spinner color (contrasting with button)
const spinnerColor = computed(() => {
  // For flat/elevated variants, use white
  if (['flat', 'elevated'].includes(props.variant)) {
    return 'white';
  }
  // For outlined/text, use button color
  return props.color;
});

// Handle click with double-click prevention
let lastClick = 0;
function handleClick(event) {
  if (props.loading || props.disabled) {
    event.preventDefault();
    return;
  }
  
  if (props.preventDoubleClick) {
    const now = Date.now();
    if (now - lastClick < 500) {
      event.preventDefault();
      return;
    }
    lastClick = now;
  }
  
  emit('click', event);
}
</script>

<style scoped>
.loading-btn {
  position: relative;
  pointer-events: none;
}

.opacity-0 {
  opacity: 0;
}

/* Ensure minimum touch target */
.v-btn {
  min-height: 44px;
  min-width: 44px;
}

/* Loading spinner positioning */
.v-btn .v-progress-circular {
  margin-right: 8px;
}

/* Transition for smooth state changes */
.v-btn {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.loading-btn:active {
  transform: none;
}
</style>
