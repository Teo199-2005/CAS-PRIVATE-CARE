<template>
  <div class="empty-state" :class="sizeClass">
    <!-- Icon or Illustration -->
    <div class="empty-state-visual">
      <slot name="icon">
        <div class="empty-state-icon-wrapper" :class="colorClass">
          <v-icon :size="iconSize" :color="iconColor">{{ icon }}</v-icon>
        </div>
      </slot>
    </div>

    <!-- Title -->
    <h3 class="empty-state-title">{{ title }}</h3>

    <!-- Description -->
    <p v-if="description" class="empty-state-description">{{ description }}</p>

    <!-- Action Button -->
    <slot name="action">
      <v-btn 
        v-if="actionLabel"
        :color="actionColor"
        :variant="actionVariant"
        @click="$emit('action')"
        class="empty-state-action"
        :prepend-icon="actionIcon"
      >
        {{ actionLabel }}
      </v-btn>
    </slot>

    <!-- Additional content slot -->
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  icon: {
    type: String,
    default: 'mdi-inbox-outline'
  },
  iconColor: {
    type: String,
    default: 'grey-lighten-1'
  },
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  actionLabel: {
    type: String,
    default: ''
  },
  actionIcon: {
    type: String,
    default: ''
  },
  actionColor: {
    type: String,
    default: 'primary'
  },
  actionVariant: {
    type: String,
    default: 'flat'
  },
  size: {
    type: String,
    default: 'medium', // 'small', 'medium', 'large'
    validator: (v) => ['small', 'medium', 'large'].includes(v)
  },
  variant: {
    type: String,
    default: 'default', // 'default', 'subtle', 'prominent'
    validator: (v) => ['default', 'subtle', 'prominent'].includes(v)
  }
});

defineEmits(['action']);

const sizeClass = computed(() => `empty-state--${props.size}`);

const colorClass = computed(() => {
  const colorMap = {
    'primary': 'icon-bg-primary',
    'success': 'icon-bg-success',
    'warning': 'icon-bg-warning',
    'error': 'icon-bg-error',
    'info': 'icon-bg-info',
    'grey-lighten-1': 'icon-bg-grey'
  };
  return colorMap[props.iconColor] || 'icon-bg-grey';
});

const iconSize = computed(() => {
  const sizeMap = {
    small: 32,
    medium: 48,
    large: 64
  };
  return sizeMap[props.size];
});
</script>

<style scoped>
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 32px 24px;
  min-height: 200px;
}

.empty-state--small {
  padding: 24px 16px;
  min-height: 150px;
}

.empty-state--large {
  padding: 48px 32px;
  min-height: 300px;
}

.empty-state-visual {
  margin-bottom: 16px;
}

.empty-state-icon-wrapper {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
}

.empty-state--small .empty-state-icon-wrapper {
  width: 56px;
  height: 56px;
}

.empty-state--large .empty-state-icon-wrapper {
  width: 100px;
  height: 100px;
}

/* Icon background colors */
.icon-bg-primary {
  background: rgba(59, 130, 246, 0.1);
}

.icon-bg-success {
  background: rgba(16, 185, 129, 0.1);
}

.icon-bg-warning {
  background: rgba(245, 158, 11, 0.1);
}

.icon-bg-error {
  background: rgba(239, 68, 68, 0.1);
}

.icon-bg-info {
  background: rgba(6, 182, 212, 0.1);
}

.icon-bg-grey {
  background: #f3f4f6;
}

.empty-state-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 8px;
  line-height: 1.3;
}

.empty-state--small .empty-state-title {
  font-size: 1rem;
}

.empty-state--large .empty-state-title {
  font-size: 1.375rem;
}

.empty-state-description {
  font-size: 0.9375rem;
  color: #6b7280;
  max-width: 320px;
  line-height: 1.5;
  margin: 0 auto 24px;
}

.empty-state--small .empty-state-description {
  font-size: 0.875rem;
  max-width: 260px;
  margin-bottom: 16px;
}

.empty-state--large .empty-state-description {
  font-size: 1rem;
  max-width: 400px;
}

.empty-state-action {
  min-height: 44px;
  padding: 12px 24px;
  font-weight: 600;
  border-radius: 12px;
  text-transform: none;
}

/* Mobile responsiveness */
@media (max-width: 480px) {
  .empty-state {
    padding: 24px 16px;
  }
  
  .empty-state-icon-wrapper {
    width: 64px;
    height: 64px;
  }
  
  .empty-state-title {
    font-size: 1rem;
  }
  
  .empty-state-description {
    font-size: 0.875rem;
    max-width: 90%;
  }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .empty-state * {
    transition: none !important;
    animation: none !important;
  }
}
</style>
