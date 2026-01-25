<template>
  <component
    :is="tag"
    class="touch-feedback"
    :class="{ 'touch-active': isPressed }"
    @touchstart.passive="handleTouchStart"
    @touchend.passive="handleTouchEnd"
    @touchcancel.passive="handleTouchEnd"
    @mousedown="handleMouseDown"
    @mouseup="handleMouseUp"
    @mouseleave="handleMouseUp"
  >
    <slot />
  </component>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  tag: {
    type: String,
    default: 'div'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  haptic: {
    type: Boolean,
    default: true
  }
});

const isPressed = ref(false);

const triggerHaptic = () => {
  if (props.haptic && 'vibrate' in navigator) {
    // Very subtle haptic feedback (10ms)
    navigator.vibrate(10);
  }
};

const handleTouchStart = () => {
  if (props.disabled) return;
  isPressed.value = true;
  triggerHaptic();
};

const handleTouchEnd = () => {
  isPressed.value = false;
};

const handleMouseDown = () => {
  if (props.disabled) return;
  isPressed.value = true;
};

const handleMouseUp = () => {
  isPressed.value = false;
};
</script>

<style scoped>
.touch-feedback {
  transition: transform 0.1s ease-out;
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
}

.touch-feedback.touch-active {
  transform: scale(0.97);
}

/* Disable on reduced motion */
@media (prefers-reduced-motion: reduce) {
  .touch-feedback.touch-active {
    transform: none;
  }
}

/* Only apply on touch devices */
@media (hover: hover) {
  .touch-feedback.touch-active {
    transform: none;
  }
}
</style>
