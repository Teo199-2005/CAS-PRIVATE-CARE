<template>
  <v-card 
    class="stat-card" 
    :class="['stat-card-accent-' + (iconClass || 'primary'), { 'is-loaded': !loading, 'animate-in': isVisible }]"
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
    <v-card-text v-else class="stat-card-content pa-0">
      <!-- Top section: icon -->
      <div class="stat-card-header pa-6 pb-4">
        <div class="stat-icon" :class="iconClass">
          <v-icon color="white" size="36">{{ icon }}</v-icon>
        </div>
      </div>
      <v-divider class="stat-divider mx-4" />
      <!-- Main section: value & label left, change/date right -->
      <div class="stat-card-body pa-6 py-4 d-flex align-start justify-space-between gap-3">
        <div class="stat-body-left flex-grow-1 min-width-0">
          <div class="stat-value mb-1">
            <span class="value-text">{{ displayValue }}</span>
          </div>
          <div class="stat-label">{{ label }}</div>
        </div>
        <div v-if="change || date" class="stat-body-right text-end">
          <div v-if="change" class="stat-change" :class="changeColor">
            <v-icon size="small" class="stat-change-icon">{{ changeIcon }}</v-icon>
            <span class="stat-change-text">{{ change }}</span>
          </div>
          <div v-if="date" class="stat-date">{{ date }}</div>
        </div>
      </div>
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
   - Simple card, metallic on icons only
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

.stat-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, rgba(59, 130, 246, 0.5) 0%, rgba(59, 130, 246, 0.15) 100%);
  border-radius: var(--card-radius-lg, 20px) var(--card-radius-lg, 20px) 0 0;
}

