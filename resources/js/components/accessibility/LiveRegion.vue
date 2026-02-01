<template>
  <div
    :aria-live="politeness"
    :aria-atomic="atomic"
    :aria-relevant="relevant"
    :role="role"
    class="live-region"
    :class="{ 'sr-only': visuallyHidden }"
  >
    <slot></slot>
  </div>
</template>

<script setup>
/**
 * LiveRegion Component
 * Creates ARIA live regions for dynamic content announcements
 * WCAG 2.1 Success Criterion 4.1.3: Status Messages
 */

defineProps({
  /**
   * Politeness level for announcements
   * - 'polite': Wait for user to finish current task
   * - 'assertive': Interrupt immediately (use sparingly)
   * - 'off': Don't announce
   */
  politeness: {
    type: String,
    default: 'polite',
    validator: (v) => ['polite', 'assertive', 'off'].includes(v)
  },
  /**
   * Whether to announce the entire region or just changes
   */
  atomic: {
    type: Boolean,
    default: true
  },
  /**
   * What types of changes to announce
   * - 'additions': New nodes added
   * - 'removals': Nodes removed
   * - 'text': Text content changes
   * - 'all': All changes
   */
  relevant: {
    type: String,
    default: 'additions text'
  },
  /**
   * ARIA role for the region
   * Common values: 'status', 'alert', 'log', 'timer'
   */
  role: {
    type: String,
    default: null
  },
  /**
   * Whether to visually hide the region (still accessible to screen readers)
   */
  visuallyHidden: {
    type: Boolean,
    default: true
  }
});
</script>

<style scoped>
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.live-region:not(.sr-only) {
  /* Visible live region styles */
  padding: 8px 16px;
  border-radius: 4px;
  margin: 8px 0;
}
</style>
