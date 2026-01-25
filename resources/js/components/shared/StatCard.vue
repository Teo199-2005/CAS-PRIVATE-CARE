<template>
  <v-card 
    class="stat-card" 
    :class="{ 'is-loaded': !loading, 'animate-in': isVisible }"
    :style="{ '--stagger-delay': `${staggerIndex * 0.1}s` }"
    elevation="0"
  >
    <!-- Skeleton Loading State -->
    <div v-if="loading" class="skeleton-wrapper">
      <div class="skeleton-icon"></div>
      <div class="skeleton-value"></div>
      <div class="skeleton-label"></div>
      <div class="skeleton-change"></div>
    </div>
    
    <!-- Actual Content -->
    <v-card-text v-else class="pa-8">
      <div class="stat-icon mb-5" :class="iconClass">
        <v-icon color="white" size="36">{{ icon }}</v-icon>
      </div>
      <div class="stat-value mb-2">
        <span class="value-text">{{ displayValue }}</span>
      </div>
      <div class="stat-label mb-3">{{ label }}</div>
      <div v-if="change" class="stat-change" :class="changeColor">
        <v-icon size="small">{{ changeIcon }}</v-icon> {{ change }}
      </div>
      <div v-if="date" class="stat-date">{{ date }}</div>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
  icon: String,
  value: [String, Number],
  label: String,
  change: String,
  changeColor: String,
  changeIcon: String,
  iconClass: { type: String, default: '' },
  date: String,
  loading: { type: Boolean, default: false },
  staggerIndex: { type: Number, default: 0 },
  animateNumbers: { type: Boolean, default: true }
});

const isVisible = ref(false);
const animatedValue = ref(0);

// Display value with number animation support
const displayValue = computed(() => {
  if (!props.animateNumbers || typeof props.value !== 'number') {
    return props.value;
  }
  return Math.round(animatedValue.value);
});

// Animate number counting
const animateNumber = (target) => {
  if (typeof target !== 'number') return;
  
  const duration = 1000;
  const startTime = performance.now();
  const startValue = animatedValue.value;
  
  const animate = (currentTime) => {
    const elapsed = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);
    
    // Ease out cubic
    const easeOut = 1 - Math.pow(1 - progress, 3);
    animatedValue.value = startValue + (target - startValue) * easeOut;
    
    if (progress < 1) {
      requestAnimationFrame(animate);
    }
  };
  
  requestAnimationFrame(animate);
};

// Watch for value changes to re-animate
watch(() => props.value, (newVal) => {
  if (props.animateNumbers && typeof newVal === 'number' && isVisible.value) {
    animateNumber(newVal);
  }
});

onMounted(() => {
  // Staggered entrance animation
  setTimeout(() => {
    isVisible.value = true;
    
    // Start number animation after card is visible
    if (props.animateNumbers && typeof props.value === 'number') {
      setTimeout(() => {
        animateNumber(props.value);
      }, 200);
    }
  }, props.staggerIndex * 100);
});
</script>