.stat-card-accent-success::after {
  background: linear-gradient(90deg, rgba(5, 150, 105, 0.5) 0%, rgba(5, 150, 105, 0.15) 100%);
}
.stat-card-accent-error::after {
  background: linear-gradient(90deg, rgba(185, 28, 28, 0.5) 0%, rgba(185, 28, 28, 0.15) 100%);
}
.stat-card-accent-deep-purple::after,
.stat-card-accent-purple::after {
  background: linear-gradient(90deg, rgba(106, 27, 154, 0.5) 0%, rgba(106, 27, 154, 0.15) 100%);
}
.stat-card-accent-grey-darken-2::after {
  background: linear-gradient(90deg, rgba(71, 85, 105, 0.5) 0%, rgba(71, 85, 105, 0.15) 100%);
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

.stat-card:hover .stat-value {
  color: var(--primary, #3b82f6);
}

/* Dividers */
.stat-divider {
  border-color: rgba(0, 0, 0, 0.06) !important;
  opacity: 1;
}

.stat-card-header {
  background: linear-gradient(to bottom, rgba(248, 250, 252, 0.5) 0%, transparent 100%);
}

.stat-card-body {
  min-height: 72px;
}

.stat-body-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  justify-content: flex-start;
  gap: 2px;
  min-width: 0;
  max-width: 55%;
}

.stat-body-right .stat-change {
  text-align: right;
  line-height: 1.3;
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  justify-content: flex-end;
  gap: 4px;
}

.stat-change-icon {
  flex-shrink: 0;
}

.stat-change-text {
  overflow-wrap: break-word;
  word-break: break-word;
  min-width: 0;
}

/* Mobile: stack value/label and change vertically so change text has full width and doesn't fragment */
@media (max-width: 600px) {
  .stat-card-body {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 0.5rem !important;
  }
  .stat-body-right {
    max-width: 100%;
    width: 100%;
    align-items: flex-start;
  }
  .stat-body-right .stat-change {
    text-align: left;
    justify-content: flex-start;
  }
  .stat-change-text {
    word-break: normal;
    overflow-wrap: anywhere;
  }
}

/* ========================================
   Stat Icon Styles - Metallic by color scheme
   - Brushed metal with colored metallic sheen
   ======================================== */
.stat-card-header .stat-icon {
  margin-bottom: 0;
}

.stat-icon {
  width: 64px;
  height: 64px;
  background: linear-gradient(145deg, #5b9cf5 0%, #2563eb 30%, #1d4ed8 70%, #1e40af 100%);
  border-radius: var(--card-radius-sm, 12px);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 
    0 4px 12px rgba(37, 99, 235, 0.35),
    inset 0 1px 0 rgba(255, 255, 255, 0.35),
    inset 0 -1px 0 rgba(0, 0, 0, 0.2);
  transition: box-shadow 200ms ease-out;
}

.stat-icon:hover {
  box-shadow: 
    0 6px 18px rgba(37, 99, 235, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.4),
    inset 0 -1px 0 rgba(0, 0, 0, 0.25);
}

.stat-icon.success {
  background: linear-gradient(145deg, #34d399 0%, #059669 30%, #047857 70%, #065f46 100%);
  box-shadow: 
    0 4px 16px rgba(5, 150, 105, 0.35),
    inset 0 1px 0 rgba(255, 255, 255, 0.35),
    inset 0 -1px 0 rgba(0, 0, 0, 0.2);
}

.stat-icon.success:hover {
  box-shadow: 
    0 6px 18px rgba(5, 150, 105, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.4),
    inset 0 -1px 0 rgba(0, 0, 0, 0.25);
}

.stat-icon.deep-purple {
  background: linear-gradient(145deg, #a855f7 0%, #6A1B9A 30%, #5b21b6 70%, #4c1d95 100%);
  box-shadow: 
    0 4px 16px rgba(106, 27, 154, 0.35),
    inset 0 1px 0 rgba(255, 255, 255, 0.35),
    inset 0 -1px 0 rgba(0, 0, 0, 0.2);
}

.stat-icon.deep-purple:hover {
  box-shadow: 
    0 6px 18px rgba(106, 27, 154, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.4),
    inset 0 -1px 0 rgba(0, 0, 0, 0.25);
}

.stat-icon.purple {
  background: linear-gradient(145deg, #c084fc 0%, #7B1FA2 30%, #6b21a8 70%, #581c87 100%);
  box-shadow: 
    0 4px 16px rgba(123, 31, 162, 0.35),
    inset 0 1px 0 rgba(255, 255, 255, 0.35),
    inset 0 -1px 0 rgba(0, 0, 0, 0.2);
}

.stat-icon.purple:hover {
  box-shadow: 
    0 6px 18px rgba(123, 31, 162, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.4),
    inset 0 -1px 0 rgba(0, 0, 0, 0.25);
}

.stat-icon.error {
  background: linear-gradient(145deg, #f87171 0%, #b91c1c 30%, #991b1b 70%, #7f1d1d 100%);
  box-shadow: 
    0 4px 16px rgba(185, 28, 28, 0.35),
    inset 0 1px 0 rgba(255, 255, 255, 0.35),
    inset 0 -1px 0 rgba(0, 0, 0, 0.2);
}

.stat-icon.error:hover {
  box-shadow: 
    0 6px 18px rgba(185, 28, 28, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.4),
    inset 0 -1px 0 rgba(0, 0, 0, 0.25);
}

.stat-icon.grey-darken-2 {
  background: linear-gradient(145deg, #94a3b8 0%, #475569 30%, #334155 70%, #1e293b 100%);
  box-shadow: 
    0 4px 16px rgba(71, 85, 105, 0.35),
    inset 0 1px 0 rgba(255, 255, 255, 0.35),
    inset 0 -1px 0 rgba(0, 0, 0, 0.25);
}

.stat-icon.grey-darken-2:hover {
  box-shadow: 
    0 6px 18px rgba(71, 85, 105, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.4),
    inset 0 -1px 0 rgba(0, 0, 0, 0.3);
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
  font-size: 0.8125rem;
  font-weight: 600;
}

.stat-date {
  font-size: 0.8rem;
  color: #94a3b8;
  font-weight: 500;
  letter-spacing: 0.02em;
  overflow-wrap: break-word;
  word-break: break-word;
  min-width: 0;
}

/* Mobile Responsive Styles */
@media (max-width: 960px) {
  .stat-card {
    border-radius: 16px !important;
  }

  .stat-card-header,
  .stat-card-body {
    padding: 1rem 1.25rem !important;
  }

  .stat-divider {
    margin-left: 1rem !important;
    margin-right: 1rem !important;
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
  }

  .stat-change {
    font-size: 0.75rem !important;
  }

  .stat-date {
    font-size: 0.7rem !important;
  }
}

@media (max-width: 480px) {
  .stat-card-header,
  .stat-card-body {
    padding: 0.875rem 1rem !important;
  }

  .stat-icon {
    width: 40px !important;
    height: 40px !important;
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
  }

  .stat-change {
    font-size: 0.6875rem !important;
  }

  .stat-date {
    font-size: 0.65rem !important;
  }
}
</style>
