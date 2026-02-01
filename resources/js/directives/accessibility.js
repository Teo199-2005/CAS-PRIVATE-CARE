/**
 * Vue Accessibility Directives & Utilities
 * =========================================
 * 
 * Provides Vue directives and utilities for WCAG 2.1 AA compliance.
 * These can be used across all components to ensure consistent
 * accessibility patterns.
 * 
 * @version 1.0.0
 * @since 2026-01-28
 */

/**
 * Focus Trap Directive
 * 
 * Traps focus within a container (useful for modals, dialogs)
 * Usage: v-focus-trap
 */
export const vFocusTrap = {
  mounted(el, binding) {
    const focusableSelectors = [
      'button:not([disabled]):not([tabindex="-1"])',
      'a[href]:not([tabindex="-1"])',
      'input:not([disabled]):not([tabindex="-1"])',
      'select:not([disabled]):not([tabindex="-1"])',
      'textarea:not([disabled]):not([tabindex="-1"])',
      '[tabindex]:not([tabindex="-1"])',
    ].join(', ');

    const getFocusableElements = () => {
      return Array.from(el.querySelectorAll(focusableSelectors));
    };

    const handleKeydown = (e) => {
      if (e.key !== 'Tab') return;

      const focusableElements = getFocusableElements();
      if (focusableElements.length === 0) return;

      const firstElement = focusableElements[0];
      const lastElement = focusableElements[focusableElements.length - 1];

      if (e.shiftKey) {
        if (document.activeElement === firstElement) {
          e.preventDefault();
          lastElement.focus();
        }
      } else {
        if (document.activeElement === lastElement) {
          e.preventDefault();
          firstElement.focus();
        }
      }
    };

    el._focusTrapHandler = handleKeydown;
    el.addEventListener('keydown', handleKeydown);

    // Auto-focus first focusable element
    if (binding.value !== false) {
      const focusableElements = getFocusableElements();
      if (focusableElements.length > 0) {
        requestAnimationFrame(() => {
          focusableElements[0].focus();
        });
      }
    }
  },

  unmounted(el) {
    if (el._focusTrapHandler) {
      el.removeEventListener('keydown', el._focusTrapHandler);
      delete el._focusTrapHandler;
    }
  },
};

/**
 * Announce Directive
 * 
 * Announces content to screen readers using aria-live regions
 * Usage: v-announce="message" or v-announce:polite="message"
 */
export const vAnnounce = {
  mounted(el, binding) {
    const politeness = binding.arg || 'polite'; // 'polite' or 'assertive'
    
    let announcer = document.getElementById('vue-announcer');
    if (!announcer) {
      announcer = document.createElement('div');
      announcer.id = 'vue-announcer';
      announcer.setAttribute('role', 'status');
      announcer.setAttribute('aria-live', politeness);
      announcer.setAttribute('aria-atomic', 'true');
      announcer.style.cssText = `
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
      `;
      document.body.appendChild(announcer);
    }

    el._announcer = announcer;
    
    if (binding.value) {
      announce(binding.value, politeness);
    }
  },

  updated(el, binding) {
    if (binding.value !== binding.oldValue && binding.value) {
      const politeness = binding.arg || 'polite';
      announce(binding.value, politeness);
    }
  },
};

/**
 * Announce a message to screen readers
 */
export function announce(message, politeness = 'polite') {
  let announcer = document.getElementById('vue-announcer');
  if (!announcer) {
    announcer = document.createElement('div');
    announcer.id = 'vue-announcer';
    announcer.setAttribute('role', 'status');
    announcer.setAttribute('aria-live', politeness);
    announcer.setAttribute('aria-atomic', 'true');
    announcer.style.cssText = `
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
    `;
    document.body.appendChild(announcer);
  }

  // Clear and re-announce to trigger screen reader
  announcer.textContent = '';
  announcer.setAttribute('aria-live', politeness);
  
  requestAnimationFrame(() => {
    announcer.textContent = message;
  });
}

/**
 * Skip Link Directive
 * 
 * Creates a skip link for keyboard navigation
 * Usage: v-skip-link="'#main-content'"
 */
export const vSkipLink = {
  mounted(el, binding) {
    const targetId = binding.value || '#main-content';
    
    el.setAttribute('href', targetId);
    el.classList.add('skip-link');
    
    // Add skip link styles if not present
    if (!document.getElementById('skip-link-styles')) {
      const style = document.createElement('style');
      style.id = 'skip-link-styles';
      style.textContent = `
        .skip-link {
          position: absolute;
          top: -100%;
          left: 50%;
          transform: translateX(-50%);
          z-index: 10000;
          padding: 12px 24px;
          background: #1e3a5f;
          color: white;
          text-decoration: none;
          border-radius: 0 0 8px 8px;
          transition: top 0.2s ease-in-out;
        }
        .skip-link:focus {
          top: 0;
          outline: 3px solid #fbbf24;
          outline-offset: 2px;
        }
      `;
      document.head.appendChild(style);
    }
  },
};

