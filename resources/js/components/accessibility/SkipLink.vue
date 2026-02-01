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
 * SkipLink Component
 * Provides keyboard-accessible skip navigation for screen readers and keyboard users
 * WCAG 2.1 Success Criterion 2.4.1: Bypass Blocks
 */

const skipToMain = () => {
  const main = document.getElementById('main-content');
  if (main) {
    main.setAttribute('tabindex', '-1');
    main.focus();
    main.scrollIntoView({ behavior: 'smooth' });
    // Remove tabindex after focus to prevent issues
    setTimeout(() => {
      main.removeAttribute('tabindex');
    }, 100);
  }
};
</script>

<style scoped>
.skip-link {
  position: absolute;
  top: -100%;
  left: 50%;
  transform: translateX(-50%);
  z-index: 9999;
  padding: 12px 24px;
  background-color: #1976D2;
  color: white;
  text-decoration: none;
  font-weight: 600;
  border-radius: 0 0 8px 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  transition: top 0.3s ease;
}

.skip-link:focus {
  top: 0;
  outline: 3px solid #FFC107;
  outline-offset: 2px;
}

.skip-link:hover {
  background-color: #1565C0;
}
</style>
