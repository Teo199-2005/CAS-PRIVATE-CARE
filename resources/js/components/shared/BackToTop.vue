<template>
  <Transition name="fade-slide">
    <button 
      v-show="isVisible"
      class="back-to-top-btn"
      :class="{ 'visible': isVisible }"
      @click="scrollToTop"
      aria-label="Scroll to top of page"
      type="button"
    >
      <v-icon size="24">mdi-chevron-up</v-icon>
    </button>
  </Transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  threshold: {
    type: Number,
    default: 300 // Show button after scrolling 300px
  },
  smooth: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['scroll-top']);

const isVisible = ref(false);
let scrollContainer = null;

const checkScroll = () => {
  const scrollY = scrollContainer 
    ? scrollContainer.scrollTop 
    : window.scrollY || document.documentElement.scrollTop;
  
  isVisible.value = scrollY > props.threshold;
};

const scrollToTop = () => {
  const scrollOptions = {
    top: 0,
    behavior: props.smooth && !prefersReducedMotion() ? 'smooth' : 'auto'
  };

  if (scrollContainer) {
    scrollContainer.scrollTo(scrollOptions);
  } else {
    window.scrollTo(scrollOptions);
  }
  
  emit('scroll-top');
};

const prefersReducedMotion = () => {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
};

onMounted(() => {
  // Try to find the main scroll container (v-main) or use window
  scrollContainer = document.querySelector('.v-main') || document.querySelector('main');
  
  if (scrollContainer) {
    scrollContainer.addEventListener('scroll', checkScroll, { passive: true });
  }
  window.addEventListener('scroll', checkScroll, { passive: true });
  
  // Initial check
  checkScroll();
});

onUnmounted(() => {
  if (scrollContainer) {
    scrollContainer.removeEventListener('scroll', checkScroll);
  }
  window.removeEventListener('scroll', checkScroll);
});
</script>

<style scoped>
.back-to-top-btn {
  position: fixed;
  bottom: 88px;
  right: 16px;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--brand-primary, #0B4FA2);
  color: white;
  border: none;
  box-shadow: 0 4px 16px rgba(11, 79, 162, 0.3);
  z-index: 99;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease-out;
}

.back-to-top-btn:hover {
  background: var(--brand-primary-dark, #093d7a);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(11, 79, 162, 0.4);
}

.back-to-top-btn:active {
  transform: scale(0.95);
}

.back-to-top-btn:focus-visible {
  outline: 3px solid rgba(59, 130, 246, 0.5);
  outline-offset: 2px;
}

/* Safe area support for notched devices */
@supports (padding: env(safe-area-inset-bottom)) {
  .back-to-top-btn {
    bottom: calc(88px + env(safe-area-inset-bottom));
  }
}

/* Transition animations */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.2s ease-out, transform 0.2s ease-out;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* Desktop positioning */
@media (min-width: 961px) {
  .back-to-top-btn {
    bottom: 32px;
    right: 32px;
  }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .back-to-top-btn {
    transition: opacity 0.1s;
  }
  
  .back-to-top-btn:hover {
    transform: none;
  }
  
  .fade-slide-enter-active,
  .fade-slide-leave-active {
    transition: opacity 0.1s;
  }
  
  .fade-slide-enter-from,
  .fade-slide-leave-to {
    transform: none;
  }
}
</style>
