<template>
  <a 
    href="#main-content" 
    class="skip-link"
    @click.prevent="skipToMain"
  >
    Skip to main content
  </a>
</template>

<script setup>
/**
 * SkipNavigation Component
 * 
 * Provides a skip link for keyboard users to bypass navigation
 * and jump directly to the main content area.
 * 
 * WCAG 2.1 Success Criterion 2.4.1: Bypass Blocks
 */

const skipToMain = () => {
  const mainContent = document.getElementById('main-content') || 
                       document.querySelector('main') ||
                       document.querySelector('[role="main"]');
  
  if (mainContent) {
    // Make element focusable if it isn't
    if (!mainContent.hasAttribute('tabindex')) {
      mainContent.setAttribute('tabindex', '-1');
    }
    
    mainContent.focus();
    
    // Scroll into view
    mainContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
};
</script>

<style scoped>
.skip-link {
  position: absolute;
  top: -100%;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10000;
  padding: 1rem 2rem;
  background-color: #1e40af;
  color: white;
  font-weight: 600;
  font-size: 1rem;
  text-decoration: none;
  border-radius: 0 0 8px 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: top 0.2s ease-in-out;
}

.skip-link:focus {
  top: 0;
  outline: 3px solid #fbbf24;
  outline-offset: 2px;
}

.skip-link:hover {
  background-color: #1e3a8a;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .skip-link {
    background-color: #000;
    color: #fff;
    border: 3px solid #fff;
  }
  
  .skip-link:focus {
    outline: 4px solid #ff0;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .skip-link {
    transition: none;
  }
}
</style>
