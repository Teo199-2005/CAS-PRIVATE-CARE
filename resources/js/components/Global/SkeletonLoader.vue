<template>
  <div 
    class="skeleton-loader"
    :class="[`skeleton-${variant}`, { 'skeleton-animated': animated }]"
    :style="skeletonStyle"
    role="progressbar"
    aria-label="Loading content"
    :aria-busy="true"
  >
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /**
   * Skeleton variant type
   */
  variant: {
    type: String,
    default: 'text',
    validator: (v) => ['text', 'circular', 'rectangular', 'rounded', 'card', 'avatar', 'button'].includes(v)
  },
  /**
   * Width (CSS value)
   */
  width: {
    type: [String, Number],
    default: '100%'
  },
  /**
   * Height (CSS value)
   */
  height: {
    type: [String, Number],
    default: null
  },
  /**
   * Enable shimmer animation
   */
  animated: {
    type: Boolean,
    default: true
  },
  /**
   * Number of text lines to show
   */
  lines: {
    type: Number,
    default: 1
  },
  /**
   * Border radius for rounded variant
   */
  borderRadius: {
    type: [String, Number],
    default: null
  }
});

const skeletonStyle = computed(() => {
  const style = {};
  
  // Width
  if (props.width) {
    style.width = typeof props.width === 'number' ? `${props.width}px` : props.width;
  }
  
  // Height
  if (props.height) {
    style.height = typeof props.height === 'number' ? `${props.height}px` : props.height;
  } else {
    // Default heights by variant
    const defaultHeights = {
      text: '1em',
      circular: '40px',
      avatar: '40px',
      button: '36px',
      rectangular: '100px',
      rounded: '100px',
      card: '200px'
    };
    style.height = defaultHeights[props.variant] || '1em';
  }
  
  // Border radius
  if (props.borderRadius) {
    style.borderRadius = typeof props.borderRadius === 'number' 
      ? `${props.borderRadius}px` 
      : props.borderRadius;
  }
  
  return style;
});
</script>

<style scoped>
.skeleton-loader {
  background: linear-gradient(
    90deg,
    var(--skeleton-base, #e0e0e0) 25%,
    var(--skeleton-highlight, #f5f5f5) 50%,
    var(--skeleton-base, #e0e0e0) 75%
  );
  background-size: 200% 100%;
  display: block;
}

.skeleton-animated {
  animation: skeleton-shimmer 1.5s infinite linear;
}

@keyframes skeleton-shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Variants */
.skeleton-text {
  border-radius: 4px;
  max-width: 100%;
}

.skeleton-circular,
.skeleton-avatar {
  border-radius: 50%;
  flex-shrink: 0;
}

.skeleton-rectangular {
  border-radius: 0;
}

.skeleton-rounded {
  border-radius: 8px;
}

.skeleton-card {
  border-radius: 12px;
}

.skeleton-button {
  border-radius: 4px;
  width: auto;
  min-width: 64px;
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
  .skeleton-loader {
    background: linear-gradient(
      90deg,
      var(--skeleton-base-dark, #333333) 25%,
      var(--skeleton-highlight-dark, #444444) 50%,
      var(--skeleton-base-dark, #333333) 75%
    );
    background-size: 200% 100%;
  }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .skeleton-loader {
    animation: none;
    background: var(--skeleton-base, #e0e0e0);
  }
  
  @media (prefers-color-scheme: dark) {
    .skeleton-loader {
      background: var(--skeleton-base-dark, #333333);
    }
  }
}
</style>