<style scoped>
/* Font is loaded at app level - no need to import here */
* {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* ========================================
   Local Animation Overrides
   Base animations (fadeInUp, shimmer, pulse) are in global animations.css
   These are component-specific variations
   - Simplified: removed scale from card animation
   - Reduced pop bounce for subtler effect
   ======================================== */
@keyframes statCardFadeIn {
  from {
    opacity: 0;
    transform: translateY(16px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes statValuePop {
  0% {
    opacity: 0;
    transform: scale(0.9);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes valueCountUp {
  from {
    opacity: 0.5;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ========================================
   Skeleton Loading Styles
   Uses shimmer from global animations.css
   ======================================== */
.skeleton-wrapper {
  padding: 2rem;
}

.skeleton-icon,
.skeleton-value,
.skeleton-label,
.skeleton-change {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s ease-in-out infinite;
  border-radius: 8px;
}

.skeleton-icon {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  margin-bottom: 1.25rem;
}

.skeleton-value {
  width: 80px;
  height: 40px;
  margin-bottom: 0.5rem;
}

.skeleton-label {
  width: 120px;
  height: 20px;
  margin-bottom: 0.75rem;
}

.skeleton-change {
  width: 60px;
  height: 16px;
}

/* ========================================
   Stat Card Base Styles
   - Uses design tokens for consistency
   - Simplified hover effects (single transform)
   ======================================== */
.stat-card {
  border-radius: var(--card-radius-lg, 20px) !important;
  border: 1px solid var(--border-default, #e5e7eb);
  transition: transform 200ms ease-out, box-shadow 200ms ease-out;
  position: relative;
  overflow: hidden;
  background: white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
  opacity: 0;
  transform: translateY(20px);
}

.stat-card.animate-in {
  animation: statCardFadeIn 0.4s ease-out forwards;
  animation-delay: var(--stagger-delay, 0s);
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 120px;
  height: 120px;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.03) 0%, transparent 70%);
  border-radius: 50%;
  transform: translate(35%, -35%);
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
}

/* Removed excessive hover animations on child elements */
.stat-card:hover .stat-value {
  color: var(--primary, #3b82f6);
}

/* ========================================
   Stat Icon Styles
   - Simplified transitions
   ======================================== */
.stat-icon {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, var(--primary, #3b82f6), var(--primary-600, #2563eb));
  border-radius: var(--card-radius-sm, 12px);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
  transition: box-shadow 200ms ease-out;
}

/* Removed rotation + scale transform on hover */
.stat-icon:hover {
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.25);
}

.stat-icon.success {
  background: linear-gradient(135deg, #10b981, #059669);
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.2);
}

.stat-icon.deep-purple {
  background: linear-gradient(135deg, #7B1FA2, #6A1B9A);
  box-shadow: 0 4px 16px rgba(123, 31, 162, 0.2);
}

.stat-icon.purple {
  background: linear-gradient(135deg, #9C27B0, #7B1FA2);
  box-shadow: 0 4px 16px rgba(156, 39, 176, 0.2);
}

.stat-icon.error {
  background: linear-gradient(135deg, #dc2626, #b91c1c);
  box-shadow: 0 4px 16px rgba(220, 38, 38, 0.2);
}

.stat-icon.grey-darken-2 {
  background: linear-gradient(135deg, #616161, #424242);
  box-shadow: 0 4px 16px rgba(97, 97, 97, 0.2);
}

.stat-value {
  font-size: 2.5rem;
  font-weight: 700;
  letter-spacing: -0.04em;
  color: var(--text-primary, #1a1a1a);
  line-height: 1;
  transition: color 200ms ease-out;
}

.stat-value .value-text {
  display: inline-block;
}

.stat-card.animate-in .stat-value .value-text {
  animation: statValuePop 0.4s ease-out forwards;
  animation-delay: calc(var(--stagger-delay, 0s) + 0.15s);
}

.stat-label {
  font-size: 0.95rem;
  color: var(--text-secondary, #666);
  font-weight: 500;
  letter-spacing: -0.01em;
}

.stat-change {
  font-size: 0.875rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

.stat-date {
  font-size: 0.8rem;
  color: #888;
  font-weight: 500;
  margin-top: 2px;
}

/* Mobile Responsive Styles */
@media (max-width: 960px) {
  .stat-card {
    border-radius: 16px !important;
  }

  .stat-card .v-card-text {
    padding: 1rem !important;
  }

  .stat-icon {
    width: 48px !important;
    height: 48px !important;
    border-radius: 12px !important;
  }

  .stat-icon .v-icon {
    font-size: 24px !important;
  }

  .stat-value {
    font-size: 1.75rem !important;
    margin-bottom: 0.5rem !important;
  }

  .stat-label {
    font-size: 0.8125rem !important;
    margin-bottom: 0.5rem !important;
  }

  .stat-change {
    font-size: 0.75rem !important;
  }

  .stat-date {
    font-size: 0.7rem !important;
  }
}

@media (max-width: 480px) {
  .stat-card .v-card-text {
    padding: 0.875rem !important;
  }

  .stat-icon {
    width: 40px !important;
    height: 40px !important;
    margin-bottom: 0.75rem !important;
  }

  .stat-icon .v-icon {
    font-size: 20px !important;
  }

  .stat-value {
    font-size: 1.5rem !important;
    margin-bottom: 0.375rem !important;
  }

  .stat-label {
    font-size: 0.75rem !important;
    margin-bottom: 0.375rem !important;
  }

  .stat-change {
    font-size: 0.6875rem !important;
  }

  .stat-date {
    font-size: 0.65rem !important;
  }
}
</style>
