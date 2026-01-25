<template>
  <Teleport to="body">
    <!-- Skip to main content link -->
    <a 
      href="#main-content" 
      class="skip-link"
      @click.prevent="skipToMain"
    >
      Skip to main content
    </a>
    
    <!-- Accessibility toolbar (optional, triggered by keyboard shortcut) -->
    <div 
      v-if="showToolbar" 
      class="a11y-toolbar"
      role="toolbar"
      aria-label="Accessibility options"
    >
      <button 
        @click="toggleHighContrast" 
        :aria-pressed="isHighContrast"
        class="a11y-btn"
      >
        <span class="a11y-icon">◐</span>
        High Contrast
      </button>
      
      <button 
        @click="increaseFontSize" 
        class="a11y-btn"
        aria-label="Increase font size"
      >
        <span class="a11y-icon">A+</span>
      </button>
      
      <button 
        @click="decreaseFontSize" 
        class="a11y-btn"
        aria-label="Decrease font size"
      >
        <span class="a11y-icon">A-</span>
      </button>
      
      <button 
        @click="resetFontSize" 
        class="a11y-btn"
        aria-label="Reset font size"
      >
        <span class="a11y-icon">A</span>
      </button>
      
      <button 
        @click="toggleReducedMotion" 
        :aria-pressed="isReducedMotion"
        class="a11y-btn"
      >
        <span class="a11y-icon">⏸</span>
        Reduce Motion
      </button>
      
      <button 
        @click="closeToolbar" 
        class="a11y-btn a11y-close"
        aria-label="Close accessibility toolbar"
      >
        ✕
      </button>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const showToolbar = ref(false);
const isHighContrast = ref(false);
const isReducedMotion = ref(false);
const fontSize = ref(100);

const STORAGE_KEY = 'cas-a11y-preferences';

// Skip to main content
const skipToMain = () => {
  const main = document.getElementById('main-content') || document.querySelector('main');
  if (main) {
    main.setAttribute('tabindex', '-1');
    main.focus();
    main.scrollIntoView({ behavior: 'smooth' });
  }
};

// Toggle high contrast mode
const toggleHighContrast = () => {
  isHighContrast.value = !isHighContrast.value;
  document.documentElement.classList.toggle('high-contrast', isHighContrast.value);
  savePreferences();
};

// Font size controls
const increaseFontSize = () => {
  if (fontSize.value < 150) {
    fontSize.value += 10;
    applyFontSize();
  }
};

const decreaseFontSize = () => {
  if (fontSize.value > 80) {
    fontSize.value -= 10;
    applyFontSize();
  }
};

const resetFontSize = () => {
  fontSize.value = 100;
  applyFontSize();
};

const applyFontSize = () => {
  document.documentElement.style.fontSize = `${fontSize.value}%`;
  savePreferences();
};

// Toggle reduced motion
const toggleReducedMotion = () => {
  isReducedMotion.value = !isReducedMotion.value;
  document.documentElement.classList.toggle('reduce-motion', isReducedMotion.value);
  savePreferences();
};

// Close toolbar
const closeToolbar = () => {
  showToolbar.value = false;
};

// Save preferences to localStorage
const savePreferences = () => {
  const preferences = {
    highContrast: isHighContrast.value,
    reducedMotion: isReducedMotion.value,
    fontSize: fontSize.value
  };
  localStorage.setItem(STORAGE_KEY, JSON.stringify(preferences));
};

// Load preferences from localStorage
const loadPreferences = () => {
  try {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) {
      const preferences = JSON.parse(saved);
      
      if (preferences.highContrast) {
        isHighContrast.value = true;
        document.documentElement.classList.add('high-contrast');
      }
      
      if (preferences.reducedMotion) {
        isReducedMotion.value = true;
        document.documentElement.classList.add('reduce-motion');
      }
      
      if (preferences.fontSize) {
        fontSize.value = preferences.fontSize;
        document.documentElement.style.fontSize = `${fontSize.value}%`;
      }
    }
  } catch (e) {
    console.warn('Failed to load accessibility preferences:', e);
  }
};

// Keyboard shortcut handler (Alt + A to toggle toolbar)
const handleKeydown = (event) => {
  if (event.altKey && event.key === 'a') {
    event.preventDefault();
    showToolbar.value = !showToolbar.value;
  }
  
  // Escape to close toolbar
  if (event.key === 'Escape' && showToolbar.value) {
    closeToolbar();
  }
};

// Check for system preferences
const checkSystemPreferences = () => {
  // Check for reduced motion preference
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    isReducedMotion.value = true;
    document.documentElement.classList.add('reduce-motion');
  }
  
  // Check for high contrast preference
  if (window.matchMedia('(prefers-contrast: more)').matches) {
    isHighContrast.value = true;
    document.documentElement.classList.add('high-contrast');
  }
};

onMounted(() => {
  loadPreferences();
  checkSystemPreferences();
  document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
});
</script>

<style>
/* Skip link - visible only on focus */
.skip-link {
  position: fixed;
  top: -100px;
  left: 50%;
  transform: translateX(-50%);
  background: #1976d2;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 0 0 8px 8px;
  z-index: 10000;
  text-decoration: none;
  font-weight: 600;
  transition: top 0.3s ease;
}

.skip-link:focus {
  top: 0;
  outline: 3px solid #fff;
  outline-offset: 2px;
}

/* Accessibility toolbar */
.a11y-toolbar {
  position: fixed;
  top: 0;
  right: 0;
  background: #333;
  color: white;
  padding: 0.5rem;
  border-radius: 0 0 0 8px;
  z-index: 9999;
  display: flex;
  gap: 0.5rem;
  align-items: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.a11y-btn {
  background: #444;
  color: white;
  border: none;
  padding: 0.5rem 0.75rem;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.875rem;
  transition: background 0.2s;
}

.a11y-btn:hover,
.a11y-btn:focus {
  background: #555;
  outline: 2px solid #fff;
  outline-offset: 2px;
}

.a11y-btn[aria-pressed="true"] {
  background: #1976d2;
}

.a11y-icon {
  font-size: 1rem;
}

.a11y-close {
  padding: 0.5rem;
  margin-left: 0.25rem;
}

/* High contrast mode styles */
.high-contrast {
  --text-primary: #000 !important;
  --text-secondary: #333 !important;
  --bg-primary: #fff !important;
  --bg-secondary: #f0f0f0 !important;
  --border-color: #000 !important;
}

.high-contrast body {
  background: #fff !important;
  color: #000 !important;
}

.high-contrast a {
  color: #0000ff !important;
  text-decoration: underline !important;
}

.high-contrast button,
.high-contrast .btn {
  border: 2px solid #000 !important;
}

.high-contrast img {
  filter: contrast(1.2);
}

/* Reduced motion styles */
.reduce-motion *,
.reduce-motion *::before,
.reduce-motion *::after {
  animation-duration: 0.001ms !important;
  animation-iteration-count: 1 !important;
  transition-duration: 0.001ms !important;
  scroll-behavior: auto !important;
}

/* Focus visible improvements */
:focus-visible {
  outline: 3px solid #1976d2;
  outline-offset: 2px;
}

/* Ensure sufficient color contrast for interactive elements */
a:focus,
button:focus,
input:focus,
select:focus,
textarea:focus {
  outline: 2px solid #1976d2;
  outline-offset: 2px;
}

@media (max-width: 640px) {
  .a11y-toolbar {
    flex-wrap: wrap;
    max-width: 100%;
  }
  
  .a11y-btn span:not(.a11y-icon) {
    display: none;
  }
}
</style>
