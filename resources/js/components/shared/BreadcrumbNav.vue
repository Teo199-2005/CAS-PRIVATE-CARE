<template>
  <nav 
    v-if="items.length > 0" 
    class="breadcrumb-nav" 
    aria-label="Breadcrumb navigation"
  >
    <ol class="breadcrumb-list" role="list">
      <li 
        v-for="(item, index) in items" 
        :key="index" 
        class="breadcrumb-item"
        :class="{ 'breadcrumb-item--current': index === items.length - 1 }"
      >
        <!-- Separator (except for first item) -->
        <span 
          v-if="index > 0" 
          class="breadcrumb-separator" 
          aria-hidden="true"
        >
          <v-icon size="16">{{ separatorIcon }}</v-icon>
        </span>
        
        <!-- Link or current page -->
        <template v-if="index === items.length - 1">
          <!-- Current page (not a link) -->
          <span 
            class="breadcrumb-current" 
            aria-current="page"
          >
            <v-icon 
              v-if="item.icon" 
              size="16" 
              class="mr-1"
              aria-hidden="true"
            >{{ item.icon }}</v-icon>
            {{ item.text }}
          </span>
        </template>
        <template v-else>
          <!-- Clickable link -->
          <a 
            :href="item.href" 
            class="breadcrumb-link"
            @click.prevent="navigate(item)"
          >
            <v-icon 
              v-if="item.icon" 
              size="16" 
              class="mr-1"
              aria-hidden="true"
            >{{ item.icon }}</v-icon>
            {{ item.text }}
          </a>
        </template>
      </li>
    </ol>
  </nav>
</template>

<script setup>
/**
 * Breadcrumb Navigation Component
 * 
 * Provides accessible breadcrumb navigation for multi-page flows.
 * 
 * WCAG 2.1 Compliant:
 * - Uses nav element with aria-label
 * - Uses ordered list structure
 * - Current page marked with aria-current="page"
 * - Proper keyboard navigation
 * 
 * Usage:
 * <BreadcrumbNav :items="[
 *   { text: 'Home', href: '/', icon: 'mdi-home' },
 *   { text: 'Bookings', href: '/bookings' },
 *   { text: 'New Booking', href: '/book-service' }
 * ]" />
 */
import { computed } from 'vue';

const props = defineProps({
  items: {
    type: Array,
    required: true,
    validator: (value) => {
      return value.every(item => item.text && (item.href || item.to));
    }
  },
  // Separator icon
  separatorIcon: {
    type: String,
    default: 'mdi-chevron-right'
  },
  // Use Vue Router navigation
  useRouter: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['navigate']);

/**
 * Navigate to breadcrumb item
 */
const navigate = (item) => {
  emit('navigate', item);
  
  if (props.useRouter && item.to) {
    // If using Vue Router, emit event for parent to handle
    return;
  }
  
  // Standard navigation
  if (item.href) {
    window.location.href = item.href;
  }
};
</script>

<style scoped>
.breadcrumb-nav {
  padding: 0.75rem 0;
  margin-bottom: 1rem;
}

.breadcrumb-list {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.25rem;
  list-style: none;
  padding: 0;
  margin: 0;
}

.breadcrumb-item {
  display: flex;
  align-items: center;
}

.breadcrumb-separator {
  color: #9ca3af;
  margin: 0 0.25rem;
  display: flex;
  align-items: center;
}

.breadcrumb-link {
  display: flex;
  align-items: center;
  color: #3b82f6;
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.breadcrumb-link:hover {
  background-color: #eff6ff;
  color: #1d4ed8;
  text-decoration: underline;
}

.breadcrumb-link:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

.breadcrumb-current {
  display: flex;
  align-items: center;
  color: #6b7280;
  font-size: 0.875rem;
  font-weight: 600;
  padding: 0.25rem 0.5rem;
}

/* Mobile responsive */
@media (max-width: 480px) {
  .breadcrumb-nav {
    padding: 0.5rem 0;
  }
  
  .breadcrumb-link,
  .breadcrumb-current {
    font-size: 0.75rem;
    padding: 0.25rem;
  }
  
  /* On very small screens, show only last 2-3 items */
  .breadcrumb-item:not(:nth-last-child(-n+3)) {
    display: none;
  }
  
  .breadcrumb-item:first-child {
    display: flex !important;
  }
  
  /* Show ellipsis when items are hidden */
  .breadcrumb-item:first-child + .breadcrumb-item:not(:nth-last-child(-n+2))::before {
    content: '...';
    color: #9ca3af;
    margin-right: 0.5rem;
  }
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .breadcrumb-link {
    color: #0000ee;
    text-decoration: underline;
  }
  
  .breadcrumb-current {
    color: #000;
  }
  
  .breadcrumb-separator {
    color: #000;
  }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .breadcrumb-link {
    transition: none;
  }
}
</style>
