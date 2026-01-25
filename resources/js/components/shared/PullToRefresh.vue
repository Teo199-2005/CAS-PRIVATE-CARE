<template>
  <div 
    ref="containerRef"
    class="pull-to-refresh-container"
    @touchstart.passive="handleTouchStart"
    @touchmove.passive="handleTouchMove"
    @touchend.passive="handleTouchEnd"
  >
    <!-- Pull indicator -->
    <div 
      class="pull-indicator"
      :class="{ 
        'pulling': isPulling && !isRefreshing,
        'refreshing': isRefreshing 
      }"
      :style="{ transform: `translateX(-50%) translateY(${indicatorY}px)` }"
    >
      <v-icon 
        :class="{ 'spin': isRefreshing }"
        :style="{ transform: `rotate(${pullRotation}deg)` }"
      >
        {{ isRefreshing ? 'mdi-refresh' : 'mdi-arrow-down' }}
      </v-icon>
    </div>

    <!-- Content -->
    <div 
      class="pull-content"
      :style="{ transform: `translateY(${contentOffset}px)` }"
    >
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  threshold: {
    type: Number,
    default: 80
  },
  maxPull: {
    type: Number,
    default: 120
  },
  disabled: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['refresh']);

const containerRef = ref(null);
const startY = ref(0);
const currentY = ref(0);
const isPulling = ref(false);
const isRefreshing = ref(false);
const canPull = ref(false);

const pullDistance = computed(() => {
  if (!isPulling.value) return 0;
  const distance = currentY.value - startY.value;
  return Math.min(Math.max(0, distance), props.maxPull);
});

const indicatorY = computed(() => {
  if (isRefreshing.value) return 20;
  return Math.min(pullDistance.value * 0.6 - 40, 20);
});

const contentOffset = computed(() => {
  if (isRefreshing.value) return 60;
  return pullDistance.value * 0.4;
});

const pullRotation = computed(() => {
  return (pullDistance.value / props.threshold) * 180;
});

const handleTouchStart = (e) => {
  if (props.disabled || isRefreshing.value) return;
  
  // Only allow pull when at top of scroll
  const scrollTop = containerRef.value?.scrollTop || 0;
  canPull.value = scrollTop <= 0;
  
  if (canPull.value) {
    startY.value = e.touches[0].clientY;
  }
};

const handleTouchMove = (e) => {
  if (props.disabled || isRefreshing.value || !canPull.value) return;
  
  currentY.value = e.touches[0].clientY;
  
  if (currentY.value > startY.value) {
    isPulling.value = true;
  }
};

const handleTouchEnd = async () => {
  if (!isPulling.value || isRefreshing.value) return;
  
  if (pullDistance.value >= props.threshold) {
    isRefreshing.value = true;
    isPulling.value = false;
    
    // Emit refresh event and wait for completion
    emit('refresh', () => {
      isRefreshing.value = false;
    });
    
    // Auto-complete after 5 seconds as fallback
    setTimeout(() => {
      isRefreshing.value = false;
    }, 5000);
  } else {
    isPulling.value = false;
  }
  
  startY.value = 0;
  currentY.value = 0;
  canPull.value = false;
};

// Expose method to complete refresh programmatically
const complete = () => {
  isRefreshing.value = false;
};

defineExpose({ complete });
</script>

<style scoped>
.pull-to-refresh-container {
  position: relative;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-overflow-scrolling: touch;
}

.pull-indicator {
  position: fixed;
  top: 0;
  left: 50%;
  width: 44px;
  height: 44px;
  background: white;
  border-radius: 50%;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  opacity: 0;
  transition: opacity 0.2s ease-out;
  pointer-events: none;
}

.pull-indicator.pulling,
.pull-indicator.refreshing {
  opacity: 1;
}

.pull-indicator .v-icon {
  transition: transform 0.1s linear;
  color: var(--brand-primary, #0B4FA2);
}

.pull-indicator .v-icon.spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.pull-content {
  transition: transform 0.2s ease-out;
}

/* Only enable on mobile */
@media (min-width: 961px) {
  .pull-indicator {
    display: none;
  }
  
  .pull-content {
    transform: none !important;
  }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .pull-indicator,
  .pull-content {
    transition: none;
  }
  
  .pull-indicator .v-icon.spin {
    animation: none;
  }
}
</style>