/**
 * Keyboard Navigation Directive
 * 
 * Adds arrow key navigation for lists and menus
 * Usage: v-keyboard-nav
 */
export const vKeyboardNav = {
  mounted(el, binding) {
    const orientation = binding.value?.orientation || 'vertical';
    const selector = binding.value?.selector || '[role="menuitem"], [role="option"], button, a';
    
    const handleKeydown = (e) => {
      const items = Array.from(el.querySelectorAll(selector));
      const currentIndex = items.indexOf(document.activeElement);
      
      if (currentIndex === -1) return;

      let nextIndex = currentIndex;

      if (orientation === 'vertical') {
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          nextIndex = (currentIndex + 1) % items.length;
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          nextIndex = (currentIndex - 1 + items.length) % items.length;
        }
      } else if (orientation === 'horizontal') {
        if (e.key === 'ArrowRight') {
          e.preventDefault();
          nextIndex = (currentIndex + 1) % items.length;
        } else if (e.key === 'ArrowLeft') {
          e.preventDefault();
          nextIndex = (currentIndex - 1 + items.length) % items.length;
        }
      } else if (orientation === 'both') {
        if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
          e.preventDefault();
          nextIndex = (currentIndex + 1) % items.length;
        } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
          e.preventDefault();
          nextIndex = (currentIndex - 1 + items.length) % items.length;
        }
      }

      // Home/End navigation
      if (e.key === 'Home') {
        e.preventDefault();
        nextIndex = 0;
      } else if (e.key === 'End') {
        e.preventDefault();
        nextIndex = items.length - 1;
      }

      if (nextIndex !== currentIndex) {
        items[nextIndex].focus();
      }
    };

    el._keyboardNavHandler = handleKeydown;
    el.addEventListener('keydown', handleKeydown);
  },

  unmounted(el) {
    if (el._keyboardNavHandler) {
      el.removeEventListener('keydown', el._keyboardNavHandler);
      delete el._keyboardNavHandler;
    }
  },
};

/**
 * Roving Tab Index Directive
 * 
 * Implements roving tabindex pattern for composite widgets
 * Usage: v-roving-tabindex
 */
export const vRovingTabindex = {
  mounted(el, binding) {
    const selector = binding.value?.selector || '[role="tab"], [role="option"], button';
    const items = el.querySelectorAll(selector);
    
    // Set initial tabindex
    items.forEach((item, index) => {
      item.setAttribute('tabindex', index === 0 ? '0' : '-1');
    });

    const handleKeydown = (e) => {
      if (!['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(e.key)) {
        return;
      }

      const items = Array.from(el.querySelectorAll(selector));
      const currentIndex = items.indexOf(document.activeElement);
      
      if (currentIndex === -1) return;

      e.preventDefault();
      
      let nextIndex = currentIndex;

      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
        nextIndex = (currentIndex + 1) % items.length;
      } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
        nextIndex = (currentIndex - 1 + items.length) % items.length;
      } else if (e.key === 'Home') {
        nextIndex = 0;
      } else if (e.key === 'End') {
        nextIndex = items.length - 1;
      }

      // Update tabindex
      items[currentIndex].setAttribute('tabindex', '-1');
      items[nextIndex].setAttribute('tabindex', '0');
      items[nextIndex].focus();
    };

    el._rovingTabHandler = handleKeydown;
    el.addEventListener('keydown', handleKeydown);
  },

  unmounted(el) {
    if (el._rovingTabHandler) {
      el.removeEventListener('keydown', el._rovingTabHandler);
      delete el._rovingTabHandler;
    }
  },
};

/**
 * Register all accessibility directives
 */
export function registerAccessibilityDirectives(app) {
  app.directive('focus-trap', vFocusTrap);
  app.directive('announce', vAnnounce);
  app.directive('skip-link', vSkipLink);
  app.directive('keyboard-nav', vKeyboardNav);
  app.directive('roving-tabindex', vRovingTabindex);
}

export default {
  vFocusTrap,
  vAnnounce,
  vSkipLink,
  vKeyboardNav,
  vRovingTabindex,
  announce,
  registerAccessibilityDirectives,
};
