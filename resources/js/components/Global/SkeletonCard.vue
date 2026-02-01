<template>
  <div class="skeleton-card" :class="{ 'skeleton-card--with-header': showHeader }">
    <!-- Header -->
    <div v-if="showHeader" class="skeleton-card__header">
      <SkeletonLoader v-if="showAvatar" variant="avatar" :width="40" :height="40" />
      <div class="skeleton-card__header-text">
        <SkeletonLoader variant="text" width="60%" height="1.125rem" />
        <SkeletonLoader variant="text" width="40%" height="0.875rem" />
      </div>
    </div>
    
    <!-- Image placeholder -->
    <SkeletonLoader 
      v-if="showImage" 
      variant="rectangular" 
      width="100%" 
      :height="imageHeight" 
    />
    
    <!-- Content -->
    <div class="skeleton-card__content">
      <SkeletonLoader 
        v-for="i in lines" 
        :key="i"
        variant="text"
        :width="i === lines ? '75%' : '100%'"
        height="1rem"
      />
    </div>
    
    <!-- Actions -->
    <div v-if="showActions" class="skeleton-card__actions">
      <SkeletonLoader variant="button" width="80px" height="32px" />
      <SkeletonLoader variant="button" width="80px" height="32px" />
    </div>
  </div>
</template>

<script setup>
import SkeletonLoader from './SkeletonLoader.vue';

defineProps({
  /**
   * Show card header with avatar
   */
  showHeader: {
    type: Boolean,
    default: false
  },
  /**
   * Show avatar in header
   */
  showAvatar: {
    type: Boolean,
    default: true
  },
  /**
   * Show image placeholder
   */
  showImage: {
    type: Boolean,
    default: false
  },
  /**
   * Image height
   */
  imageHeight: {
    type: [String, Number],
    default: 140
  },
  /**
   * Number of content text lines
   */
  lines: {
    type: Number,
    default: 3
  },
  /**
   * Show action buttons
   */
  showActions: {
    type: Boolean,
    default: false
  }
});
</script>

<style scoped>
.skeleton-card {
  background: var(--card-background, #ffffff);
  border-radius: 12px;
  padding: 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.skeleton-card__header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.skeleton-card__header-text {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.skeleton-card__content {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.skeleton-card__actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--divider, #e0e0e0);
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
  .skeleton-card {
    background: var(--card-background-dark, #1e1e1e);
  }
  
  .skeleton-card__actions {
    border-top-color: var(--divider-dark, #333333);
  }
}
</style>
